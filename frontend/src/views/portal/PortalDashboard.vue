<template>
  <div class="portal-home">
    <!-- Greeting -->
    <div class="greeting" v-if="alumno">
      <div class="greeting-avatar" v-if="alumno.foto"><img :src="alumno.foto" /></div>
      <div class="greeting-avatar placeholder" v-else><i class="fas fa-user-graduate"></i></div>
      <div class="greeting-text">
        <h1>Hola, {{ alumno.nombre?.split(' ')[0] }}</h1>
        <p>{{ alumno.especialidad || '—' }} · {{ alumno.semestre }}° · {{ alumno.grupo || '—' }}</p>
      </div>
    </div>

    <!-- Latest News -->
    <div class="news-section" v-if="ultimaNoticia">
      <div class="news-card">
        <img v-if="ultimaNoticia.imagen" :src="ultimaNoticia.imagen" class="news-img" @click.stop="showPhoto = ultimaNoticia.imagen" />
        <div class="news-body">
          <h6 class="news-title">{{ ultimaNoticia.titulo }}</h6>
          <p class="news-text" v-html="linkify(ultimaNoticia.contenido)"></p>
          <span class="news-date">{{ ultimaNoticia.fecha_publicacion || ultimaNoticia.created_at }}</span>
        </div>
      </div>
    </div>

    <!-- Photo Lightbox -->
    <div class="photo-overlay" v-if="showPhoto" @click="showPhoto = null">
      <img :src="showPhoto" class="photo-full" @click.stop />
      <button class="photo-close-btn" @click="showPhoto = null"><i class="fas fa-times"></i></button>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <button class="stat-card" @click="openDetail('materias')">
        <div class="stat-icon" style="background:rgba(14,165,233,.15);color:#38BDF8"><i class="fas fa-book-open"></i></div>
        <div class="stat-value">{{ materiasCount }}</div>
        <div class="stat-label">Materias</div>
      </button>
      <button class="stat-card" @click="openDetail('promedio')">
        <div class="stat-icon" style="background:rgba(59,130,246,.15);color:#60A5FA"><i class="fas fa-star"></i></div>
        <div class="stat-value">{{ resumen?.promedio_general || '—' }}</div>
        <div class="stat-label">Promedio</div>
      </button>
      <button class="stat-card" @click="openDetail('calificaciones')">
        <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34D399"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value">{{ resumen?.materias_aprobadas || 0 }}</div>
        <div class="stat-label">Aprobadas</div>
      </button>
      <button class="stat-card" @click="openDetail('horario')">
        <div class="stat-icon" style="background:rgba(139,92,246,.15);color:#A78BFA"><i class="fas fa-clock"></i></div>
        <div class="stat-value">{{ horarioCount }}</div>
        <div class="stat-label">Clases</div>
      </button>
      <button class="stat-card" @click="openDetail('asistencia')">
        <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#F59E0B"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-value">{{ (asistenciasData?.porcentaje || 100) }}%</div>
        <div class="stat-label">Asistencia</div>
      </button>
      <button class="stat-card" @click="openDetail('kardex')">
        <div class="stat-icon" style="background:rgba(236,72,153,.15);color:#F472B6"><i class="fas fa-file-alt"></i></div>
        <div class="stat-value">{{ materiasCount }}</div>
        <div class="stat-label">Materias</div>
      </button>
    </div>

    <!-- Avisos -->
    <div class="avisos-section" v-if="avisos?.length">
      <h6 class="section-sm"><i class="fas fa-bullhorn"></i> Avisos</h6>
      <div v-for="a in avisos.slice(0,3)" :key="a.id" class="aviso-item">
        <strong>{{ a.titulo }}</strong><p>{{ a.contenido }}</p>
      </div>
    </div>

    <!-- MODAL -->
    <div class="detail-overlay" v-if="activeDetail" @click.self="activeDetail = null">
      <div class="detail-modal">
        <div class="detail-modal-header">
          <button class="detail-close" @click="activeDetail = null"><i class="fas fa-times"></i></button>
          <h3>{{ detailTitle }}</h3>
        </div>
        <div class="detail-modal-body">

          <!-- PROMEDIO -->
          <template v-if="activeDetail === 'promedio'">
            <div class="detail-big">{{ resumen?.promedio_general || '—' }}</div>
            <div class="detail-sub">Basado en {{ resumen?.materias_cursadas || 0 }} materias</div>
            <div class="semester-list">
              <div v-for="p in kardexHistorial" :key="p.periodo" class="semester-row">
                <div class="semester-name">{{ semestreLabel(p.periodo) }}</div>
                <div class="semester-prom">{{ p.promedio_periodo || '—' }}</div>
                <div class="semester-detail">{{ p.materias?.length || 0 }} materias</div>
              </div>
            </div>
          </template>

          <!-- CALIFICACIONES -->
          <template v-if="activeDetail === 'calificaciones'">
            <div class="cal-periodo" v-for="(mats, periodo) in calificacionesGrouped" :key="periodo">
              <div class="cal-periodo-bar" @click="togglePeriodo(periodo)">
                <div class="cal-periodo-left">
                  <i class="fas fa-chevron-right cal-chevron" :class="{open: calOpen[periodo]}"></i>
                  <span class="cal-periodo-title">{{ semestreLabel(periodo) }}</span>
                </div>
                <span class="cal-periodo-count">{{ mats.length }} mat.</span>
              </div>
              <div class="cal-periodo-body" v-show="calOpen[periodo]">
                <div class="cal-item" v-for="c in mats" :key="c.id">
                  <div class="cal-item-top">
                    <span class="cal-item-name">{{ c.materia }}</span>
                    <span :class="[(c.promedio_final||0)>=7?'ok':'fail']" class="cal-item-badge">{{ c.promedio_final||'—' }}</span>
                  </div>
                  <div class="cal-item-parciales">
                    <span>P1: {{ c.parcial1 ?? '—' }}</span><span>P2: {{ c.parcial2 ?? '—' }}</span><span>P3: {{ c.parcial3 ?? '—' }}</span>
                    <span :class="c.estatus" class="cal-est">{{ c.estatus || '—' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- HORARIO CALENDAR -->
          <template v-if="activeDetail === 'horario'">
            <div class="cal-wrap">
            <div class="cal-schedule">
              <div class="cal-schedule-head">
                <div class="cal-sh-time"></div>
                <div v-for="d in diasSemana" :key="d.val" class="cal-sh-day">{{ d.label }}</div>
              </div>
              <div v-for="slot in calendarSlots" :key="slot.key" class="cal-schedule-row">
                <div class="cal-sr-time">{{ slot.label }}</div>
                <div v-for="d in diasSemana" :key="d.val" class="cal-sr-cell">
                  <div v-for="(h,i) in getBlocksCompact(d.val, slot)" :key="i"
                    class="cal-sr-block" :style="{background: h._color}">
                    <span class="cal-sr-subject">{{ h.materia }}</span>
                    <span class="cal-sr-teacher" v-if="h.docente">{{ h.docente }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- cal-wrap -->
          </template>

          <!-- MATERIAS -->
          <template v-if="activeDetail === 'materias'">
            <div v-for="m in materiasList" :key="m.nombre" class="materia-card">
              <div class="materia-name">{{ m.nombre }}</div>
              <div class="materia-info">
                <div class="materia-docente" v-if="m.docente"><i class="fas fa-chalkboard-user"></i> {{ m.docente }}</div>
                <div class="materia-horario" v-for="h in m.horarios" :key="h.id">
                  <i class="fas fa-clock"></i> {{ h.dia }} {{ h.hora_inicio }}–{{ h.hora_fin }}
                </div>
              </div>
            </div>
          </template>

          <!-- ASISTENCIA -->
          <template v-if="activeDetail === 'asistencia'">
            <div class="att-big">{{ asistenciasData?.porcentaje || 100 }}%</div>
            <div class="att-stats-row">
              <div class="att-stat"><span class="att-num">{{ asistenciasData?.total || 0 }}</span><span class="att-lbl">Registros</span></div>
              <div class="att-stat danger"><span class="att-num">{{ asistenciasData?.faltas || 0 }}</span><span class="att-lbl">Faltas</span></div>
            </div>
            <div class="att-list">
              <div v-for="a in asistenciasList" :key="a.id" class="att-row"><span>{{ a.fecha }}</span><span class="att-mat">{{ a.materia?.nombre||'—' }}</span><span :class="['att-badge','att-'+(a.estatus||'')]">{{ a.estatus }}</span></div>
            </div>
          </template>

          <!-- KARDEX -->
          <template v-if="activeDetail === 'kardex'">
            <div v-for="p in kardexHistorial" :key="p.periodo" class="kardex-p">
              <h6>{{ semestreLabel(p.periodo) }} <span class="text-muted">Prom: {{ p.promedio_periodo }}</span></h6>
              <table class="kardex-mini">
                <thead><tr><th>Materia</th><th>P1</th><th>P2</th><th>P3</th><th>Prom</th></tr></thead>
                <tbody>
                  <tr v-for="m in p.materias" :key="m.materia">
                    <td>{{ m.materia }}</td><td>{{ m.parcial1??'—' }}</td><td>{{ m.parcial2??'—' }}</td><td>{{ m.parcial3??'—' }}</td><td class="fw-bold">{{ m.promedio_final??'—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>

        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
  </div>
</template>

<script>
import PortalAlumnoService from '@/services/PortalAlumnoService'
import api from '@/api/axios'
export default {
  name: 'PortalDashboard',
  data() { return {       loading: true, activeDetail: null, activeDay: 0, calOpen: {}, showPhoto: null,
      alumno: null, resumen: null, avisos: [], ultimaNoticia: null, calificacionesGrouped: {}, kardexHistorial: [], horario: {}, asistenciasData: null, asistenciasList: [] } },
  computed: {
    detailTitle() { const t={promedio:'Promedio General',calificaciones:'Calificaciones',horario:'Horario',asistencia:'Asistencia',kardex:'Historial Académico',materias:'Mis Materias'}; return t[this.activeDetail]||'' },
    horarioCount() { let n=0; Object.values(this.horario).forEach(b=>n+=b.length); return n },
    materiasCount() { return this.materiasList.length },
    materiasList() {
      const map = {}
      const dias = { lunes:'Lunes', martes:'Martes', miercoles:'Miércoles', jueves:'Jueves', viernes:'Viernes' }
      Object.entries(this.horario).forEach(([dia, bloques]) => {
        bloques.forEach(b => {
          const key = b.materia || 'Sin nombre'
          if (!map[key]) map[key] = { nombre: key, docente: b.docente, horarios: [] }
          if (b.docente && !map[key].docente) map[key].docente = b.docente
          map[key].horarios.push({ id: b.id, dia: dias[dia] || dia, hora_inicio: b.hora_inicio, hora_fin: b.hora_fin })
        })
      })
      return Object.values(map)
    },
    diasSemana() { return [{val:'lunes',label:'Lunes'},{val:'martes',label:'Martes'},{val:'miercoles',label:'Miércoles'},{val:'jueves',label:'Jueves'},{val:'viernes',label:'Viernes'}] },
    calendarSlots() {
      const allHours = []
      Object.values(this.horario).flat().forEach(h => {
        const [sh] = (h.hora_inicio || '7:00').split(':').map(Number)
        const [eh] = (h.hora_fin || '7:00').split(':').map(Number)
        if (sh > 0) allHours.push(sh)
        if (eh > 0) allHours.push(eh)
      })
      let min = allHours.length ? Math.max(7, Math.min(...allHours) - 1) : 7
      let max = allHours.length ? Math.min(20, Math.max(...allHours) + 1) : 14
      if (min >= max) max = min + 6
      const s = []
      for (let h = min; h <= max; h++) {
        s.push({ key: h + ':00', label: this.pad(h) + ':00', start: h * 60 })
        s.push({ key: h + ':30', label: '', start: h * 60 + 30 })
      }
      return s
    },
  },
  async mounted() {
    this.loading=true
    try {
      const [dash,calif,kardexR,horR,asistR]=await Promise.all([PortalAlumnoService.getDashboard(),PortalAlumnoService.getCalificaciones(),PortalAlumnoService.getKardex(),PortalAlumnoService.getHorario(),PortalAlumnoService.getAsistencias()])
      const dd=dash.data.data||dash.data; this.alumno=dd.alumno;this.resumen=dd.resumen;this.avisos=dd.avisos||[]
      const cd=calif.data.data||calif.data; this.calificacionesGrouped=cd.calificaciones||{}
      const kd=kardexR.data.data||kardexR.data; this.kardexHistorial=kd.historial||[]
      const hd=horR.data.data||horR.data; this.horario=hd.horario||{}
      const ad=asistR.data.data||asistR.data; this.asistenciasData=ad;this.asistenciasList=ad.asistencias?.data||ad.asistencias||[]
      // Load latest news
      try { const n = await api.get('/configuracion/noticias', { params: { per_page: 1 } }); const nd = n.data.data || n.data; this.ultimaNoticia = (Array.isArray(nd) ? nd[0] : nd?.data?.[0]) || null } catch (e) {}
    } catch(e) { console.error(e) }
    this.loading=false
  },
  methods: {
    openDetail(key) { this.activeDetail = key; this.activeDay = 0 },
    togglePeriodo(p) { this.calOpen[p] = !this.calOpen[p] },
    semestreLabel(p) {
      const parts = String(p).split('-')
      const sem = parts[1] || parts[0] || '?'
      return sem + '° Semestre'
    },
    linkify(text) {
      if (!text) return ''
      const escaped = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      return escaped.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener" style="color:var(--color-primary)">$1</a>')
    },
    pad(n) { return n < 10 ? '0' + n : '' + n },
    colorFor(name) { const c = ['#C62828','#1565C0','#2E7D32','#F57F17','#6A1B9A','#00838F','#BF360C','#4E342E']; let h = 0; if (name) for (let i = 0; i < name.length; i++) h = name.charCodeAt(i) + ((h << 5) - h); return c[Math.abs(h || 0) % c.length] },
    getBlocksCompact(dia, slot) {
      const bl = this.horario[dia] || []
      return bl.filter(h => {
        const [sh, sm] = (h.hora_inicio || '0:00').split(':').map(Number)
        const [eh, em] = (h.hora_fin || '0:00').split(':').map(Number)
        const bs = slot.start; const be = bs + 30
        const cs = sh * 60 + (sm || 0); const ce = eh * 60 + (em || 0)
        return cs < be && ce > bs
      }).map(h => ({ ...h, _color: this.colorFor(h.materia) }))
    },
  },
}
</script>

<style scoped>
.portal-home { max-width: 480px; margin: 0 auto; }
.greeting { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
.greeting-avatar { width: 48px; height: 48px; border-radius: 14px; overflow: hidden; flex-shrink: 0; }
.greeting-avatar img { width: 100%; height: 100%; object-fit: cover; }
.greeting-avatar.placeholder { background: var(--color-primary); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.25rem; }
.greeting-text h1 { margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--color-text); }
.greeting-text p { margin: .125rem 0 0; font-size: .75rem; color: var(--color-text-muted); }

/* Stats grid */
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; margin-bottom: 1rem; }
.stat-card { display: flex; flex-direction: column; align-items: center; gap: .25rem; padding: .875rem .5rem; background: var(--glass-bg, rgba(255,255,255,.03)); border: 1.5px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 14px; cursor: pointer; font-family: inherit; text-align: center; color: var(--color-text-muted); transition: all .15s; }
.stat-card:active { border-color: var(--color-primary); transform: scale(.97); }
.stat-icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .75rem; }
.stat-value { font-size: 1.25rem; font-weight: 800; color: var(--color-text); line-height: 1.1; }
.stat-label { font-size: .625rem; text-transform: uppercase; letter-spacing: .04em; }

/* Modal overlay */
.detail-overlay { position: fixed; inset: 0; z-index: 200; background: rgba(0,0,0,.6); backdrop-filter: blur(4px); display: flex; align-items: flex-end; justify-content: center; animation: fadeIn .15s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.detail-modal { width: 100%; max-width: 480px; max-height: 85vh; background: var(--color-bg, #0A0E14); border-radius: 20px 20px 0 0; display: flex; flex-direction: column; animation: slideUp .2s ease; }
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
.detail-modal-header { display: flex; align-items: center; gap: .75rem; padding: 1rem; border-bottom: 1px solid var(--glass-border); }
.detail-close { width: 32px; height: 32px; border: none; background: var(--color-bg-secondary); color: var(--color-text); border-radius: 8px; cursor: pointer; font-size: .875rem; display: flex; align-items: center; justify-content: center; }
.detail-modal-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--color-text); }
.detail-modal-body { padding: 1rem; overflow-y: auto; flex: 1; }

/* Detail content */
.detail-big { font-size: 2.5rem; font-weight: 800; color: var(--color-primary); text-align: center; }
.detail-sub { text-align: center; font-size: .75rem; color: var(--color-text-muted); margin-bottom: .75rem; }
.semester-list { display: flex; flex-direction: column; gap: .375rem; }
.semester-row { display: flex; align-items: center; gap: .5rem; padding: .5rem .625rem; background: var(--color-bg-secondary); border-radius: 8px; }
.semester-name { flex: 1; font-size: .75rem; color: var(--color-text); }
.semester-prom { font-size: .875rem; font-weight: 700; color: var(--color-primary); }
.semester-detail { font-size: .625rem; color: var(--color-text-muted); }

/* Calificaciones */
.cal-periodo { margin-bottom: .375rem; }
.cal-periodo-bar { display: flex; align-items: center; justify-content: space-between; padding: .5rem .625rem; background: var(--color-bg-secondary); border-radius: 10px; cursor: pointer; user-select: none; transition: background .15s; }
.cal-periodo-bar:active { background: var(--color-bg-tertiary, rgba(255,255,255,0.06)); }
.cal-periodo-left { display: flex; align-items: center; gap: .5rem; }
.cal-chevron { font-size: .5625rem; color: var(--color-text-muted); transition: transform .2s; }
.cal-chevron.open { transform: rotate(90deg); }
.cal-periodo-title { font-size: .75rem; font-weight: 700; color: var(--color-text); }
.cal-periodo-count { font-size: .625rem; color: var(--color-text-muted); background: var(--color-bg); padding: .0625rem .375rem; border-radius: 8px; }
.cal-periodo-body { padding: .375rem 0 .25rem .5rem; border-left: 2px solid var(--color-border-light); margin-left: .75rem; }
.cal-item { padding: .375rem .5rem; background: var(--color-bg-secondary); border-radius: 6px; margin-bottom: .25rem; }
.cal-item-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .125rem; }
.cal-item-name { font-size: .75rem; font-weight: 500; color: var(--color-text); }
.cal-item-badge { font-size: .6875rem; font-weight: 700; } .cal-item-badge.ok { color: #10B981; } .cal-item-badge.fail { color: #EF4444; }
.cal-item-parciales { display: flex; gap: .375rem; font-size: .625rem; color: var(--color-text-muted); flex-wrap: wrap; }
.cal-est { font-weight: 600; text-transform: uppercase; font-size: .5625rem; }
.cal-est.aprobada { color: #10B981; } .cal-est.reprobada { color: #EF4444; }

/* Calendar Schedule */
.cal-wrap { position: relative; }
.cal-wrap::after { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 30px; background: linear-gradient(to right, transparent, var(--color-bg)); pointer-events: none; z-index: 2; }
@media (min-width: 769px) { .cal-wrap::after { display: none; } }
.cal-schedule { overflow: auto; -webkit-overflow-scrolling: touch; border: 1px solid var(--color-border-light, rgba(255,255,255,0.06)); border-radius: 12px; max-height: 60vh; }
.cal-schedule::-webkit-scrollbar { width: 5px; height: 5px; }
.cal-schedule::-webkit-scrollbar-track { background: var(--color-bg-secondary); border-radius: 0 0 12px 0; }
.cal-schedule::-webkit-scrollbar-thumb { background: var(--color-border); border-radius: 5px; }
.cal-schedule-head { display: grid; grid-template-columns: 44px repeat(5, minmax(90px, 1fr)); min-width: 500px; background: var(--color-bg-secondary); }
.cal-sh-time { padding: .375rem .25rem; }
.cal-sh-day { padding: .375rem .125rem; text-align: center; font-size: .5625rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-muted); letter-spacing: .03em; }
.cal-schedule-row { display: grid; grid-template-columns: 44px repeat(5, minmax(90px, 1fr)); min-width: 500px; border-top: 1px solid var(--color-border-light, rgba(255,255,255,0.04)); }
.cal-sr-time { padding: .0625rem .25rem 0; font-size: .5rem; color: var(--color-text-muted); text-align: right; background: var(--color-bg); }
.cal-sr-cell { min-height: 30px; padding: 0; background: var(--color-bg); border-left: 1px solid var(--color-border-light, rgba(255,255,255,0.04)); overflow: hidden; display: flex; flex-direction: column; }
.cal-sr-block { flex: 1; padding: .125rem .25rem; font-size: .5rem; color: #fff; line-height: 1.3; display: flex; flex-direction: column; justify-content: center; }
.cal-sr-subject { display: block; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cal-sr-teacher { display: none; }
@media (min-width: 481px) { .cal-sr-block { font-size: .5625rem; } .cal-sr-subject { font-size: .5625rem; } }

/* Mobile calendar list - removed, grid shows everywhere */

/* Asistencia */
.att-big { font-size: 2.5rem; font-weight: 800; color: var(--color-primary); text-align: center; margin-bottom: .5rem; }
.att-stats-row { display: flex; gap: .5rem; margin-bottom: .5rem; }
.att-stat { flex: 1; text-align: center; padding: .5rem; background: var(--color-bg-secondary); border-radius: 8px; }
.att-stat.danger .att-num { color: #EF4444; }
.att-num { display: block; font-size: 1.125rem; font-weight: 700; color: var(--color-text); }
.att-lbl { font-size: .5625rem; color: var(--color-text-muted); text-transform: uppercase; }
.att-list { display: flex; flex-direction: column; gap: .1875rem; max-height: 250px; overflow-y: auto; }
.att-row { display: flex; justify-content: space-between; align-items: center; padding: .3125rem .375rem; font-size: .6875rem; color: var(--color-text); background: var(--color-bg-secondary); border-radius: 6px; }
.att-mat { color: var(--color-text-muted); }
.att-badge { padding: .0625rem .3125rem; border-radius: 3px; font-size: .5625rem; font-weight: 600; }
.att-presente { background: rgba(16,185,129,.15); color: #10B981; }
.att-falta { background: rgba(239,68,68,.15); color: #EF4444; }
.att-justificada { background: rgba(245,158,11,.15); color: #F59E0B; }

/* Kardex */
.kardex-p { margin-bottom: .5rem; }
.kardex-p h6 { font-size: .6875rem; font-weight: 700; color: var(--color-text); margin-bottom: .25rem; }
.kardex-mini { width: 100%; font-size: .625rem; border-collapse: collapse; }
.kardex-mini th, .kardex-mini td { padding: .1875rem .25rem; border-bottom: 1px solid var(--color-border-light); color: var(--color-text); }
.kardex-mini th { color: var(--color-text-muted); font-weight: 600; font-size: .5625rem; }

/* Materias */
.materia-card { padding: .625rem; background: var(--color-bg-secondary); border-radius: 10px; margin-bottom: .375rem; }
.materia-name { font-size: .8125rem; font-weight: 600; color: var(--color-text); margin-bottom: .375rem; }
.materia-info { display: flex; flex-direction: column; gap: .125rem; font-size: .6875rem; color: var(--color-text-muted); }
.materia-docente { font-weight: 500; color: var(--color-text); }

/* News */
.news-section { margin-bottom: 1rem; }
.news-card { background: var(--glass-bg, rgba(255,255,255,.03)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 14px; overflow: hidden; cursor: pointer; }
.news-img { width: 100%; max-height: 220px; object-fit: contain; background: var(--color-bg-secondary); cursor: pointer; }

/* Photo lightbox */
.photo-overlay { position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.9); display: flex; align-items: center; justify-content: center; animation: fadeIn .15s; }
.photo-full { max-width: 95vw; max-height: 90vh; object-fit: contain; }
.photo-close-btn { position: absolute; top: 1rem; right: 1rem; width: 40px; height: 40px; border: none; background: rgba(255,255,255,.1); color: #fff; border-radius: 50%; font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
@keyframes fadeIn { from { opacity: 0; } }
.news-body { padding: .75rem 1rem; }
.news-title { font-size: .875rem; font-weight: 700; color: var(--color-text); margin-bottom: .25rem; }
.news-text { font-size: .75rem; color: var(--color-text-muted); margin: 0; overflow: hidden; word-break: break-word; }
.news-text a { color: var(--color-primary); text-decoration: underline; }
.news-date { font-size: .625rem; color: var(--color-text-muted); display: block; margin-top: .375rem; }

/* Avisos */
.avisos-section { margin-top: .5rem; }
.section-sm { font-size: .6875rem; font-weight: 700; color: var(--color-text-muted); margin-bottom: .375rem; text-transform: uppercase; letter-spacing: .04em; }
.aviso-item { padding: .5rem .625rem; border-radius: 8px; background: var(--color-bg-secondary); margin-bottom: .25rem; font-size: .6875rem; color: var(--color-text); }
.aviso-item strong { font-size: .75rem; }
.aviso-item p { margin: .0625rem 0 0; color: var(--color-text-muted); font-size: .625rem; }
.text-muted { color: var(--color-text-muted); }
.fw-bold { font-weight: 700; }
.mt-2 { margin-top: .5rem; }
</style>
