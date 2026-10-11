<template>
  <div class="dashboard-root">
    <!-- ── Page Header ── -->
    <div class="page-header">
      <div class="page-title">
        <i class="fas fa-chart-pie"></i>Dashboard
      </div>
      <div class="page-actions">
        <span class="badge badge-neutral">
          <i class="fas fa-calendar-day me-1"></i>{{ currentDate }}
        </span>
      </div>
    </div>

    <!-- ── Glow Cards ── -->
    <div class="glow-cards-grid">
      <div
        v-for="(card, idx) in glowCards"
        :key="card.label"
        class="glow-card-wrap"
        @mousemove="onCardMouseMove($event, idx)"
        @mouseleave="onCardMouseLeave(idx)"
        @touchstart="onCardTouchStart($event, idx)"
        @touchmove="onCardTouchMove($event, idx)"
        @touchend="onCardTouchEnd(idx)"
      >
        <div class="gc-grid">
          <div class="gc-area" v-for="n in 9" :key="n"></div>
          <div class="gc-card" :ref="el => { if (el) glowRefs[idx] = el }">
            <div class="gc-glow"></div>
            <div class="gc-inner-glow"></div>
            <div class="gc-bg"></div>
            <div class="gc-content">
              <div class="gc-header">
                <span class="gc-label">{{ card.label }}</span>
                <div class="gc-icon" :style="{ boxShadow: '0 0 1px 2px ' + card.color + '99, 0 -3px 2px 5px rgba(0,0,0,0.3), 0 0 1px 3px #000, 0 3px 3px 2px ' + card.color + '66' }">
                  <i :class="card.icon" :style="{ color: card.color }"></i>
                </div>
              </div>
              <div class="gc-value">
                <div class="gc-number">
                  <div v-for="(digit, di) in String(card.value).padStart(4, '0').split('')" :key="di" class="gc-digit-col" :style="{ '--num': digit }">
                    <span v-for="n in 10" :key="n">{{ n - 1 }}</span>
                  </div>
                </div>
              </div>
              <div class="gc-spark">
                <svg viewBox="0 0 120 36" preserveAspectRatio="none">
                  <defs>
                    <linearGradient :id="'gcSparkGrad' + idx" x1="0" x2="1" y1="0" y2="0">
                      <stop offset="0%" :stop-color="card.color" stop-opacity="0.2"/>
                      <stop offset="50%" :stop-color="card.color" stop-opacity="1"/>
                      <stop offset="100%" :stop-color="card.color" stop-opacity="0.2"/>
                    </linearGradient>
                  </defs>
                  <path :d="card.sparkline" fill="none" :stroke="'url(#gcSparkGrad' + idx + ')'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Secondary Stats ── -->
    <div class="stats-row">
      <div class="mini-stat" v-for="s in secondaryStats" :key="s.label">
        <i :class="s.icon" :style="{ color: s.color }"></i>
        <div class="mini-stat-text">
          <div class="mini-stat-value">{{ s.value }}</div>
          <div class="mini-stat-label">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- ── Charts ── -->
    <div class="charts-row">
      <div class="chart-card chart-card-donut">
        <div class="chart-card-header">
          <i class="fas fa-chart-pie"></i>Calificaciones
        </div>
        <div class="chart-card-body">
          <div class="chart-inner">
            <canvas ref="calCanvas"></canvas>
          </div>
        </div>
      </div>
      <div class="chart-card chart-card-bar">
        <div class="chart-card-header">
          <i class="fas fa-chart-column"></i>Alumnos por Semestre
        </div>
        <div class="chart-card-body chart-bar-body">
          <div class="chart-bar-scroll">
            <canvas ref="semCanvas" height="240"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- ── News ── -->
    <div class="news-section">
      <div class="news-header">
        <i class="fas fa-newspaper"></i>Noticias y Avisos
      </div>
      <div class="news-body">
        <div v-if="!stats.noticias || stats.noticias.length === 0" class="empty-state">
          <i class="fas fa-newspaper empty-state-icon"></i>
          <div class="empty-state-title">Sin noticias</div>
          <p class="empty-state-description">No hay noticias publicadas en este momento.</p>
        </div>
        <div v-else class="news-list">
          <div v-for="(n, ni) in stats.noticias" :key="n.id" class="news-item" :style="{ animationDelay: ni * 0.1 + 's' }">
            <div class="news-dot"></div>
            <div class="news-content">
              <strong>{{ n.titulo }}</strong>
              <small>{{ n.fecha_publicacion || n.fecha }}</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import api from '@/api/axios'
Chart.register(...registerables, ChartDataLabels)

function genSparkline(peaks, min, max) {
  const w = 120, h = 36, pad = 4
  const xStep = (w - pad * 2) / (peaks - 1)
  const range = max - min || 1
  let d = `M ${pad} `
  for (let i = 0; i < peaks; i++) {
    const x = pad + i * xStep
    const y = pad + (h - pad * 2) * (1 - (Math.random() * 0.6 + 0.2))
    d += (i === 0 ? '' : 'L ') + x.toFixed(1) + ' ' + y.toFixed(1) + ' '
  }
  return d
}

const sparklines = {
  alumnos: genSparkline(10),
  docentes: genSparkline(10),
  grupos: genSparkline(10),
  materias: genSparkline(10),
}

export default {
  name: 'DashboardView',
  data() {
    return {
      stats: {},
      chartCal: null,
      chartSem: null,
      glowRefs: {},
      currentDate: new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
    }
  },
  computed: {
    themePrimary() {
      return getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim()
    },
    themePrimaryRgb() {
      return getComputedStyle(document.documentElement).getPropertyValue('--color-primary-rgb').trim()
    },
    glowCards() {
      const s = this.stats
      const c = this.themePrimary || '#C62828'
      return [
        { label: 'Alumnos', value: s.total_alumnos || 0, icon: 'fas fa-user-graduate', color: c, sparkline: sparklines.alumnos },
        { label: 'Docentes', value: s.total_docentes || 0, icon: 'fas fa-chalkboard-user', color: c, sparkline: sparklines.docentes },
        { label: 'Grupos', value: s.total_grupos || 0, icon: 'fas fa-users', color: c, sparkline: sparklines.grupos },
        { label: 'Materias', value: s.total_materias || 0, icon: 'fas fa-book-open', color: c, sparkline: sparklines.materias },
      ]
    },
    secondaryStats() {
      const s = this.stats
      return [
        { label: 'Especialidades', value: s.total_especialidades || 0, icon: 'fas fa-graduation-cap', color: '#60A5FA' },
        { label: 'Inscripciones', value: s.total_inscripciones || 0, icon: 'fas fa-file-signature', color: '#34D399' },
        { label: 'Egresados', value: s.total_egresados || 0, icon: 'fas fa-award', color: '#A78BFA' },
        { label: 'Activos', value: s.alumnos_activos || 0, icon: 'fas fa-user-check', color: '#F59E0B' },
      ]
    },
  },
  async mounted() {
    try {
      const [statsRes, chartsRes] = await Promise.all([
        api.get('/dashboard/stats'),
        api.get('/dashboard/charts'),
      ])
      this.stats = statsRes.data.data
      const ch = chartsRes.data.data
      this._chartsData = ch
      this.$nextTick(() => {
        if (ch.calificaciones) this.renderCal(ch.calificaciones)
        if (ch.alumnos_por_semestre) this.renderSem(ch.alumnos_por_semestre)
      })
    } catch (e) {
      console.error(e)
    }

    document.addEventListener('theme-colors-changed', this.reloadCharts)
  },
  methods: {
    /**
     * Resolve a CSS custom property from :root to a real color string
     * that Canvas 2D / Chart.js can use. Returns a fallback if the var
     * is empty (e.g. during SSR or initial mount before styles load).
     */
    _cssVar(name, fallback) {
      try {
        const raw = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
        return raw || fallback || ''
      } catch {
        return fallback || ''
      }
    },

    /* ── Theme-aware chart reload ── */
    async reloadCharts() {
      await this.$nextTick()
      setTimeout(() => {
        if (this._chartsData) {
          if (this._chartsData.calificaciones) this.renderCal(this._chartsData.calificaciones)
          if (this._chartsData.alumnos_por_semestre) this.renderSem(this._chartsData.alumnos_por_semestre)
        }
      }, 100)
    },
    /* ── Mouse tilt (desktop) ── */
    onCardMouseMove(e, idx) {
      this._applyTilt(e.clientX, e.clientY, idx)
    },
    onCardMouseLeave(idx) {
      this._resetTilt(idx)
    },
    /* ── Touch tilt (mobile) ── */
    onCardTouchStart(e, idx) {
      if (!e.touches || e.touches.length === 0) return
      this._applyTilt(e.touches[0].clientX, e.touches[0].clientY, idx)
    },
    onCardTouchMove(e, idx) {
      if (!e.touches || e.touches.length === 0) return
      this._applyTilt(e.touches[0].clientX, e.touches[0].clientY, idx)
    },
    onCardTouchEnd(idx) {
      this._resetTilt(idx)
    },
    _applyTilt(clientX, clientY, idx) {
      const el = this.glowRefs[idx]
      if (!el) return
      const rect = el.getBoundingClientRect()
      const x = clientX - rect.left
      const y = clientY - rect.top
      const rx = ((y / rect.height) - 0.5) * -18
      const ry = ((x / rect.width) - 0.5) * 18
      el.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) scale3d(1.02, 1.02, 1.02)`
    },
    _resetTilt(idx) {
      const el = this.glowRefs[idx]
      if (!el) return
      el.style.transform = 'perspective(800px) rotateX(0) rotateY(0) scale3d(1, 1, 1)'
    },
    /* ── Chart: Calificaciones (Donut) ── */
    renderCal(data) {
      const ctx = this.$refs.calCanvas?.getContext('2d')
      if (!ctx) return
      if (this.chartCal) this.chartCal.destroy()

      // Resolve CSS vars for Canvas (Chart.js writes to Canvas, Canvas does NOT resolve var())
      const successColor = this._cssVar('--color-success', '#2ECC71')
      const dangerColor = this._cssVar('--color-danger', '#E74C3C')
      const warningColor = this._cssVar('--color-warning', '#F39C12')
      const bgSecondary = this._cssVar('--color-bg-secondary', '#161B22')
      const textSecondary = this._cssVar('--color-text-secondary', '#8B949E')

      this.chartCal = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Aprobadas', 'Reprobadas', 'Cursando'],
          datasets: [{
            data: [data.aprobadas || 0, data.reprobadas || 0, data.cursando || 0],
            backgroundColor: [successColor, dangerColor, warningColor],
            borderColor: bgSecondary,
            borderWidth: 3,
            borderRadius: 4,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          cutout: '65%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 16,
                usePointStyle: true,
                pointStyleWidth: 8,
                pointStyleHeight: 8,
                font: { size: 11 },
                color: textSecondary,
              },
            },
            datalabels: { display: false },
          },
        },
      })
    },
    /* ── Chart: Alumnos por Semestre (Bar) ── */
    renderSem(data) {
      const ctx = this.$refs.semCanvas?.getContext('2d')
      if (!ctx) return
      const labels = Object.keys(data).map(k => 'Sem ' + k)
      const values = Object.values(data)
      if (this.chartSem) this.chartSem.destroy()

      // Resolve CSS vars for Canvas
      const textSecondary = this._cssVar('--color-text-secondary', '#8B949E')
      const borderColor = this._cssVar('--color-border', 'rgba(255,255,255,0.08)')

      this.chartSem = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Alumnos',
            data: values,
            backgroundColor: values.map((_, i) => {
              const t = i / (values.length - 1 || 1)
              const c = this.themePrimaryRgb || '198,40,40'
              return `rgba(${c},${0.3 + t * 0.7})`
            }),
            borderRadius: 8,
            borderSkipped: false,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 5, color: textSecondary },
              grid: { color: borderColor },
            },
            x: {
              ticks: { color: textSecondary },
              grid: { display: false },
            },
          },
          plugins: {
            legend: { display: false },
            datalabels: { display: false },
          },
        },
      })
    },
  },
  beforeUnmount() {
    if (this.chartCal) this.chartCal.destroy()
    if (this.chartSem) this.chartSem.destroy()
    document.removeEventListener('theme-colors-changed', this.reloadCharts)
  },
}
</script>

<style scoped>
/* ============================================================
   DASHBOARD — Mobile-First Adaptive Layout
   Glass-token driven. Zero new dependencies. Touch + Mouse.
   ============================================================ */

/* ── Root Container ── */
.dashboard-root {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ── Page Header ── */
.page-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}
.page-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-text);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  letter-spacing: -0.01em;
}
.page-title i {
  color: var(--color-primary);
}
.badge-neutral {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  color: var(--color-text-secondary);
  font-size: 0.7rem;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  white-space: nowrap;
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
}

/* ============================================================
   GLOW CARDS
   ============================================================ */

/* ── Grid ── */
.glow-cards-grid {
  display: grid;
  /* Mobile: 2 per row */
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

/* ── Wrap ── */
.glow-card-wrap {
  position: relative;
  overflow: hidden;
  -webkit-tap-highlight-color: transparent;
  user-select: none;
  border-radius: 18px;
}

/* ── Touch feedback: subtle scale-down on press ── */
.glow-card-wrap:active {
  transform: scale(0.97);
  transition: transform 0.12s ease;
}

/* ── 3D grid container ── */
.gc-grid {
  position: relative;
  border-radius: 16px;
  min-height: 180px;
}

/* ── Area placeholders (not visible) ── */
.gc-area {
  position: absolute;
  z-index: 1;
  inset: 0;
}

/* ── 3D Card ── */
.gc-card {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 180px;
  border-radius: 16px;
  z-index: 9;
  transform-style: preserve-3d;
  will-change: transform;
  transition: transform 0.4s cubic-bezier(0.03, 1, 0.5, 1);
}

/* ── Outer Glow Ring ── */
.gc-glow {
  position: absolute;
  inset: -2px;
  border-radius: 18px;
  overflow: hidden;
  z-index: 0;
  opacity: 0.5;
  filter: blur(12px);
}
.gc-glow::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(from 0deg, var(--crystal-cyan), var(--color-primary) 20%, transparent 30%, var(--crystal-gold) 45%, transparent 55%, var(--crystal-purple) 70%, transparent 80%, var(--crystal-cyan) 100%);
  animation: gcSpin 8s linear infinite;
}
@keyframes gcSpin {
  to { transform: rotate(360deg); }
}

/* ── Inner Glow Layer ── */
.gc-inner-glow {
  position: absolute;
  inset: 5px;
  border-radius: 14px;
  overflow: hidden;
  z-index: 1;
  opacity: 0.2;
  filter: blur(24px);
}
.gc-inner-glow::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(from 180deg, var(--crystal-cyan), transparent 40%, var(--color-primary) 60%, transparent 90%, var(--crystal-purple) 100%);
  animation: gcSpin 12s linear infinite reverse;
}

/* ── Card Background (glass) ── */
.gc-bg {
  position: absolute;
  inset: 0;
  border-radius: 20px;
  background: linear-gradient(145deg, var(--color-bg-secondary), var(--color-bg));
  box-shadow: 0 8px 32px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.06);
  z-index: 2;
}

/* ═══ FF Materia color accent borders ═══ */
.glow-card-wrap:nth-child(1) .gc-bg { border-top: 2px solid var(--crystal-cyan); }
.glow-card-wrap:nth-child(2) .gc-bg { border-top: 2px solid var(--crystal-green); }
.glow-card-wrap:nth-child(3) .gc-bg { border-top: 2px solid var(--crystal-gold); }
.glow-card-wrap:nth-child(4) .gc-bg { border-top: 2px solid var(--crystal-purple); }

/* ═══ Crystal shard diamond decoration ═══ */
.gc-bg::after {
  content: '';
  position: absolute;
  top: 12px; right: 12px;
  width: 14px; height: 14px;
  background: linear-gradient(135deg, rgba(255,255,255,0.5), rgba(255,255,255,0.1));
  transform: rotate(45deg);
  border-radius: 1px;
  z-index: 20;
  opacity: 0.7;
}
.glow-card-wrap:nth-child(1) .gc-bg::after { box-shadow: 0 0 10px var(--crystal-cyan-glow); }
.glow-card-wrap:nth-child(2) .gc-bg::after { box-shadow: 0 0 10px var(--crystal-green-glow); }
.glow-card-wrap:nth-child(3) .gc-bg::after { box-shadow: 0 0 10px var(--crystal-gold-glow); }
.glow-card-wrap:nth-child(4) .gc-bg::after { box-shadow: 0 0 10px var(--crystal-purple-glow); }
/* Desktop hover: intensify border glow */
@media (min-width: 901px) {
  .glow-card-wrap:hover .gc-bg {
    box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.08), var(--rune-glow);
  }
}

/* ── Card Content ── */
.gc-content {
  position: relative;
  z-index: 3;
  padding: 0.875rem;
  height: 100%;
  display: flex;
  flex-direction: column;
  transform-style: preserve-3d;
}

/* ── Header (label + icon) ── */
.gc-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  transform: translateZ(20px);
  margin-bottom: 0.5rem;
}
.gc-label {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--color-text-muted);
  font-weight: 600;
  line-height: 1;
}
.gc-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  transform-style: preserve-3d;
  animation: gcPulse 2.5s ease-in-out infinite, materiaFloat 3s ease-in-out infinite;
  flex-shrink: 0;
}
@keyframes gcPulse {
  0%, 100% { transform: translateZ(0); }
  50% { transform: translateZ(15px); }
}

/* ── Value (big number) ── */
.gc-value {
  transform: translateZ(15px);
  margin-bottom: auto;
}
.gc-number {
  display: flex;
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--color-text);
  letter-spacing: -0.02em;
  line-height: 1;
}
.gc-digit-col {
  display: flex;
  flex-direction: column;
  height: 1.8rem;
  overflow: hidden;
  width: 0.58em;
  text-align: center;
}
.gc-digit-col:nth-child(n+3) { margin-left: 0.05em; }
.gc-digit-col span {
  height: 1.8rem;
  line-height: 1.8rem;
  animation: gcDigits 3.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}
@keyframes gcDigits {
  0% { transform: translateY(0); }
  80% { transform: translateY(calc(-100% * var(--num))); }
  100% { transform: translateY(calc(-100% * var(--num))); }
}

/* ── Sparkline ── */
.gc-spark {
  transform: translateZ(10px);
  margin-top: 0.25rem;
}
.gc-spark svg {
  display: block;
  width: 100%;
  height: 24px;
  overflow: visible;
}
.gc-spark path {
  stroke-dasharray: 200;
  stroke-dashoffset: 200;
  animation: gcSparkDraw 2s ease forwards 0.5s;
}
@keyframes gcSparkDraw {
  to { stroke-dashoffset: 0; }
}

/* ============================================================
   SECONDARY STATS
   ============================================================ */

.stats-row {
  display: grid;
  /* Mobile: 2x2 compact grid */
  grid-template-columns: repeat(2, 1fr);
  gap: 0.625rem;
}

.mini-stat {
  background: linear-gradient(145deg, var(--color-bg-secondary), var(--color-bg));
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 0.75rem 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.625rem;
  transition: all 0.25s ease;
  -webkit-tap-highlight-color: transparent;
  min-height: 48px; /* touch-friendly */
}

/* Touch feedback override (no hover on mobile) */
.mini-stat:active {
  border-color: var(--color-border-hover);
  transform: scale(0.97);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.mini-stat i {
  font-size: 1.25rem;
  opacity: 0.85;
  flex-shrink: 0;
  width: 28px;
  text-align: center;
}

.mini-stat-text {
  min-width: 0;
  flex: 1;
}

.mini-stat-value {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-text);
  line-height: 1.15;
}

.mini-stat-label {
  font-size: 0.6rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ============================================================
   CHARTS
   ============================================================ */

.charts-row {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.chart-card {
  background: linear-gradient(145deg, var(--color-bg-secondary), var(--color-bg));
  border: 1px solid var(--color-border);
  border-radius: 14px;
  overflow: hidden;
}

.chart-card-header {
  padding: 0.75rem 1rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.chart-card-header i {
  color: var(--color-primary);
}

.chart-card-body {
  padding: 1rem;
}

/* ── Donut ── */
.chart-inner {
  max-width: 180px;
  margin: 0 auto;
  aspect-ratio: 1;
}

/* ── Bar Chart: horizontal scroll on mobile ── */
.chart-bar-body {
  padding: 0.75rem 1rem 1rem;
}

.chart-bar-scroll {
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: var(--color-border) transparent;
}
.chart-bar-scroll::-webkit-scrollbar {
  height: 4px;
}
.chart-bar-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.chart-bar-scroll::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 4px;
}
.chart-bar-scroll canvas {
  /* Ensure the canvas is at least wide enough for all bars */
  min-height: 180px;
}

/* ============================================================
   NEWS SECTION
   ============================================================ */

.news-section {
  background: linear-gradient(145deg, var(--color-bg-secondary), var(--color-bg));
  border: 1px solid var(--color-border);
  border-radius: 14px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.news-header {
  padding: 0.75rem 1rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.news-header i {
  color: var(--color-primary);
}

.news-body {
  padding: 0;
}

.news-list {
  display: flex;
  flex-direction: column;
}

.news-item {
  display: flex;
  align-items: flex-start;
  gap: 0.625rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--color-border-light);
  animation: newsFadeIn 0.4s ease forwards;
  opacity: 0;
  -webkit-tap-highlight-color: transparent;
  min-height: 48px; /* touch-friendly tap area */
}

/* Touch feedback on news items */
.news-item:active {
  background: var(--color-bg-tertiary);
}

@keyframes newsFadeIn {
  from { opacity: 0; transform: translateX(-10px); }
  to { opacity: 1; transform: translateX(0); }
}
.news-item:last-child { border-bottom: none; }

.news-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: var(--color-primary);
  margin-top: 5px;
  flex-shrink: 0;
}

.news-content {
  min-width: 0;
  flex: 1;
}

.news-content strong {
  display: block;
  font-size: 0.8rem;
  color: var(--color-text);
  margin-bottom: 0.1rem;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.news-content small {
  font-size: 0.65rem;
  color: var(--color-text-muted);
}

/* ── Empty State ── */
.empty-state {
  text-align: center;
  padding: 2rem 1rem;
}
.empty-state-icon {
  font-size: 2rem;
  color: var(--color-text-muted);
  opacity: 0.4;
  margin-bottom: 0.5rem;
  display: block;
}
.empty-state-title {
  color: var(--color-text-muted);
  font-weight: 600;
  font-size: 0.85rem;
}
.empty-state-description {
  color: var(--color-text-muted);
  opacity: 0.7;
  margin: 0;
  font-size: 0.75rem;
}

/* ============================================================
   BREAKPOINT: 480px+ (phablet / large phones)
   ============================================================ */
@media (min-width: 480px) {
  .dashboard-root {
    gap: 1.25rem;
  }
  .glow-cards-grid {
    gap: 1rem;
  }
  .gc-grid {
    min-height: 180px;
  }
  .gc-card {
    min-height: 180px;
  }
  .gc-content {
    padding: 1rem;
  }
  .gc-number {
    font-size: 1.6rem;
  }
  .gc-digit-col {
    height: 2rem;
  }
  .gc-digit-col span {
    height: 2rem;
    line-height: 2rem;
  }
  .gc-spark svg {
    height: 28px;
  }
  .stats-row {
    gap: 0.75rem;
  }
  .mini-stat {
    padding: 0.875rem 1rem;
  }
  .mini-stat-value {
    font-size: 1.15rem;
  }
}

/* ============================================================
   BREAKPOINT: 600px+ (small tablets)
   ============================================================ */
@media (min-width: 600px) {
  .stats-row {
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
  }
  .charts-row {
    flex-direction: row;
    gap: 1rem;
  }
  .chart-card-donut {
    flex: 1;
    min-width: 0;
  }
  .chart-card-bar {
    flex: 2;
    min-width: 0;
  }
  .chart-bar-scroll {
    overflow-x: visible;
  }
}

/* ============================================================
   BREAKPOINT: 768px+ (tablets / small laptops)
   ============================================================ */
@media (min-width: 768px) {
  .dashboard-root {
    gap: 1.5rem;
  }
  .glow-cards-grid {
    gap: 1.25rem;
  }
  .gc-grid,
  .gc-card {
    min-height: 180px;
  }
  .gc-content {
    padding: 1.25rem;
  }
  .gc-label {
    font-size: 0.7rem;
  }
  .gc-icon {
    width: 36px;
    height: 36px;
    font-size: 0.95rem;
  }
  .gc-number {
    font-size: 1.75rem;
  }
  .gc-digit-col {
    height: 2.2rem;
  }
  .gc-digit-col span {
    height: 2.2rem;
    line-height: 2.2rem;
  }
  .gc-spark svg {
    height: 30px;
  }
  .stats-row {
    gap: 1rem;
  }
  .mini-stat {
    padding: 1rem 1.25rem;
    border-radius: 14px;
  }
  .mini-stat-value {
    font-size: 1.25rem;
  }
  .mini-stat-label {
    font-size: 0.65rem;
  }
  .chart-card {
    border-radius: 16px;
  }
  .chart-card-header {
    padding: 0.875rem 1.25rem;
    font-size: 0.85rem;
  }
  .chart-card-body {
    padding: 1.25rem;
  }
  .chart-inner {
    max-width: 220px;
  }
  .chart-bar-body {
    padding: 0.75rem 1.25rem 1.25rem;
  }
  .news-section {
    border-radius: 16px;
  }
  .news-header {
    padding: 0.875rem 1.25rem;
    font-size: 0.85rem;
  }
  .news-item {
    padding: 0.875rem 1.25rem;
  }
  .news-content strong {
    font-size: 0.85rem;
  }
  .news-content small {
    font-size: 0.7rem;
  }
}

/* ============================================================
   BREAKPOINT: 901px+ (desktop)
   ============================================================ */
@media (min-width: 901px) {
  .glow-cards-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
  }
  .gc-grid,
  .gc-card {
    min-height: 180px;
  }
  .gc-content {
    padding: 1.5rem;
  }
  .gc-label {
    font-size: 0.75rem;
  }
  .gc-icon {
    width: 38px;
    height: 38px;
    font-size: 1rem;
  }
  .gc-number {
    font-size: 1.85rem;
  }
  .gc-digit-col {
    height: 2.4rem;
  }
  .gc-digit-col span {
    height: 2.4rem;
    line-height: 2.4rem;
  }
  .gc-spark svg {
    height: 32px;
  }

  /* Hover: glow card surge (desktop-only, no touch conflict) */
  .glow-card-wrap:hover .gc-bg {
    box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.08);
    transition: box-shadow 0.35s ease;
  }
  .glow-card-wrap:hover .gc-glow {
    opacity: 0.7;
    filter: blur(18px);
    transition: opacity 0.35s ease, filter 0.35s ease;
  }

  /* Stats hover (desktop only) */
  .mini-stat:hover {
    border-color: var(--color-border-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  }

  /* News hover */
  .news-item:hover {
    background: var(--color-bg-tertiary);
  }
}

/* ============================================================
   BREAKPOINT: 1024px+ (desktop standard)
   ============================================================ */
@media (min-width: 1024px) {
  .dashboard-root {
    gap: 2rem;
  }
  .stats-row {
    gap: 1rem;
  }
}

/* ============================================================
   BREAKPOINT: 1280px+ (large desktop)
   ============================================================ */
@media (min-width: 1280px) {
  .glow-cards-grid {
    gap: 2rem;
  }
  .gc-grid,
  .gc-card {
    min-height: 180px;
  }
}

/* ============================================================
   ORIENTATION: Landscape on small devices
   ============================================================ */
@media (max-width: 768px) and (orientation: landscape) {
  .glow-cards-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
  }
  .gc-grid,
  .gc-card {
    min-height: 160px;
  }
  .gc-content {
    padding: 0.625rem;
  }
  .gc-label {
    font-size: 0.55rem;
  }
  .gc-icon {
    width: 26px;
    height: 26px;
    font-size: 0.7rem;
  }
  .gc-number {
    font-size: 1.15rem;
  }
  .gc-digit-col {
    height: 1.5rem;
  }
  .gc-digit-col span {
    height: 1.5rem;
    line-height: 1.5rem;
  }
  .gc-spark svg {
    height: 20px;
  }
  .stats-row {
    grid-template-columns: repeat(4, 1fr);
  }
  .charts-row {
    flex-direction: row;
    gap: 0.75rem;
  }
  .chart-card-donut {
    flex: 1;
  }
  .chart-card-bar {
    flex: 2;
  }
  .chart-bar-scroll {
    overflow-x: visible;
  }
  .chart-inner {
    max-width: 140px;
  }
}
</style>
