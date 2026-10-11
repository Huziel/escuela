<template>
  <div class="detail-page">
    <div class="page-header">
      <button class="btn btn-outline-secondary btn-icon-lg" @click="$router.push('/alumnos')" title="Volver">
        <i class="fas fa-arrow-left"></i>
      </button>
      <button class="btn btn-primary btn-icon-lg ms-auto" @click="openEditModal" title="Editar alumno">
        <i class="fas fa-pen-to-square"></i>
      </button>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

    <template v-else-if="alumno.id">
      <div class="credential-card">
        <div class="cred-left">
          <div class="cred-photo-wrapper" @click="showPhoto = true">
            <img v-if="alumno.usuario?.fotografia" :src="alumno.usuario.fotografia" class="cred-photo" />
            <div v-else class="cred-photo cred-photo-empty"><i class="fas fa-user-graduate"></i></div>
            <div class="cred-photo-overlay"><i class="fas fa-expand"></i></div>
          </div>
          <div class="cred-status"><span :class="['badge', statusBadge(alumno.estatus)]">{{ alumno.estatus }}</span></div>
        </div>
        <div class="cred-right">
          <div class="cred-name">{{ alumno.usuario?.nombre_completo || 'Sin nombre' }}</div>
          <div class="cred-grid">
            <div class="cred-field"><div class="cred-label">Grado</div><div class="cred-value">{{ alumno.semestre }}°</div></div>
            <div class="cred-field"><div class="cred-label">Grupo</div><div class="cred-value">{{ alumno.grupo?.nombre || '-' }}</div></div>
            <div class="cred-field"><div class="cred-label">Especialidad</div><div class="cred-value">{{ alumno.especialidad?.nombre || '-' }}</div></div>
            <div class="cred-field"><div class="cred-label">Turno</div><div class="cred-value">{{ alumno.turno || alumno.grupo?.turno || '-' }}</div></div>
            <div class="cred-field"><div class="cred-label">N° Control</div><div class="cred-value">{{ alumno.matricula || '-' }}</div></div>
            <div class="cred-field"><div class="cred-label">Ciclo Escolar</div><div class="cred-value">{{ alumno.ciclo_escolar || '-' }}</div></div>
            <div class="cred-field"><div class="cred-label">Generación</div><div class="cred-value">{{ alumno.generacion?.nombre || '-' }}</div></div>
          </div>
          <div class="cred-footer">
            <span class="cred-date">Ingreso: {{ alumno.fecha_ingreso || '-' }}</span>
          </div>
        </div>
      </div>

      <div class="section-toggles">
        <button class="section-btn" @click="openSection('datos')"><i class="fas fa-address-card"></i> Datos Personales</button>
        <button class="section-btn" @click="openSection('asistencia')"><i class="fas fa-calendar-check"></i> Asistencias</button>
        <button class="section-btn" @click="openSection('horario')"><i class="fas fa-clock"></i> Horario</button>
        <button class="section-btn" @click="openSection('calificaciones')"><i class="fas fa-star"></i> Calificaciones</button>
      </div>

      <div class="modal fade" ref="sectionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-icon icon-create"><i :class="modalIcon"></i></div>
              <div><h5 class="modal-title">{{ modalTitle }}</h5></div>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div v-if="currentSection==='datos'" class="info-grid">
                <div class="info-item"><span class="info-label">Email</span><span class="info-value">{{ alumno.usuario?.email || '-' }}</span></div>
                <div class="info-item"><span class="info-label">CURP</span><span class="info-value">{{ alumno.curp || '-' }}</span></div>
                <div class="info-item"><span class="info-label">Teléfono</span><span class="info-value">{{ alumno.usuario?.telefono || '-' }}</span></div>
                <div class="info-item"><span class="info-label">Dirección</span><span class="info-value">{{ alumno.usuario?.direccion || '-' }}</span></div>
              </div>
              <div v-if="currentSection==='asistencia'">
                <div class="att-stats"><div class="att-s"><div class="att-c green">{{ attendCount.presentes }}</div><span>Presentes</span></div><div class="att-s"><div class="att-c red">{{ attendCount.faltas }}</div><span>Faltas</span></div><div class="att-s"><div class="att-c amber">{{ attendCount.justificadas }}</div><span>Justificadas</span></div></div>
                <div class="att-bar"><div class="att-f green" :style="{width:attendPercent.presentes+'%'}"></div><div class="att-f red" :style="{width:attendPercent.faltas+'%'}"></div><div class="att-f amber" :style="{width:attendPercent.justificadas+'%'}"></div></div>
              </div>
              <div v-if="currentSection==='horario'">
                <div v-if="!alumno.horarios||!alumno.horarios.length" class="text-muted text-center py-3">Sin horario asignado para este grupo</div>
                <div v-else class="mini-cal">
                  <div class="mc-header" v-for="d in ['Lunes','Martes','Miércoles','Jueves','Viernes']" :key="d">{{ d.substring(0,3) }}</div>
                  <div v-for="d in ['lunes','martes','miercoles','jueves','viernes']" :key="d" class="mc-col">
                    <div v-for="h in horariosPorDia(d)" :key="h.id" class="mc-item" :style="{background:slotColor(h.materia_id||0)}">
                      <div class="mc-time">{{ h.hora_inicio?.substring(0,5) }}</div>
                      <div class="mc-subject">{{ h.materia||'-' }}</div>
                      <div class="mc-teacher" v-if="h.docente">{{ h.docente }}</div>
                    </div>
                    <div v-if="!horariosPorDia(d).length" class="mc-empty">—</div>
                  </div>
                </div>
              </div>
              <div v-if="currentSection==='calificaciones'">
                <div v-if="!alumno.calificaciones||!alumno.calificaciones.length" class="text-muted text-center py-3">Sin calificaciones registradas</div>
                <div v-else>
                  <div v-for="(materias, semestre) in calificacionesPorSemestre" :key="semestre" class="semester-block mb-3">
                    <h6 class="semester-title" @click="calOpen[semestre] = !calOpen[semestre]" style="cursor:pointer">
                      <i class="fas fa-chevron-right cal-chevron" :class="{open: calOpen[semestre]}"></i>
                      <i class="fas fa-layer-group ms-1"></i> {{ semestreLabel(semestre) }}
                      <span class="badge badge-neutral ms-2">{{ materias.length }} materia(s)</span>
                    </h6>
                    <div class="cal-cards" v-show="calOpen[semestre]">
                      <div v-for="cal in materias" :key="cal.id" class="cal-card-item">
                        <div class="cal-card-header">
                          <span class="cal-materia-name">{{ cal.materia?.nombre||'-' }}</span>
                          <span :class="['badge',(cal.promedio_final||0)>=7?'badge-success':cal.promedio_final?'badge-danger':'badge-neutral']">{{ cal.promedio_final||'-' }}</span>
                        </div>
                        <div class="cal-parciales">
                          <div class="cal-parcial">
                            <span class="cal-parcial-label">P1</span>
                            <span class="cal-parcial-value">{{ cal.parcial1||'-' }}</span>
                            <span class="cal-asistencia">Asist: {{ cal.asistencia_p1||'-' }}%</span>
                          </div>
                          <div class="cal-parcial">
                            <span class="cal-parcial-label">P2</span>
                            <span class="cal-parcial-value">{{ cal.parcial2||'-' }}</span>
                            <span class="cal-asistencia">Asist: {{ cal.asistencia_p2||'-' }}%</span>
                          </div>
                          <div class="cal-parcial">
                            <span class="cal-parcial-label">P3</span>
                            <span class="cal-parcial-value">{{ cal.parcial3||'-' }}</span>
                            <span class="cal-asistencia">Asist: {{ cal.asistencia_p3||'-' }}%</span>
                          </div>
                        </div>
                        <div class="cal-card-footer">
                          <span :class="['badge',calEstatusBadge(cal.estatus)]">{{ cal.estatus||'-' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" ref="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header"><div class="modal-icon icon-edit"><i class="fas fa-pen-to-square"></i></div><div><h5 class="modal-title">Editar Alumno</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <div class="photo-upload-row mb-3">
                <div class="photo-pv" v-if="editPhotoPreview||alumno.usuario?.fotografia"><img :src="editPhotoPreview||alumno.usuario?.fotografia" /><button class="photo-rm" @click="editPhoto=null;editPhotoPreview=null"><i class="fas fa-times"></i></button></div>
                <label class="photo-ph" v-else><i class="fas fa-camera fa-2x"></i><span>Subir foto</span><input type="file" accept="image/*" hidden @change="onEditPhotoSelect" /></label>
                <label v-if="editPhotoPreview||alumno.usuario?.fotografia" class="photo-ch"><i class="fas fa-arrow-up-from-bracket"></i> Cambiar<input type="file" accept="image/*" hidden @change="onEditPhotoSelect" /></label>
              </div>
              <div class="fg">
                <div class="ff"><label>Matrícula <span class="req">*</span></label><input v-model="editForm.matricula" class="fi" /></div>
                <div class="ff"><label>CURP</label><input v-model="editForm.curp" class="fi" /></div>
                <div class="ff"><label>Teléfono</label><input v-model="editForm.telefono" class="fi" placeholder="5551234567" /></div>
                <div class="ff fw"><label>Dirección</label><input v-model="editForm.direccion" class="fi" placeholder="Calle, número, colonia..." /></div>
                <div class="ff"><label>Especialidad <span class="req">*</span></label><select v-model="editForm.especialidad_id" class="fi"><option value="">—</option><option v-for="e in especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option></select></div>
                <div class="ff"><label>Semestre <span class="req">*</span></label><select v-model="editForm.semestre" class="fi"><option v-for="s in 6" :key="s" :value="s">{{ s }}°</option></select></div>
                <div class="ff"><label>Grupo</label><select v-model="editForm.grupo_id" class="fi" @change="onEditGrupoChange"><option value="">Sin grupo</option><option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option></select></div>
                <div class="ff"><label>Generación</label><select v-model="editForm.generacion_id" class="fi"><option value="">—</option><option v-for="g in generaciones" :key="g.id" :value="g.id">{{ g.nombre }}</option></select></div>
                <div class="ff"><label>Turno</label><select v-model="editForm.turno" class="fi"><option value="">—</option><option value="matutino">Matutino</option><option value="vespertino">Vespertino</option></select></div>
                <div class="ff"><label>Estatus <span class="req">*</span></label><select v-model="editForm.estatus" class="fi"><option value="activo">Activo</option><option value="inactivo">Inactivo</option><option value="egresado">Egresado</option><option value="baja">Baja</option></select></div>
                <div class="ff"><label>Fecha Ingreso</label><input v-model="editForm.fecha_ingreso" type="date" class="fi" /></div>
                <div class="ff"><label>Ciclo Escolar</label><select v-model="editForm.ciclo_escolar" class="fi"><option value="">—</option><option v-for="c in ciclos" :key="c.id" :value="c.nombre">{{ c.nombre }}</option></select></div>
                <div class="ff fw"><label>Observaciones</label><textarea v-model="editForm.observaciones" class="fi ta" rows="2"></textarea></div>
              </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" @click="saveEdit" :disabled="savingEdit"><i v-if="savingEdit" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar</button></div>
          </div>
        </div>
      </div>

      <div class="photo-modal" v-if="showPhoto" @click="showPhoto = false">
        <div class="photo-modal-content"><img v-if="alumno.usuario?.fotografia" :src="alumno.usuario.fotografia" /><div v-else class="photo-modal-empty"><i class="fas fa-user-graduate fa-4x"></i><p>Sin foto</p></div><button class="photo-modal-close"><i class="fas fa-times"></i></button></div>
      </div>
    </template>

    <div v-else class="text-center py-5"><i class="fas fa-circle-exclamation fa-3x mb-3" style="color:var(--color-text-muted);opacity:0.5"></i><h5 class="text-muted">Alumno no encontrado</h5></div>
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
export default {
  name: 'AlumnoDetail',
  data(){return{alumno:{},loading:false,showPhoto:false,currentSection:'',sectionModal:null,editForm:{},editPhoto:null,editPhotoPreview:null,savingEdit:false,editModal:null,especialidades:[],grupos:[],ciclos:[],generaciones:[],calOpen:{}}},
  computed:{
    modalTitle(){const t={datos:'Datos Personales',asistencia:'Asistencias',horario:'Horario',calificaciones:'Calificaciones'};return t[this.currentSection]||''},
    modalIcon(){const i={datos:'fas fa-address-card',asistencia:'fas fa-calendar-check',horario:'fas fa-clock',calificaciones:'fas fa-star'};return i[this.currentSection]||''},
    editTurno(){const g=this.grupos.find(x=>x.id==this.editForm.grupo_id);return g?.turno||'-'},
    attendCount(){const a=this.alumno.asistencias||[];return{presentes:a.filter(x=>x.estatus==='presente').length,faltas:a.filter(x=>x.estatus==='falta').length,justificadas:a.filter(x=>x.estatus==='justificada').length}},
    attendPercent(){const t=(this.alumno.asistencias||[]).length||1;return{presentes:Math.round(this.attendCount.presentes/t*100),faltas:Math.round(this.attendCount.faltas/t*100),justificadas:Math.round(this.attendCount.justificadas/t*100)}},
    calificacionesPorSemestre(){
      const grouped = {}
      const cals = this.alumno.calificaciones || []
      cals.forEach(cal => {
        const key = cal.periodo || cal.materia?.semestre || this.alumno.semestre || '?'
        if (!grouped[key]) grouped[key] = []
        grouped[key].push(cal)
      })
      return grouped
    },
  },
  mounted(){this.fetchData()},
  methods:{
    openSection(s){this.currentSection=s;if(!this.sectionModal)this.sectionModal=new Modal(this.$refs.sectionModal);this.sectionModal.show()},
    async openEditModal(){if(!this.editModal)this.editModal=new Modal(this.$refs.editModal);this.editForm={matricula:this.alumno.matricula||'',curp:this.alumno.curp||'',telefono:this.alumno.usuario?.telefono||'',direccion:this.alumno.usuario?.direccion||'',especialidad_id:this.alumno.especialidad_id||'',semestre:this.alumno.semestre||'',grupo_id:this.alumno.grupo_id||'',turno:this.alumno.turno||'',estatus:this.alumno.estatus||'activo',fecha_ingreso:this.alumno.fecha_ingreso||'',ciclo_escolar:this.alumno.ciclo_escolar||'',generacion_id:this.alumno.generacion_id||'',observaciones:this.alumno.observaciones||''};this.editPhoto=null;this.editPhotoPreview=null;try{const[e,g,c,gen]=await Promise.all([api.get('/especialidades?per_page=100'),api.get('/grupos?per_page=100'),api.get('/configuracion/ciclos'),api.get('/generaciones/all')]);this.especialidades=e.data.data||e.data||[];this.grupos=g.data.data||g.data||[];this.ciclos=c.data.data||c.data||[];this.generaciones=gen.data||[]}catch(e){};this.editModal.show()},
    onEditPhotoSelect(e){const f=e.target.files[0];if(!f)return;this.editPhoto=f;const r=new FileReader();r.onload=ev=>{this.editPhotoPreview=ev.target.result};r.readAsDataURL(f)},
    onEditGrupoChange(){},
    async saveEdit(){this.savingEdit=true;try{const p={...this.editForm};if(!p.grupo_id)p.grupo_id=null;if(!p.curp)p.curp=null;await api.put('/alumnos/'+this.alumno.id,p);if(this.alumno.usuario?.id){await api.put('/usuarios/'+this.alumno.usuario.id,{telefono:this.editForm.telefono||null,direccion:this.editForm.direccion||null})};if(this.editPhoto&&this.alumno.usuario?.id){const fd=new FormData();fd.append('foto',this.editPhoto);await api.post('/usuarios/'+this.alumno.usuario.id+'/foto',fd,{headers:{'Content-Type':'multipart/form-data'}})};this.editModal.hide();this.fetchData();Swal.fire({toast:true,position:'top-end',icon:'success',title:'Actualizado',showConfirmButton:false,timer:2000,timerProgressBar:true})}catch(e){Swal.fire('Error','No se pudo actualizar.','error')};this.savingEdit=false},
    statusBadge(s){const m={activo:'badge-success',inactivo:'badge-secondary',egresado:'badge-info',baja:'badge-danger',bloqueado:'badge-warning',irregular:'badge-warning'};return m[s]||'badge-secondary'},
    calEstatusBadge(s){const m={aprobada:'badge-success',reprobada:'badge-danger',cursando:'badge-info',pendiente:'badge-warning'};return m[s]||'badge-secondary'},
    semestreLabel(key) { const parts = String(key).split('-'); const sem = parts[1] || parts[0] || '?'; return sem + '° Semestre' },
    horariosPorDia(dia){return(this.alumno.horarios||[]).filter(h=>String(h.dia||'').toLowerCase()===dia)},
    slotColor(id){const c=['#C62828','#E53935','#EF5350','#FF8A80','#B71C1C','#FFCDD2'];return c[(id||0)%c.length]},
    async fetchData(){this.loading=true;try{const{data}=await api.get('/alumnos/'+this.$route.params.id);this.alumno=data.data||data}catch(e){this.alumno={}};this.loading=false},
  },
}
</script>

<style scoped>
.detail-page{max-width:1100px}
.page-header{display:flex;align-items:center;margin-bottom:1.5rem;gap:.5rem}
.btn-icon-lg{width:42px;height:42px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-md);font-size:var(--font-size-lg)}
.credential-card{display:flex;gap:1.5rem;background:var(--color-bg);border:1px solid var(--color-border-light);border-radius:20px;padding:1.75rem;margin-bottom:1.5rem;box-shadow:var(--shadow-md);flex-wrap:wrap}
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
.cred-value{font-size:.9375rem;font-weight:600}
.cred-empty{color:var(--color-text-muted);font-style:italic}
.cred-footer{display:flex;justify-content:space-between;align-items:center;padding-top:.75rem;border-top:1px solid var(--color-border-light)}
.cred-matricula{font-family:var(--font-family-mono);font-weight:700;color:var(--color-primary);font-size:.875rem;background:var(--color-primary-light);padding:.2rem .625rem;border-radius:6px}
.cred-date{font-size:.75rem;color:var(--color-text-muted)}

.section-toggles{display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap}
.section-btn{display:flex;align-items:center;gap:.625rem;flex:1;min-width:150px;padding:1rem 1.25rem;border:1.5px solid var(--color-border);border-radius:14px;background:var(--color-bg);font-size:.9375rem;font-weight:600;color:var(--color-text);cursor:pointer;transition:all .2s;box-shadow:var(--shadow-xs)}
.section-btn:hover{border-color:var(--color-primary);color:var(--color-primary);transform:translateY(-1px);box-shadow:var(--shadow-md)}
.section-btn i{font-size:1.125rem;color:var(--color-primary)}

.modal-content{border:none;border-radius:20px;overflow:hidden}
.modal-header{padding:1.5rem 1.75rem 1rem;border-bottom:none;display:flex;align-items:flex-start;gap:.875rem}
.modal-icon{width:42px;height:42px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff}
.icon-create{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-hover))}
.icon-edit{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-hover))}
.modal-title{font-size:1.125rem;font-weight:700;margin:0}
.modal-body{padding:0 1.75rem 1.5rem}
.modal-footer{padding:1rem 1.75rem;border-top:1px solid var(--color-border-light);background:var(--color-bg-tertiary);display:flex;justify-content:flex-end;gap:.625rem}

.info-grid{display:flex;flex-direction:column;gap:.75rem}
.info-item{display:flex;justify-content:space-between;padding-bottom:.5rem;border-bottom:1px solid var(--color-border-light)}
.info-item:last-child{border-bottom:none;padding-bottom:0}
.info-label{font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.04em;font-weight:500}
.info-value{font-size:.875rem;font-weight:500;text-align:right}

.att-stats{display:flex;gap:1.5rem;justify-content:center;margin-bottom:1rem}
.att-s{text-align:center}
.att-c{width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:700;margin:0 auto 4px}
.att-c.green{background:var(--color-success-light);color:var(--color-success)}
.att-c.red{background:var(--color-danger-light);color:var(--color-danger)}
.att-c.amber{background:var(--color-warning-light);color:var(--color-warning)}
.att-s span{font-size:.6875rem;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-secondary);font-weight:500}
.att-bar{display:flex;height:10px;border-radius:10px;overflow:hidden;background:var(--color-bg-secondary)}
.att-f{transition:width .3s}
.att-f.green{background:var(--color-success)}
.att-f.red{background:var(--color-danger)}
.att-f.amber{background:var(--color-warning)}

.sch-row{padding:.5rem;background:var(--color-bg-secondary);border-radius:8px;font-size:.875rem;margin-bottom:.375rem}
.cal-row{display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid var(--color-border-light)}
.cal-table{width:100%;border-collapse:collapse;font-size:.8125rem}
.cal-table th{text-align:center;padding:.375rem .25rem;font-size:.625rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);border-bottom:2px solid var(--color-border)}
.cal-table td{text-align:center;padding:.375rem .25rem;border-bottom:1px solid var(--color-border-light)}
.cal-table td:first-child{text-align:left}

.mini-cal{display:grid;grid-template-columns:repeat(5,1fr);gap:1px;background:var(--color-border-light);border-radius:12px;overflow:hidden}
.mc-header{padding:.5rem .25rem;text-align:center;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-bg-tertiary)}
.mc-col{background:var(--color-bg);min-height:80px;padding:.25rem;display:flex;flex-direction:column;gap:2px}
.mc-item{border-radius:6px;padding:.25rem .375rem;color:#fff;font-size:.6875rem}
.mc-time{font-weight:700;font-size:.625rem;opacity:.9}
.mc-subject{font-weight:600;line-height:1.2}
.mc-teacher{font-size:.5625rem;opacity:.8}
.mc-empty{text-align:center;color:var(--color-text-muted);font-size:.75rem;padding:.5rem 0}
.cal-row:last-child{border-bottom:none}

.photo-upload-row{display:flex;align-items:center;gap:1rem}
.photo-pv{position:relative;width:72px;height:72px;border-radius:12px;overflow:hidden;flex-shrink:0}
.photo-pv img{width:100%;height:100%;object-fit:cover}
.photo-ph{width:72px;height:72px;border-radius:12px;border:2px dashed var(--color-border);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;cursor:pointer;color:var(--color-text-muted);font-size:.6875rem}
.photo-rm{position:absolute;top:-4px;right:-4px;width:18px;height:18px;border-radius:50%;border:none;background:var(--color-danger);color:#fff;cursor:pointer;font-size:.5625rem;display:flex;align-items:center;justify-content:center}
.photo-ch{padding:.4rem .875rem;border-radius:8px;border:1px solid var(--color-border);font-size:.75rem;cursor:pointer;color:var(--color-primary);display:inline-flex;align-items:center;gap:.25rem}
.fg{display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem}
.ff{display:flex;flex-direction:column;gap:.2rem}
.ff label{font-size:.75rem;font-weight:500}
.ff .req{color:var(--color-danger)}
.ff.fw{grid-column:1/-1}
.fi{padding:.4375rem .625rem;border:1.5px solid var(--color-border);border-radius:8px;font-size:.8125rem;font-family:var(--font-family);color:var(--color-text);background:var(--color-bg);outline:none}
.fi:focus{border-color:var(--color-primary)}
.ta{resize:vertical;min-height:45px}

.photo-modal{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:pointer}
.photo-modal-content{position:relative;max-width:90vw;max-height:90vh}
.photo-modal-content img{max-width:90vw;max-height:90vh;border-radius:20px;object-fit:contain}
.photo-modal-empty{text-align:center;color:#fff;padding:4rem}
.photo-modal-empty i{opacity:.3;margin-bottom:1rem}
.photo-modal-close{position:absolute;top:-40px;right:0;background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer}
.cal-chevron{display:inline-block;transition:transform .2s;font-size:.625rem;color:var(--color-text-muted)}
.cal-chevron.open{transform:rotate(90deg)}
.semester-block{margin-bottom:1rem}
.semester-title{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-primary);margin:0 0 .625rem;display:flex;align-items:center;gap:.375rem}
.cal-cards{display:flex;flex-direction:column;gap:.5rem}
.cal-card-item{background:var(--color-bg-tertiary);border:1px solid var(--color-border-light);border-radius:10px;padding:.625rem .75rem}
.cal-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;padding-bottom:.375rem;border-bottom:1px solid var(--color-border-light)}
.cal-materia-name{font-size:.8125rem;font-weight:600}
.cal-parciales{display:flex;gap:.5rem}
.cal-parcial{flex:1;text-align:center;padding:.25rem;background:var(--color-bg);border-radius:6px}
.cal-parcial-label{font-size:.5625rem;font-weight:700;text-transform:uppercase;color:var(--color-text-muted);display:block}
.cal-parcial-value{font-size:.9375rem;font-weight:700}
.cal-asistencia{font-size:.5625rem;color:var(--color-text-muted);display:block}
.cal-card-footer{display:flex;justify-content:flex-end;padding-top:.5rem}
</style>
