<template>
  <div class="detail-page">
    <div class="page-header">
      <button class="btn btn-outline-secondary btn-icon-lg" @click="$router.push('/docentes')" title="Volver"><i class="fas fa-arrow-left"></i></button>
      <button class="btn btn-primary btn-icon-lg ms-auto" @click="openEdit" title="Editar"><i class="fas fa-pen-to-square"></i></button>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

    <template v-else-if="docente.id">
      <div class="credential-card">
        <div class="cred-left">
          <div class="cred-photo-wrapper" @click="showPhoto=true">
            <img v-if="docente.usuario?.fotografia" :src="docente.usuario.fotografia" class="cred-photo" />
            <div v-else class="cred-photo cred-photo-empty"><i class="fas fa-chalkboard-user"></i></div>
            <div class="cred-photo-overlay"><i class="fas fa-expand"></i></div>
          </div>
          <span :class="['badge',docente.estatus==='activo'?'badge-success':'badge-secondary']">{{ docente.estatus }}</span>
        </div>
        <div class="cred-right">
          <div class="cred-name">{{ docente.usuario?.nombre_completo||'Sin nombre' }}</div>
          <div class="cred-grid">
            <div class="cred-field"><div class="cred-label">N° Control</div><div class="cred-value">{{ docente.numero_empleado }}</div></div>
            <div class="cred-field"><div class="cred-label">Grado</div><div class="cred-value">{{ docente.grado_academico||'-' }}</div></div>
            <div class="cred-field"><div class="cred-label">Especialidad</div><div class="cred-value">{{ (docente.especialidades||[]).map(e=>e.nombre).join(', ')||docente.especialidad_nombre||docente.especialidad||'-' }}</div></div>
            <div class="cred-field"><div class="cred-label">RFC</div><div class="cred-value">{{ docente.rfc||'-' }}</div></div>
            <div class="cred-field"><div class="cred-label">CURP</div><div class="cred-value">{{ docente.curp||'-' }}</div></div>
            <div class="cred-field"><div class="cred-label">Ingreso</div><div class="cred-value">{{ docente.fecha_ingreso||'-' }}</div></div>
          </div>
          <div class="cred-footer"><span class="cred-matricula">{{ docente.numero_empleado }}</span><span class="cred-date">Docente CETIS</span></div>
        </div>
      </div>

      <div class="section-toggles">
        <button class="section-btn" @click="openSection('datos')"><i class="fas fa-address-card"></i> Datos</button>
        <button class="section-btn" @click="openSection('materias')"><i class="fas fa-book-open"></i> Materias</button>
        <button class="section-btn" @click="openSection('horario')"><i class="fas fa-clock"></i> Horario</button>
        <button class="section-btn" @click="openSection('grupos')"><i class="fas fa-users"></i> Grupos</button>
      </div>

      <div class="modal fade" ref="sectionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header"><div class="modal-icon icon-create"><i :class="modalIcon"></i></div><div><h5 class="modal-title">{{ modalTitle }}</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <div v-if="currentSection==='datos'" class="info-grid">
                <div class="info-item"><span class="info-label">Email</span><span class="info-value">{{ docente.usuario?.email||'-' }}</span></div>
                <div class="info-item"><span class="info-label">CURP</span><span class="info-value">{{ docente.curp||'-' }}</span></div>
                <div class="info-item"><span class="info-label">RFC</span><span class="info-value">{{ docente.rfc||'-' }}</span></div>
                <div class="info-item"><span class="info-label">Especialidades</span><span class="info-value">{{ (docente.especialidades||[]).map(e=>e.nombre).join(', ')||'-' }}</span></div>
                <div class="info-item"><span class="info-label">Ingreso</span><span class="info-value">{{ docente.fecha_ingreso||'-' }}</span></div>
              </div>
              <div v-if="currentSection==='materias'">
                <div v-if="!todasLasMaterias.length" class="text-muted text-center py-3">Sin materias</div>
                <div v-else class="tags-wrap"><span v-for="m in todasLasMaterias" :key="m.id" class="materia-tag">{{ m.nombre }}</span></div>
              </div>
              <div v-if="currentSection==='horario'">
                <div v-if="!docente.horarios||!docente.horarios.length" class="text-muted text-center py-3">Sin horario</div>
                <div v-else class="mini-cal">
                  <div class="mc-header" v-for="d in ['Lunes','Martes','Miércoles','Jueves','Viernes']" :key="d">{{ d.substring(0,3) }}</div>
                  <div v-for="d in ['lunes','martes','miercoles','jueves','viernes']" :key="d" class="mc-col">
                    <div v-for="h in horariosPorDia(d)" :key="h.id" class="mc-item" :style="{background:slotColor(h.materia_id||0)}">
                      <div class="mc-time">{{ h.hora_inicio?.substring(0,5) }}</div>
                      <div class="mc-subject">{{ h.materia||'-' }}</div>
                      <div class="mc-teacher">{{ h.grupo||'-' }}</div>
                    </div>
                    <div v-if="!horariosPorDia(d).length" class="mc-empty">—</div>
                  </div>
                </div>
              </div>
              <div v-if="currentSection==='grupos'">
                <div v-if="!docente.grupos||!docente.grupos.length" class="text-muted text-center py-3">Sin grupos</div>
                <div v-else v-for="g in docente.grupos" :key="g.id" class="group-block">
                  <div class="group-header"><strong>{{ g.nombre }}</strong> <span class="badge badge-neutral">{{ g.semestre }}°</span> <span class="small text-muted">{{ g.alumnos_count||g.alumnos?.length||0 }} alumnos · {{ g.turno||'-' }}</span></div>
                  <div v-if="g.alumnos&&g.alumnos.length" class="alumno-table">
                    <div class="at-row at-head"><span>Alumno</span><span>Matrícula</span><span>Promedio</span><span></span></div>
                    <template v-for="a in g.alumnos" :key="a.id">
                      <div class="at-row" @click="a._expand=!a._expand;if(a._expand)loadAlumnoGrades(a)" style="cursor:pointer">
                        <span class="fw-semibold">{{ a.nombre_completo||'-' }}</span>
                        <span class="text-muted">{{ a.matricula }}</span>
                        <span :class="['badge',alumnoPromedioColor(a)]">{{ alumnoPromedio(a) }}</span>
                        <span><i :class="a._expand?'fas fa-chevron-up':'fas fa-chevron-down'" class="text-muted"></i></span>
                      </div>
                      <div v-if="a._expand" class="at-expand">
                        <div v-if="a._loading" class="text-center py-2"><div class="loading-pulse-sm"></div></div>
                        <div v-else-if="a._grades&&a._grades.length" class="grades-table">
                          <div class="gt-row gt-head"><span>Materia</span><span>P1</span><span>A1</span><span>P2</span><span>A2</span><span>P3</span><span>A3</span><span>Prom</span></div>
                          <div v-for="cal in a._grades" :key="cal.id" class="gt-row">
                            <span>{{ cal.materia?.nombre||'-' }}</span>
                            <span>{{ cal.parcial1||'-' }}</span><span class="text-muted small">{{ cal.asistencia_p1||'-' }}</span>
                            <span>{{ cal.parcial2||'-' }}</span><span class="text-muted small">{{ cal.asistencia_p2||'-' }}</span>
                            <span>{{ cal.parcial3||'-' }}</span><span class="text-muted small">{{ cal.asistencia_p3||'-' }}</span>
                            <span :class="['badge',cal.promedio_final>=7?'badge-success':cal.promedio_final?'badge-danger':'badge-neutral']">{{ cal.promedio_final||'-' }}</span>
                          </div>
                        </div>
                        <div v-else class="text-muted small p-2">Sin calificaciones</div>
                      </div>
                    </template>
                  </div>
                  <div v-else class="text-muted small p-2">Sin alumnos</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="photo-modal" v-if="showPhoto" @click="showPhoto=false">
        <div class="photo-modal-content"><img v-if="docente.usuario?.fotografia" :src="docente.usuario.fotografia" /><div v-else class="photo-modal-empty"><i class="fas fa-chalkboard-user fa-4x"></i><p>Sin foto</p></div><button class="photo-modal-close"><i class="fas fa-times"></i></button></div>
      </div>
    </template>

    <div v-else class="text-center py-5"><i class="fas fa-circle-exclamation fa-3x mb-3" style="color:var(--color-text-muted);opacity:0.5"></i><h5 class="text-muted">Docente no encontrado</h5></div>
  </div>
</template>

<script>
import api from '@/api/axios'
import { Modal } from 'bootstrap'
export default {
  name: 'DocenteDetail',
  data(){return{docente:{},loading:false,showPhoto:false,currentSection:'',sectionModal:null}},
  computed:{
    modalTitle(){const t={datos:'Datos Personales',materias:'Materias',horario:'Horario',grupos:'Grupos'};return t[this.currentSection]||''},
    modalIcon(){const i={datos:'fas fa-address-card',materias:'fas fa-book-open',horario:'fas fa-clock',grupos:'fas fa-users'};return i[this.currentSection]||''},
    todasLasMaterias(){
      const map = new Map()
      const pivot = this.docente.materias || []
      const horario = (this.docente.horarios || []).filter(h => h.materia).map(h => ({ id: h.materia, nombre: h.materia }))
      for (const m of [...pivot, ...horario]) {
        if (!map.has(m.nombre)) map.set(m.nombre, m)
      }
      return Array.from(map.values())
    },
  },
  mounted(){this.fetchData()},
  methods:{
    initials(n){if(!n)return'?';const p=n.trim().split(' ');return(p[0][0]+(p.length>1?p[p.length-1][0]:'')).toUpperCase()},
    avatarColor(n){const c=['#A91E22','#B99A53','#1E40AF','#059669','#7C3AED','#D97706'];let h=0;for(let i=0;i<(n||'').length;i++)h=n.charCodeAt(i)+((h<<5)-h);return c[Math.abs(h)%c.length]},
    openSection(s){this.currentSection=s;if(!this.sectionModal)this.sectionModal=new Modal(this.$refs.sectionModal);if(s==='grupos')this.loadCalificaciones();this.sectionModal.show()},
    async loadCalificaciones(){for(const g of this.docente.grupos||[]){for(const a of g.alumnos||[]){try{const{data}=await api.get('/calificaciones',{params:{alumno_id:a.id,per_page:20}});a._calificaciones=data.data||data||[]}catch(e){a._calificaciones=[]}}}},
    async loadAlumnoGrades(a){if(a._grades)return;a._loading=true;try{const{data}=await api.get('/calificaciones',{params:{alumno_id:a.id,per_page:20}});a._grades=data.data||data||[]}catch(e){a._grades=[]};a._loading=false},
    alumnoPromedio(a){const g=a._grades||a._calificaciones||[];if(!g.length)return'-';const p=g.filter(x=>x.promedio_final).map(x=>Number(x.promedio_final));if(!p.length)return'-';return Math.round(p.reduce((s,x)=>s+x,0)/p.length*10)/10},
    alumnoPromedioColor(a){const v=this.alumnoPromedio(a);if(v==='-')return'badge-neutral';return v>=7?'badge-success':'badge-danger'},
    horariosPorDia(dia){return(this.docente.horarios||[]).filter(h=>String(h.dia||'').toLowerCase()===dia)},
    slotColor(id){const c=['#A91E22','#1E40AF','#059669','#D97706','#7C3AED','#0D9488'];return c[(id||0)%c.length]},
    goToAlumno(id){if(this.sectionModal)this.sectionModal.hide();this.$router.push('/alumnos/'+id)},
    gradeDotColor(v){if(!v)return'g-none';return v>=7?'g-good':'g-bad'},
    openEdit(){this.$router.push('/docentes')},
    async fetchData(){this.loading=true;try{const{data}=await api.get('/docentes/'+this.$route.params.id);this.docente=data.data||data}catch(e){this.docente={}};this.loading=false},
  },
}
</script>

<style scoped>
.detail-page{max-width:1100px}
.page-header{display:flex;align-items:center;margin-bottom:1.5rem;gap:.5rem}
.btn-icon-lg{width:42px;height:42px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-md);font-size:var(--font-size-lg)}
.credential-card{display:flex;gap:1.5rem;background:rgba(18,23,29,.55);backdrop-filter:blur(4.4px);-webkit-backdrop-filter:blur(4.4px);border:1px solid rgba(18,23,29,.27);border-radius:16px;padding:1.75rem;margin-bottom:1.5rem;box-shadow:0 4px 30px rgba(0,0,0,.1);flex-wrap:wrap}
.cred-left{display:flex;flex-direction:column;align-items:center;gap:.75rem}
.cred-photo-wrapper{position:relative;cursor:pointer;border-radius:18px;overflow:hidden}
.cred-photo{width:130px;height:160px;border-radius:18px;object-fit:cover;display:block;box-shadow:var(--shadow-md)}
.cred-photo-empty{background:var(--color-bg-secondary);display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--color-text-muted)}
.cred-photo-overlay{position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;color:#fff;font-size:1.5rem}
.cred-photo-wrapper:hover .cred-photo-overlay{opacity:1}
.cred-right{flex:1;min-width:250px;display:flex;flex-direction:column;justify-content:center}
.cred-name{font-size:1.5rem;font-weight:800;margin-bottom:1rem}
.cred-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.625rem;margin-bottom:.875rem}
.cred-field{background:var(--color-bg-secondary);border-radius:12px;padding:.75rem 1rem}
.cred-label{font-size:.625rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-muted);font-weight:600;margin-bottom:2px}
.cred-value{font-size:.875rem;font-weight:600;word-break:break-word}
.cred-footer{display:flex;justify-content:space-between;align-items:center;padding-top:.75rem;border-top:1px solid var(--color-border-light)}
.cred-matricula{font-family:var(--font-family-mono);font-weight:700;color:var(--color-primary);font-size:.875rem;background:var(--color-primary-light);padding:.2rem .625rem;border-radius:6px}
.cred-date{font-size:.75rem;color:var(--color-text-muted)}

.section-toggles{display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap}
.section-btn{display:flex;align-items:center;gap:.625rem;flex:1;min-width:130px;padding:1rem 1.25rem;border:1px solid rgba(255,255,255,.12);border-radius:12px;background:var(--color-bg-tertiary);color:var(--color-text);font-size:.9375rem;font-weight:600;cursor:pointer;transition:all .2s}
.section-btn:hover{border-color:var(--color-primary);color:var(--color-primary);background:var(--color-primary-light);transform:translateY(-1px)}
.section-btn i{font-size:1.125rem;color:var(--color-primary)}

.modal-content{border:none;border-radius:20px;overflow:hidden}
.modal-header{padding:1.5rem 1.75rem 1rem;border-bottom:none;display:flex;align-items:flex-start;gap:.875rem}
.modal-icon{width:42px;height:42px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff}
.icon-create{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-hover))}
.modal-title{font-size:1.125rem;font-weight:700;margin:0}
.modal-body{padding:0 1.75rem 1.5rem}
.info-grid{display:flex;flex-direction:column;gap:.75rem}
.info-item{display:flex;justify-content:space-between;padding-bottom:.5rem;border-bottom:1px solid var(--color-border-light)}
.info-item:last-child{border-bottom:none;padding-bottom:0}
.info-label{font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.04em;font-weight:500}
.info-value{font-size:.875rem;font-weight:500;text-align:right}
.tags-wrap{display:flex;flex-wrap:wrap;gap:.5rem}
.materia-tag{padding:.3rem .75rem;border-radius:20px;background:var(--color-primary-light);color:var(--color-primary);font-size:.8125rem;font-weight:500}
.sch-row{padding:.5rem;background:var(--color-bg-secondary);border-radius:8px;font-size:.875rem;margin-bottom:.375rem}
.group-block{margin-bottom:.75rem;border:1px solid var(--color-border-light);border-radius:12px;overflow:hidden}
.group-header{padding:.625rem .875rem;background:var(--color-bg-tertiary);font-size:.875rem}
.alumno-table{font-size:.8125rem}
.at-row{display:grid;grid-template-columns:1fr 100px 80px;gap:.5rem;padding:.5rem .875rem;border-bottom:1px solid var(--color-border-light);align-items:center}
.at-row:last-child{border-bottom:none}
.at-head{font-size:.6875rem;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);font-weight:600}
.at-row:hover:not(.at-head){background:var(--color-bg-tertiary)}
.at-expand{padding:0 0 .5rem .875rem;border-bottom:1px solid var(--color-border-light)}
.grades-table{font-size:.75rem;margin-top:.25rem}
.gt-row{display:grid;grid-template-columns:1fr 35px 30px 35px 30px 35px 30px 50px;gap:.25rem;padding:.25rem .5rem;align-items:center}
.gt-head{font-size:.625rem;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);font-weight:600;border-bottom:1px solid var(--color-border-light)}
.gt-row:not(.gt-head):hover{background:var(--color-bg-secondary)}
.loading-pulse-sm{width:20px;height:20px;border-radius:50%;border:2px solid var(--color-border);border-top-color:var(--color-primary);animation:spin .7s linear infinite;margin:0 auto}

.mini-cal{display:grid;grid-template-columns:repeat(5,1fr);gap:1px;background:var(--color-border-light);border-radius:12px;overflow:hidden}
.mc-header{padding:.5rem .25rem;text-align:center;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-bg-tertiary)}
.mc-col{background:var(--color-bg);min-height:60px;padding:.25rem;display:flex;flex-direction:column;gap:2px}
.mc-item{border-radius:6px;padding:.25rem .375rem;color:#fff;font-size:.625rem}
.mc-time{font-weight:700;font-size:.5625rem;opacity:.9}
.mc-subject{font-weight:600;line-height:1.2}
.mc-teacher{font-size:.5rem;opacity:.75}
.mc-empty{text-align:center;color:var(--color-text-muted);font-size:.75rem;padding:.5rem 0}
.photo-modal{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:pointer}
.photo-modal-content{position:relative;max-width:90vw;max-height:90vh}
.photo-modal-content img{max-width:90vw;max-height:90vh;border-radius:20px;object-fit:contain}
.photo-modal-empty{text-align:center;color:#fff;padding:4rem}
.photo-modal-close{position:absolute;top:-40px;right:0;background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer}
</style>
