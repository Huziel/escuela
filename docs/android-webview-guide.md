# SICE Student Portal — Android WebView Integration Guide

**Document Version**: 1.0  
**Last Updated**: 2026-07-30  
**SICE Version**: Laravel 11 + Vue 3 (Vite)

---

## Table of Contents

1. [Quick Reference Card](#1-quick-reference-card)
2. [Architecture Overview](#2-architecture-overview)
3. [Authentication Flow](#3-authentication-flow)
4. [Portal URL Map](#4-portal-url-map)
5. [API Reference](#5-api-reference)
6. [WebView Configuration](#6-webview-configuration)
7. [Static Assets & CORS](#7-static-assets--cors)
8. [Theme & Persistence](#8-theme--persistence)
9. [Error Handling & Token Expiry](#9-error-handling--token-expiry)
10. [Environment Configuration](#10-environment-configuration)
11. [Checklist Before Shipping](#11-checklist-before-shipping)
12. [Appendix A: Complete Route Map](#appendix-a-complete-route-map)
13. [Appendix B: WebView Sample Activity](#appendix-b-webview-sample-activity-kotlin)
14. [Appendix C: API Response Format](#appendix-c-api-response-format-universal)

---

## 1. Quick Reference Card

| Item | Value |
|---|---|
| **Base API URL** | `http://<server>:8000/api/v1` |
| **Auth method** | Laravel Sanctum — Bearer token |
| **Token storage** | `localStorage` key `token` |
| **Token lifespan** | 1440 minutes (24 hours) |
| **Theme storage** | `localStorage` key `sice-theme-<userId>` |
| **Login endpoint** | `POST /api/v1/login` |
| **Student portal URL** | `<BASE_URL>/portal` |
| **Vue Router mode** | `createWebHistory` (no `#`) |
| **Response format** | `{ success: bool, message: string, data: ... }` |

---

## 2. Architecture Overview

```
┌─────────────────────────────────┐
│  Android WebView                 │
│  ┌─────────────────────────────┐ │
│  │  Vue 3 SPA (from server)    │ │
│  │  Login → Portal Dashboard   │ │
│  │  Axios (Bearer token)       │ │
│  └──────────────┬──────────────┘ │
└─────────────────┼────────────────┘
                  │ HTTPS
         ┌────────▼────────┐
         │  Laravel API     │
         │  :8000/api/v1    │
         └─────────────────┘
```

Android code is a thin shell. All auth, routing, and API calls happen inside the Vue SPA.

---

## 3. Authentication Flow

### Login Endpoint

```
POST /api/v1/login
Content-Type: application/json
{ "username": "student@school.edu", "password": "..." }
```

**Success (200)**:
```json
{
  "success": true,
  "data": {
    "token": "1|8aX7kPq3mNvLzRtYwJbDfGhE...",
    "user": { "id": 15, "username": "student001", "roles": [...], "alumno": {...} }
  }
}
```

### Token Storage

Stored in `localStorage.token`. Axios interceptor injects `Authorization: Bearer <token>` on every request.

### Token Refresh

`POST /api/v1/refresh-token` — called automatically on 401. On failure → clears token → redirects to `/login`.

### Logout

`POST /api/v1/logout` → clears `localStorage.token` → redirects to `/login`.

### Redirect Logic

- No token → `/login`
- Has token + alumno role → `/portal`
- Has token + admin role → `/` (dashboard)

---

## 4. Portal URL Map

| Route | Purpose | Access |
|---|---|---|
| `/portal` | Student dashboard (grades, schedule, attendance) | `portal.ver` |
| `/portal/ajustes` | Theme toggle, change password, logout | `portal.ver` |

All detail views are in-app bottom-sheet modals, not separate routes. Tapping a stat card opens the modal with data already loaded from 5 parallel API calls.

---

## 5. API Reference

### Student Portal Endpoints

All require `Authorization: Bearer <token>` + `role:alumno` middleware.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/portal-alumno/dashboard` | Student info, stats, avisos |
| GET | `/portal-alumno/perfil` | Full profile |
| GET | `/portal-alumno/calificaciones?periodo=X` | Grades grouped by period |
| GET | `/portal-alumno/kardex` | Full academic history |
| GET | `/portal-alumno/asistencias?per_page=20` | Attendance records |
| GET | `/portal-alumno/horario` | Weekly schedule |
| GET | `/portal-alumno/boleta?periodo=X` | Report card |
| POST | `/portal-alumno/change-password` | Change password |
| GET | `/configuracion/noticias?per_page=1` | Latest news |

### Dashboard Response Example

```json
{
  "success": true,
  "data": {
    "alumno": {
      "id": 8, "matricula": "A20240001", "nombre": "Juan Pérez López",
      "foto": "fotos/abc123.jpg", "especialidad": "Programación",
      "semestre": 3, "grupo": "3A", "turno": "Matutino", "estatus": "activo"
    },
    "resumen": {
      "promedio_general": 8.5, "materias_cursadas": 8,
      "materias_aprobadas": 7, "materias_reprobadas": 1,
      "porcentaje_asistencia": 92.3, "faltas": 3
    }
  }
}
```

### Horario Response Example

```json
{
  "data": {
    "grupo": "3A", "turno": "Matutino",
    "horario": {
      "lunes": [
        { "hora_inicio": "7:00", "hora_fin": "8:00", "materia": "Matemáticas", "docente": "Prof. García" }
      ],
      "martes": [...], "miercoles": [...], "jueves": [...], "viernes": [...]
    }
  }
}
```

---

## 6. WebView Configuration

### Minimum Kotlin Setup

```kotlin
webView.settings.apply {
    javaScriptEnabled = true
    domStorageEnabled = true       // CRITICAL: localStorage
    databaseEnabled = true
    allowFileAccess = false
    allowContentAccess = false
    mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
    cacheMode = WebSettings.LOAD_DEFAULT
}

CookieManager.getInstance().setAcceptCookie(true)
```

### Initial URL

**Always load `/`** — let Vue router handle auth and redirect. Do NOT load `/portal` directly.

### Splash Screen

```kotlin
webView.webViewClient = object : WebViewClient() {
    override fun onPageFinished(view: WebView, url: String) {
        hideSplashScreen()
    }
}
```

### Back Button

```kotlin
override fun onBackPressed() {
    if (webView.canGoBack()) webView.goBack() else super.onBackPressed()
}
```

### External Links

```kotlin
override fun shouldOverrideUrlLoading(view: WebView, request: WebResourceRequest): Boolean {
    val url = request.url.toString()
    if (!url.startsWith(BASE_URL)) {
        startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
        return true
    }
    return false
}
```

---

## 7. Static Assets & CORS

### Image Paths

Images stored in `storage/app/public/` → served via `http://server/storage/fotos/abc.jpg`.

**Problem**: Portal APIs return relative paths (`fotos/abc.jpg`), not full URLs. The SPA resolves these as relative to page origin → 404.

**Solutions**:
1. **Backend fix (recommended)**: Return `asset('storage/' . $path)` in PortalAlumnoService
2. **Nginx rewrite**: `rewrite ^/(fotos|noticias)/(.*)$ /storage/$1/$2 last;`
3. **WebView intercept**: Intercept image requests and prepend `/storage/`

### CORS

Same-origin in WebView → CORS not an issue. For native API calls, configure `FRONTEND_URL` in `.env`.

---

## 8. Theme & Persistence

| Data | Storage | Survives Restart? |
|---|---|---|
| Auth token | `localStorage.token` | Yes |
| Theme | `localStorage.sice-theme-<userId>` | Yes |
| User object | Pinia (memory) | No (re-fetched) |
| Portal data | Vue state | No (re-fetched) |

**Session Recovery**: WebView loads `/` → finds token → fetches user → applies theme → redirects to `/portal` → fetches data. Takes 1-3 seconds.

---

## 9. Error Handling & Token Expiry

### Axios Interceptor

| Status | Behavior |
|---|---|
| 401 | Auto-refresh token. On fail → `/login` |
| 419 | Session expired → `/login` |
| 403 | SweetAlert2: "Acceso denegado" |
| 422 | SweetAlert2 with validation error |
| 5xx | SweetAlert2: "Error del servidor" |

### Token Expiry (24h)

Sanctum `expiration => 1440` minutes. After expiry, refresh fails → `localStorage` cleared → auto-redirect to `/login`.

### Offline

```kotlin
override fun onReceivedError(...) {
    if (!isNetworkAvailable()) showOfflineScreen()
}
```

---

## 10. Environment Configuration

### Server `.env`

```
APP_URL=https://sice.miescuela.edu.mx
FRONTEND_URL=https://sice.miescuela.edu.mx
APP_DEBUG=false
```

### Frontend `.env.production`

```
VITE_API_URL=https://sice.miescuela.edu.mx/api/v1
VITE_APP_URL=https://sice.miescuela.edu.mx
```

### Android

```kotlin
// Emulator → host machine
const val DEV_BASE_URL = "http://10.0.2.2:8000"
// Production
const val PROD_BASE_URL = "https://sice.miescuela.edu.mx"
```

---

## 11. Checklist Before Shipping

### Server
- [ ] `APP_DEBUG=false`
- [ ] HTTPS enabled
- [ ] `php artisan storage:link`
- [ ] Image URLs fixed (full paths or nginx rewrite)

### Android
- [ ] `domStorageEnabled = true`
- [ ] `javaScriptEnabled = true`
- [ ] Cookies enabled
- [ ] External links open in browser
- [ ] Offline screen on error
- [ ] Splash screen hides on page finish
- [ ] Back button delegates to WebView
- [ ] `INTERNET` permission
- [ ] Network security config for cleartext (dev)

### Testing
- [ ] Login → redirects to `/portal`
- [ ] Kill app, reopen → still authenticated
- [ ] Token expiry → auto-redirect to login
- [ ] Photos load correctly
- [ ] Theme survives restart
- [ ] All dashboard cards work
- [ ] Calendar renders all days
- [ ] Offline shows error

---

## Appendix A: Complete Route Map

### Vue Routes (SPA)

| Path | Component | Auth | Permission |
|---|---|---|---|
| `/login` | Login.vue | Guest | — |
| `/forgot-password` | ForgotPassword.vue | Guest | — |
| `/` | Dashboard.vue | Required | — |
| `/portal` | PortalDashboard.vue | Required | `portal.ver` |
| `/portal/ajustes` | PortalAjustes.vue | Required | `portal.ver` |

### API Route Groups

| Module | Prefix | Middleware |
|---|---|---|
| Auth | `/api/v1/` | Public + `auth:sanctum` |
| Portal Alumno | `/api/v1/portal-alumno/` | `auth:sanctum` + `role:alumno` |
| Configuración | `/api/v1/configuracion/` | `auth:sanctum` |
| All other modules | `/api/v1/<module>/` | `auth:sanctum` + permissions |

---

## Appendix B: WebView Sample Activity (Kotlin)

```kotlin
class PortalActivity : AppCompatActivity() {
    private lateinit var webView: WebView
    private lateinit var splashView: View

    companion object {
        const val BASE_URL = "https://sice.miescuela.edu.mx"
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_portal)
        webView = findViewById(R.id.webview)
        splashView = findViewById(R.id.splash)
        configureWebView()
        webView.loadUrl(BASE_URL)
    }

    private fun configureWebView() {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            allowFileAccess = false
            allowContentAccess = false
            mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
            cacheMode = WebSettings.LOAD_DEFAULT
            setSupportMultipleWindows(false)
        }
        CookieManager.getInstance().setAcceptCookie(true)
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true)

        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView, url: String) {
                splashView.animate().alpha(0f).setDuration(300)
                    .withEndAction { splashView.visibility = View.GONE }.start()
            }
            override fun shouldOverrideUrlLoading(view: WebView, request: WebResourceRequest): Boolean {
                val url = request.url.toString()
                if (!url.startsWith(BASE_URL)) {
                    startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
                    return true
                }
                return false
            }
            override fun onReceivedError(view: WebView, request: WebResourceRequest, error: WebResourceError) {
                if (request.isForMainFrame && !isNetworkAvailable()) showOfflineScreen()
            }
        }
    }

    override fun onBackPressed() {
        if (webView.canGoBack()) webView.goBack() else super.onBackPressed()
    }

    private fun isNetworkAvailable(): Boolean {
        val cm = getSystemService(CONNECTIVITY_SERVICE) as ConnectivityManager
        val network = cm.activeNetwork ?: return false
        val caps = cm.getNetworkCapabilities(network) ?: return false
        return caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
    }

    private fun showOfflineScreen() {
        // Native layout with "Sin conexión" + retry button
    }
}
```

---

## Appendix C: API Response Format (Universal)

**Success**:
```json
{
  "success": true,
  "message": "...",
  "data": { ... },
  "meta": { "current_page": 1, "last_page": 5, "total": 95 }
}
```

**Error**:
```json
{
  "success": false,
  "message": "Error description",
  "errors": { "username": ["The username field is required."] }
}
```
