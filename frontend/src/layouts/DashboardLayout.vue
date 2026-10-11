<template>
  <!-- ═══════════════════════════════════════════════════
       SICE Dashboard Layout — Adaptive Shell
       Desktop: Sidebar + Top Navbar + Content
       Mobile:  MobileHeader + BottomNav + FAB + Drawer
       ═══════════════════════════════════════════════════ -->
  <div>
    <!-- ─── Global Loading Overlay ─── -->
    <div v-if="appStore.loading" class="loading-overlay">
      <div class="loading-glass">
        <div class="loading-spinner"></div>
        <span class="loading-text">Cargando...</span>
      </div>
    </div>

    <!-- ═══════════ DESKTOP SIDEBAR ═══════════ -->
    <div v-if="mobileSidebarOpen" class="sidebar-overlay" @click="mobileSidebarOpen=false"></div>
    <nav
      class="sidebar"
      :class="{
        collapsed: appStore.sidebarCollapsed,
        'mobile-open': mobileSidebarOpen
      }"
      @mouseenter="onHover(true)"
      @mouseleave="onHover(false)"
    >
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <img v-if="siteLogo" :src="siteLogo" style="width:100%;height:100%;object-fit:contain" />
          <i v-else class="fas fa-school"></i>
        </div>
        <div>
          <div class="sidebar-title">CETIS</div>
          <div class="sidebar-subtitle">Control Escolar</div>
        </div>
      </div>

      <div class="sidebar-user">
        <div class="user-avatar">{{ authStore.nombreCompleto?.charAt(0) || 'U' }}</div>
        <div class="user-info">
          <div class="user-name">{{ authStore.nombreCompleto }}</div>
          <div class="user-role">{{ authStore.userRole || 'Usuario' }}</div>
        </div>
      </div>

      <div class="sidebar-search" v-if="!appStore.sidebarCollapsed">
        <input type="text" placeholder="Buscar módulo..." v-model="sidebarSearch" />
      </div>

      <ul class="sidebar-nav">
        <li class="nav-item">
          <router-link to="/" class="nav-link" data-tooltip="Dashboard">
            <i class="fas fa-chart-pie"></i><span class="nav-text">Dashboard</span>
          </router-link>
        </li>

        <!-- ═══════ PORTAL DEL ALUMNO ═══════ -->
        <template v-if="authStore.hasRole('alumno') && !authStore.hasPermission('alumnos.listar')">
          <li class="sidebar-section">Mi Portal</li>
          <li class="nav-item"><router-link to="/portal" class="nav-link"><i class="fas fa-chart-pie"></i><span class="nav-text">Ir al Portal</span></router-link></li>
        </template>

        <li class="sidebar-section">Académico</li>

        <li class="nav-item" v-if="authStore.hasPermission('alumnos.listar')">
          <a class="nav-link" @click="submenuAlumnos = !submenuAlumnos" data-tooltip="Alumnos">
            <i class="fas fa-user-graduate"></i><span class="nav-text">Alumnos</span>
            <i class="fas fa-chevron-down submenu-arrow" :class="{ open: submenuAlumnos }"></i>
          </a>
          <div class="submenu" :class="{ open: submenuAlumnos }">
            <router-link to="/alumnos" class="submenu-link">
              <i class="fas fa-user-plus"></i><span>Registro</span>
            </router-link>
            <router-link to="/alumnos/seguimiento" class="submenu-link">
              <i class="fas fa-chart-line"></i><span>Seguimiento</span>
            </router-link>
          </div>
        </li>
        <li class="nav-item" v-if="authStore.hasPermission('docentes.listar')">
          <a class="nav-link" @click="submenuDocentes = !submenuDocentes" data-tooltip="Docentes">
            <i class="fas fa-chalkboard-user"></i><span class="nav-text">Docentes</span>
            <i class="fas fa-chevron-down submenu-arrow" :class="{ open: submenuDocentes }"></i>
          </a>
          <div class="submenu" :class="{ open: submenuDocentes }">
            <router-link to="/docentes" class="submenu-link">
              <i class="fas fa-user-plus"></i><span>Registro</span>
            </router-link>
            <router-link to="/docentes/seguimiento" class="submenu-link">
              <i class="fas fa-chart-line"></i><span>Seguimiento</span>
            </router-link>
          </div>
        </li>
        <li class="nav-item" v-if="authStore.hasPermission('grupos.listar')">
          <router-link to="/grupos" class="nav-link" data-tooltip="Grupos">
            <i class="fas fa-users"></i><span class="nav-text">Grupos</span>
          </router-link>
        </li>
        <li class="nav-item" v-if="authStore.hasPermission('especialidades.listar')">
          <router-link to="/especialidades" class="nav-link">
            <i class="fas fa-graduation-cap"></i><span class="nav-text">Especialidades</span>
          </router-link>
        </li>

        <li class="nav-item" v-if="authStore.hasPermission('calificaciones.listar')">
          <router-link to="/calificaciones" class="nav-link" data-tooltip="Calificaciones">
            <i class="fas fa-star"></i><span class="nav-text">Calificaciones</span>
          </router-link>
        </li>

        <li class="sidebar-section">Administrativo</li>
        <li class="nav-item" v-if="authStore.hasPermission('usuarios.listar')">
          <router-link to="/usuarios" class="nav-link" data-tooltip="Usuarios">
            <i class="fas fa-users-gear"></i><span class="nav-text">Usuarios</span>
          </router-link>
        </li>
        <li class="nav-item" v-if="authStore.hasPermission('configuracion.ver')">
          <router-link to="/configuracion" class="nav-link" data-tooltip="Configuración">
            <i class="fas fa-gear"></i><span class="nav-text">Configuración</span>
          </router-link>
        </li>
      </ul>

      <div class="sidebar-footer" v-if="!appStore.sidebarCollapsed">
        <div class="text-xs text-muted" style="opacity:0.5">SICE v1.0 &copy; 2026</div>
      </div>
    </nav>

    <!-- ═══════════ MAIN CONTENT AREA ═══════════ -->
    <div class="main-content" :class="{ expanded: appStore.sidebarCollapsed }">
      <!-- ─── DESKTOP TOP NAVBAR ─── -->
      <nav class="top-navbar">
        <div class="navbar-left">
          <button class="btn-toggle-sidebar" @click="toggleSidebar">
            <i :class="appStore.sidebarCollapsed ? 'fas fa-bars' : 'fas fa-outdent'"></i>
          </button>
          <div class="breadcrumb">
            <span class="breadcrumb-item"><router-link to="/">SICE</router-link></span>
            <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
            <span class="breadcrumb-item active" v-if="breadcrumb">{{ breadcrumb }}</span>
          </div>
          <button class="btn-toggle-nav-right" @click="showNavRight = !showNavRight" :title="showNavRight ? 'Ocultar panel' : 'Mostrar panel'">
            <i class="fas fa-ellipsis-vertical"></i>
          </button>
        </div>

        <transition name="nav-slide">
          <div class="navbar-right" v-if="showNavRight">
            <div class="dropdown d-flex">
              <button class="navbar-btn" title="Notificaciones" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                <span v-if="notifCount > 0" class="badge-count">{{ notifCount > 9 ? '9+' : notifCount }}</span>
              </button>
              <div class="dropdown-menu dropdown-menu-end dropdown-notif">
                <div class="dropdown-header">Notificaciones</div>
                <div v-if="notificaciones.length === 0" class="p-4 text-center">
                  <i class="fas fa-bell-slash fa-2x mb-2" style="color:var(--color-text-muted);opacity:0.5"></i>
                  <p class="text-muted small mb-0">Sin notificaciones</p>
                </div>
                <div v-for="n in notificaciones" :key="n.id" class="dropdown-item d-flex gap-2 align-items-start" :class="{ 'fw-semibold': !n.leida }" style="cursor:default">
                  <span v-if="!n.leida" class="badge-count" style="position:static;flex-shrink:0;margin-top:2px"></span>
                  <div>
                    <div class="small">{{ n.titulo }}</div>
                    <div class="text-xs text-muted">{{ n.mensaje }}</div>
                  </div>
                </div>
                <div class="dropdown-divider"></div>
                <router-link to="/notificaciones" class="dropdown-item text-center small">Ver todas</router-link>
              </div>
            </div>
          </div>
        </transition>

        <div class="dropdown d-flex">
          <button class="navbar-btn" @click="userMenuOpen = !userMenuOpen">
            <div class="avatar-sm">{{ authStore.nombreCompleto?.charAt(0) || 'U' }}</div>
          </button>
          <div class="dropdown-menu dropdown-menu-end" :class="{ show: userMenuOpen }" @click="userMenuOpen = false" style="right:0;left:auto">
            <div class="dropdown-header">{{ authStore.nombreCompleto }}</div>
            <div class="dropdown-divider"></div>
            <router-link to="/notificaciones" class="dropdown-item" @click="userMenuOpen = false"><i class="fas fa-bell"></i>Notificaciones</router-link>
            <router-link to="/configuracion" class="dropdown-item" v-if="authStore.hasPermission('configuracion.ver')" @click="userMenuOpen = false"><i class="fas fa-gear"></i>Configuración</router-link>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" @click.prevent="userMenuOpen = false; logout()"><i class="fas fa-right-from-bracket"></i>Cerrar sesión</a>
          </div>
        </div>
      </nav>

      <!-- ═══════════ MOBILE HEADER ═══════════ -->
      <MobileHeader
        :title="breadcrumb || 'SICE'"
        :notifCount="notifCount"
        @toggle-sidebar="toggleSidebar"
        @open-search="showSearch = true"
        @open-notifications="openMobileNotifications"
        @open-user-menu="userMenuOpen = !userMenuOpen"
      />

      <!-- ─── Page Content ─── -->
      <div class="content-wrapper">
        <router-view />
      </div>
    </div>

    <!-- ═══════════ MOBILE BOTTOM NAVIGATION ═══════════ -->
    <BottomNav @open-more="showMoreMenu = true" />

    <!-- ═══════════ MOBILE FLOATING ACTION BUTTON ═══════════ -->
    <FabButton @action="onFabAction" />

    <!-- ═══════════ MOBILE SEARCH OVERLAY ═══════════ -->
    <SearchOverlay
      :visible="showSearch"
      @close="showSearch = false"
    />

    <!-- ═══════════ MOBILE "MORE" BOTTOM SHEET ═══════════ -->
    <teleport to="body">
      <transition name="sheet-transition">
        <div v-if="showMoreMenu" class="more-overlay" @click.self="showMoreMenu = false">
          <div class="more-sheet" :class="{ open: showMoreMenu }">
            <div class="more-sheet-handle" @click="showMoreMenu = false">
              <div class="more-sheet-handle-bar"></div>
            </div>
            <div class="more-sheet-header">
              <h3 class="more-sheet-title">Más opciones</h3>
              <button class="more-sheet-close" @click="showMoreMenu = false">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="more-sheet-body">
              <!-- Docentes -->
              <template v-if="authStore.hasPermission('docentes.listar')">
                <div class="more-section-label">Docentes</div>
                <router-link to="/docentes" class="more-item" @click="showMoreMenu = false">
                  <div class="more-item-icon"><i class="fas fa-user-plus"></i></div>
                  <div class="more-item-text">
                    <span class="more-item-title">Registro</span>
                  </div>
                  <i class="fas fa-chevron-right more-item-arrow"></i>
                </router-link>
                <router-link to="/docentes/seguimiento" class="more-item" @click="showMoreMenu = false">
                  <div class="more-item-icon"><i class="fas fa-chart-line"></i></div>
                  <div class="more-item-text">
                    <span class="more-item-title">Seguimiento</span>
                  </div>
                  <i class="fas fa-chevron-right more-item-arrow"></i>
                </router-link>
              </template>

              <!-- Especialidades -->
              <div class="more-section-label">Académico</div>
              <router-link v-if="authStore.hasPermission('especialidades.listar')" to="/especialidades" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="more-item-text"><span class="more-item-title">Especialidades</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('materias.listar')" to="/materias" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-book-open"></i></div>
                <div class="more-item-text"><span class="more-item-title">Materias</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('inscripciones.listar')" to="/inscripciones" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-file-signature"></i></div>
                <div class="more-item-text"><span class="more-item-title">Inscripciones</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>

              <!-- Operaciones -->
              <div class="more-section-label">Operaciones</div>
              <router-link v-if="authStore.hasPermission('asistencia.listar')" to="/asistencia" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-clipboard-check"></i></div>
                <div class="more-item-text"><span class="more-item-title">Asistencia</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link to="/horarios" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-clock"></i></div>
                <div class="more-item-text"><span class="more-item-title">Horarios</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('tutores.listar')" to="/tutores" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-user-tie"></i></div>
                <div class="more-item-text"><span class="more-item-title">Tutores</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('regularizacion.listar')" to="/regularizacion" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-rotate"></i></div>
                <div class="more-item-text"><span class="more-item-title">Regularización</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>

              <!-- Sistema -->
              <div class="more-section-label">Sistema</div>
              <router-link v-if="authStore.hasPermission('reportes.ver')" to="/reportes" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-file-pdf"></i></div>
                <div class="more-item-text"><span class="more-item-title">Reportes</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link to="/notificaciones" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon" style="position:relative">
                  <i class="fas fa-bell"></i>
                  <span v-if="notifCount > 0" class="more-item-badge">{{ notifCount > 9 ? '9+' : notifCount }}</span>
                </div>
                <div class="more-item-text"><span class="more-item-title">Notificaciones</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('auditoria.ver')" to="/auditoria" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-shield-halved"></i></div>
                <div class="more-item-text"><span class="more-item-title">Auditoría</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('usuarios.listar')" to="/usuarios" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-users-gear"></i></div>
                <div class="more-item-text"><span class="more-item-title">Usuarios</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('configuracion.ver')" to="/configuracion" class="more-item" @click="showMoreMenu = false">
                <div class="more-item-icon"><i class="fas fa-gear"></i></div>
                <div class="more-item-text"><span class="more-item-title">Configuración</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>

              <!-- Cerrar sesión -->
              <div class="more-section-label"></div>
              <button class="more-item more-item-btn more-item-danger" @click="showMoreMenu = false; logout()">
                <div class="more-item-icon"><i class="fas fa-right-from-bracket"></i></div>
                <div class="more-item-text"><span class="more-item-title">Cerrar sesión</span></div>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- ═══════════ MOBILE NOTIFICATIONS PANEL (quick view) ═══════════ -->
    <teleport to="body">
      <transition name="sheet-transition">
        <div v-if="showMobileNotif" class="more-overlay" @click.self="showMobileNotif = false">
          <div class="more-sheet more-sheet-notif" :class="{ open: showMobileNotif }">
            <div class="more-sheet-handle" @click="showMobileNotif = false">
              <div class="more-sheet-handle-bar"></div>
            </div>
            <div class="more-sheet-header">
              <h3 class="more-sheet-title">Notificaciones</h3>
              <router-link to="/notificaciones" class="more-sheet-action" @click="showMobileNotif = false">Ver todas</router-link>
            </div>
            <div class="more-sheet-body">
              <div v-if="notificaciones.length === 0" style="text-align:center;padding:3rem 1rem;color:var(--color-text-muted)">
                <i class="fas fa-bell-slash fa-2x mb-3" style="opacity:0.4;display:block"></i>
                <span>Sin notificaciones</span>
              </div>
              <div v-for="n in notificaciones" :key="n.id" class="more-item more-item-notif">
                <span v-if="!n.leida" class="more-item-dot"></span>
                <div class="more-item-text">
                  <span class="more-item-title">{{ n.titulo }}</span>
                  <span class="more-item-subtitle">{{ n.mensaje }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- ═══════════ MOBILE USER MENU BOTTOM SHEET ═══════════ -->
    <teleport to="body">
      <transition name="sheet-transition">
        <div v-if="userMenuOpen && isMobile" class="more-overlay" @click.self="userMenuOpen = false">
          <div class="more-sheet more-sheet-user" :class="{ open: userMenuOpen }">
            <div class="more-sheet-handle" @click="userMenuOpen = false">
              <div class="more-sheet-handle-bar"></div>
            </div>
            <div class="more-sheet-user-header">
              <div class="user-avatar" style="width:48px;height:48px;font-size:1.25rem;border-radius:50%">
                {{ authStore.nombreCompleto?.charAt(0) || 'U' }}
              </div>
              <div>
                <div style="font-weight:600;color:var(--color-text)">{{ authStore.nombreCompleto }}</div>
                <div style="font-size:0.75rem;color:var(--color-text-muted)">{{ authStore.userRole || 'Usuario' }}</div>
              </div>
            </div>
            <div class="more-sheet-body">
              <router-link to="/notificaciones" class="more-item" @click="userMenuOpen = false">
                <div class="more-item-icon"><i class="fas fa-bell"></i></div>
                <div class="more-item-text"><span class="more-item-title">Notificaciones</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <router-link v-if="authStore.hasPermission('configuracion.ver')" to="/configuracion" class="more-item" @click="userMenuOpen = false">
                <div class="more-item-icon"><i class="fas fa-gear"></i></div>
                <div class="more-item-text"><span class="more-item-title">Configuración</span></div>
                <i class="fas fa-chevron-right more-item-arrow"></i>
              </router-link>
              <button class="more-item more-item-btn more-item-danger" @click="userMenuOpen = false; logout()">
                <div class="more-item-icon"><i class="fas fa-right-from-bracket"></i></div>
                <div class="more-item-text"><span class="more-item-title">Cerrar sesión</span></div>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

  </div>
</template>

<script>
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import NotificacionService from '@/services/NotificacionService'
import api from '@/api/axios'
import MobileHeader from '@/components/navigation/MobileHeader.vue'
import BottomNav from '@/components/navigation/BottomNav.vue'
import FabButton from '@/components/navigation/FabButton.vue'
import SearchOverlay from '@/components/navigation/SearchOverlay.vue'

export default {
  name: 'DashboardLayout',
  components: {
    MobileHeader,
    BottomNav,
    FabButton,
    SearchOverlay,
  },
  data() {
    return {
      notificaciones: [],
      notifCount: 0,
      sidebarSearch: '',
      submenuAlumnos: false,
      submenuDocentes: false,
      siteLogo: null,
      userMenuOpen: false,
      mobileSidebarOpen: false,
      showNavRight: true,
      /* Mobile shell state */
      showSearch: false,
      showMoreMenu: false,
      showMobileNotif: false,
    }
  },
  computed: {
    authStore() {
      return useAuthStore()
    },
    appStore() {
      return useAppStore()
    },
    breadcrumb() {
      return this.$route.meta?.title || ''
    },
    isMobile() {
      return window.innerWidth <= 768
    },
  },
  watch: {
    '$route.path'(path) {
      if (path.startsWith('/alumnos')) this.submenuAlumnos = true
      if (path.startsWith('/docentes')) this.submenuDocentes = true
      this.mobileSidebarOpen = false
      this.showMoreMenu = false
      this.showMobileNotif = false
      this.userMenuOpen = false
    },
  },
  mounted() {
    this.fetchNotificaciones()
    this.fetchSiteLogo()
    document.addEventListener('click', this.onClickOutside)
    if (this.$route.path.startsWith('/alumnos')) this.submenuAlumnos = true
    if (this.$route.path.startsWith('/docentes')) this.submenuDocentes = true
    /* Lock body scroll when bottom sheets are open */
    this.$watch('showMoreMenu', this.toggleBodyScroll)
    this.$watch('showMobileNotif', this.toggleBodyScroll)
    this.$watch('showSearch', this.toggleBodyScroll)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.onClickOutside)
    document.body.style.overflow = ''
  },
  methods: {
    toggleSidebar() {
      if (window.innerWidth <= 768) {
        this.mobileSidebarOpen = !this.mobileSidebarOpen
      } else {
        this.appStore.toggleSidebar()
      }
    },
    onHover(enter) {
      if (this.appStore.sidebarCollapsed) {
        this.appStore.sidebarCollapsed = !enter
      }
    },
    async fetchNotificaciones() {
      try {
        const { data } = await NotificacionService.getNoLeidas()
        this.notifCount = data.data?.count || 0
        this.notificaciones = (data.data?.notificaciones || []).slice(0, 5)
      } catch (e) { console.error(e) }
    },
    async fetchSiteLogo() {
      try {
        if (!this.authStore.hasPermission('configuracion.ver')) return;
        const { data } = await api.get('/configuracion')
        const configs = data.data || data || []
        const logo = configs.find(c => c.clave === 'site_logo')
        if (logo?.valor) this.siteLogo = import.meta.env.VITE_API_URL.replace('/api/v1','') + '/storage/' + logo.valor
        const fav = configs.find(c => c.clave === 'site_favicon')
        if (fav?.valor) {
          let link = document.querySelector("link[rel='icon']")
          if (!link) { link = document.createElement('link'); link.rel = 'icon'; document.head.appendChild(link) }
          link.href = import.meta.env.VITE_API_URL.replace('/api/v1','') + '/storage/' + fav.valor
        }
      } catch (e) { /* */ }
    },
    onClickOutside(e) {
      if (!e.target.closest('.navbar-right .dropdown') && !e.target.closest('.navbar-btn')) {
        if (!this.isMobile) this.userMenuOpen = false
      }
    },
    async logout() {
      await this.authStore.logout()
      this.$router.push('/login')
    },
    /* Mobile-specific methods */
    openMobileNotifications() {
      this.showMobileNotif = true
    },
    onFabAction(payload) {
      // Emitted when FAB is tapped. The specific view can listen via provide/inject
      // or global event bus. For now, if it's a 'navigate' action the FAB
      // handles navigation internally. If 'create', views can watch for this.
      if (payload.type === 'create') {
        // Views that need to react to FAB create should implement their own
        // listener or we provide a simple global event mechanism.
        window.dispatchEvent(new CustomEvent('sice:fab-create', { detail: payload }))
      }
    },
    toggleBodyScroll(open) {
      if (open) {
        document.body.style.overflow = 'hidden'
      } else {
        // Only restore if no other mobile overlay is open
        if (!this.showMoreMenu && !this.showMobileNotif && !this.showSearch) {
          document.body.style.overflow = ''
        }
      }
    },
  },
}
</script>

<style scoped>
/* ─── Loading Overlay ─── */
.loading-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.45);
  backdrop-filter: blur(4px);
}
.loading-glass {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-4);
  padding: var(--space-8) var(--space-10);
  background: var(--glass-bg-elevated);
  backdrop-filter: var(--glass-blur-lg);
  border-radius: var(--radius-xl);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
}
.loading-spinner {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  animation: spin 0.7s linear infinite;
}
.loading-text {
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
  letter-spacing: 0.04em;
}

/* ─── "More" Bottom Sheet ─── */
.more-overlay {
  position: fixed;
  inset: 0;
  z-index: 1051;
  background: rgba(0,0,0,0.55);
  backdrop-filter: blur(3px);
  -webkit-backdrop-filter: blur(3px);
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.more-sheet {
  width: 100%;
  max-width: 500px;
  max-height: 80vh;
  background: var(--color-bg-secondary);
  border-radius: 20px 20px 0 0;
  box-shadow: 0 -8px 40px rgba(0,0,0,0.5);
  display: flex;
  flex-direction: column;
  border: 1px solid var(--glass-border);
  border-bottom: none;
  overflow: hidden;
}

.more-sheet-handle {
  display: flex;
  justify-content: center;
  padding: 12px 0 4px;
  cursor: grab;
  flex-shrink: 0;
}

.more-sheet-handle-bar {
  width: 36px;
  height: 4px;
  border-radius: 2px;
  background: var(--color-text-muted);
  opacity: 0.4;
}

.more-sheet-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--space-4) var(--space-3);
  flex-shrink: 0;
}

.more-sheet-title {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  color: var(--color-text);
  margin: 0;
}

.more-sheet-action {
  font-size: var(--font-size-sm);
  color: var(--color-primary);
  text-decoration: none;
  font-weight: var(--font-weight-medium);
}

.more-sheet-close {
  background: none;
  border: none;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: var(--color-text-muted);
  cursor: pointer;
  font-size: 1rem;
}

.more-sheet-body {
  flex: 1;
  overflow-y: auto;
  padding: 0 var(--space-1) var(--space-8);
}

.more-sheet-body::-webkit-scrollbar {
  width: 3px;
}

.more-section-label {
  padding: var(--space-4) var(--space-4) var(--space-2);
  font-size: 0.625rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--color-text-muted);
  font-weight: var(--font-weight-bold);
}

.more-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: 0.75rem var(--space-3);
  margin: 0 var(--space-3);
  border-radius: var(--radius-lg);
  color: var(--color-text);
  text-decoration: none;
  cursor: pointer;
  transition: background var(--transition-fast);
  border: none;
  background: transparent;
  width: calc(100% - var(--space-6));
  font-family: var(--font-family);
  font-size: var(--font-size-sm);
}

.more-item:active,
.more-item:hover {
  background: var(--color-bg-tertiary);
}

.more-item-btn {
  text-align: left;
}

.more-item-danger {
  color: var(--color-danger);
}

.more-item-icon {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-md);
  background: var(--color-bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.9375rem;
  color: var(--color-text-secondary);
  position: relative;
}

.more-item-text {
  flex: 1;
  min-width: 0;
}

.more-item-title {
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
  display: block;
}

.more-item-subtitle {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
  display: block;
  margin-top: 2px;
}

.more-item-arrow {
  color: var(--color-text-muted);
  font-size: 0.625rem;
  flex-shrink: 0;
}

.more-item-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-primary);
  flex-shrink: 0;
  margin: 0 4px;
}

.more-item-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 16px;
  height: 16px;
  border-radius: 8px;
  background: var(--color-danger);
  color: var(--color-text-inverse);
  font-size: 0.5625rem;
  font-weight: var(--font-weight-bold);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}

.more-item-notif {
  cursor: default;
  align-items: flex-start;
}

.more-sheet-user-header {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  padding: var(--space-4);
}

/* Bottom Sheet transitions */
.sheet-transition-enter-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sheet-transition-leave-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.sheet-transition-enter-from .more-sheet,
.sheet-transition-leave-to .more-sheet {
  transform: translateY(100%);
}
.sheet-transition-enter-from,
.sheet-transition-leave-to {
  opacity: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
