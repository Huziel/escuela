<template>
  <div>
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon"><i class="fas fa-chart-line"></i></div>
        <div>
          <h1 class="header-title">Seguimiento de Docentes</h1>
          <p class="header-subtitle" v-if="docentes.length">{{ docentes.length }} docentes</p>
          <p class="header-subtitle" v-else>Busca un docente para ver su información</p>
        </div>
      </div>
    </div>

    <div class="search-bar">
      <i class="fas fa-search"></i>
      <input v-model="search" type="text" placeholder="Buscar por nombre, número de control o especialidad..." @input="debounceSearch" />
      <button v-if="search" class="search-clear" @click="search = ''; fetchData()"><i class="fas fa-times"></i></button>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="loading-pulse"></div></div>
    <template v-else>
      <div v-if="!hasSearched" class="empty-state">
        <div class="empty-illustration"><i class="fas fa-search"></i></div>
        <h3>Busca un docente</h3>
        <p>Escribe un nombre o número de control para ver su seguimiento</p>
      </div>
      <div v-else-if="docentes.length === 0" class="empty-state">
        <div class="empty-illustration"><i class="fas fa-user-slash"></i></div>
        <h3>Sin resultados</h3>
      </div>
      <div v-else class="row g-3">
        <div v-for="d in docentes" :key="d.id" class="col-xl-4 col-lg-6">
          <div class="docente-card" @click="$router.push('/docentes/'+d.id)">
            <div class="card-top">
              <img v-if="d.usuario?.fotografia" :src="d.usuario.fotografia" class="card-photo" />
              <div v-else class="card-avatar" :style="{background:avatarColor(d.usuario?.nombre_completo||'')}">{{ initials(d.usuario?.nombre_completo||'?') }}</div>
              <div class="card-info">
                <div class="card-name">{{ d.usuario?.nombre_completo || 'Sin nombre' }}</div>
                <div class="card-meta">
                  <span><i class="fas fa-hashtag"></i> {{ d.numero_empleado }}</span>
                  <span><i class="fas fa-award"></i> {{ d.grado_academico || '-' }}</span>
                </div>
              </div>
              <span :class="['badge', d.estatus==='activo'?'badge-success':'badge-secondary']">{{ d.estatus }}</span>
            </div>
            <div class="card-divider"></div>
            <div class="card-stats">
              <div class="stat"><div class="stat-icon bg-blue"><i class="fas fa-users"></i></div><div class="stat-body"><div class="stat-value">{{ (d.grupos||[]).length }}</div><div class="stat-label">Grupos</div></div></div>
              <div class="stat"><div class="stat-icon bg-green"><i class="fas fa-book"></i></div><div class="stat-body"><div class="stat-value">{{ (d.materias||[]).length }}</div><div class="stat-label">Materias</div></div></div>
              <div class="stat"><div class="stat-icon bg-purple"><i class="fas fa-clock"></i></div><div class="stat-body"><div class="stat-value">{{ (d.horarios||[]).length }}</div><div class="stat-label">Horarios</div></div></div>
            </div>
            <div class="card-footer-detail" v-if="(d.especialidades||[]).length">
              <span v-for="e in d.especialidades" :key="e.id" class="esp-tag">{{ e.nombre }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import api from '@/api/axios'

export default {
  name: 'DocentesSeguimiento',
  data(){return{docentes:[],search:'',loading:false,hasSearched:false,searchTimer:null}},
  methods:{
    initials(n){if(!n)return'?';const p=n.trim().split(' ');return(p[0][0]+(p.length>1?p[p.length-1][0]:'')).toUpperCase()},
    avatarColor(n){const c=['#A91E22','#B99A53','#1E40AF','#059669','#7C3AED','#D97706'];let h=0;for(let i=0;i<(n||'').length;i++)h=n.charCodeAt(i)+((h<<5)-h);return c[Math.abs(h)%c.length]},
    debounceSearch(){clearTimeout(this.searchTimer);this.searchTimer=setTimeout(()=>this.fetchData(),350)},
    async fetchData(){this.hasSearched=true;if(!this.search.trim()){this.docentes=[];return};this.loading=true;try{const{data}=await api.get('/docentes',{params:{search:this.search,per_page:100}});this.docentes=data.data||data||[]}catch(e){this.docentes=[]};this.loading=false},
  },
}
</script>

<style scoped>
.module-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;gap:1rem}
.header-info{display:flex;align-items:center;gap:1rem}
.header-icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-hover));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.35rem;box-shadow:0 4px 12px rgba(169,30,34,0.25)}
.header-title{font-size:1.5rem;font-weight:700;margin:0}
.header-subtitle{font-size:.8125rem;color:var(--color-text-secondary);margin:2px 0 0}
.search-bar{position:relative;max-width:500px;margin-bottom:1.5rem}
.search-bar i{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--color-text-muted)}
.search-bar input{width:100%;padding:.75rem 2.5rem .75rem 2.75rem;border:1.5px solid var(--color-border);border-radius:14px;font-size:.9375rem;color:var(--color-text);background:var(--color-bg);outline:none;transition:all .2s}
.search-bar input:focus{border-color:var(--color-primary);box-shadow:0 0 0 4px rgba(169,30,34,0.08)}
.search-clear{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--color-text-muted);cursor:pointer;padding:.25rem;border-radius:50%}
.loading-pulse{width:40px;height:40px;border-radius:50%;border:3px solid var(--color-border);border-top-color:var(--color-primary);animation:spin .7s linear infinite;margin:0 auto}
@keyframes spin{to{transform:rotate(360deg)}}
.docente-card{background:var(--color-bg);border:1px solid var(--color-border-light);border-radius:18px;overflow:hidden;cursor:pointer;transition:all .2s}
.docente-card:hover{box-shadow:var(--shadow-lg);transform:translateY(-3px)}
.card-top{display:flex;align-items:center;gap:.75rem;padding:1.25rem 1.25rem 0}
.card-photo{width:48px;height:48px;border-radius:14px;object-fit:cover;flex-shrink:0}
.card-avatar{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.1rem;flex-shrink:0}
.card-info{flex:1;min-width:0}
.card-name{font-weight:700;font-size:1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.card-meta{display:flex;gap:.75rem;margin-top:3px;font-size:.75rem;color:var(--color-text-muted);flex-wrap:wrap}
.card-meta span{display:flex;align-items:center;gap:3px}
.card-divider{height:1px;background:var(--color-border-light);margin:1rem 1.25rem 0}
.card-stats{display:flex;gap:.5rem;padding:1rem 1.25rem}
.stat{flex:1;display:flex;align-items:center;gap:.5rem}
.stat-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.8125rem;color:#fff;flex-shrink:0}
.bg-blue{background:#1E40AF}.bg-green{background:#059669}.bg-purple{background:#7C3AED}
.stat-body{min-width:0}.stat-value{font-weight:700;font-size:.9375rem;line-height:1}
.stat-label{font-size:.625rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.04em;margin-top:2px}
.card-footer-detail{display:flex;gap:.375rem;flex-wrap:wrap;padding:.75rem 1.25rem;background:var(--color-bg-tertiary)}
.esp-tag{padding:.15rem .5rem;border-radius:6px;background:var(--color-primary-light);color:var(--color-primary);font-size:.6875rem;font-weight:500}
.empty-state{text-align:center;padding:5rem 2rem}
.empty-illustration{width:96px;height:96px;border-radius:50%;background:var(--color-bg-secondary);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2.5rem;color:var(--color-text-muted)}
.empty-state h3{font-size:1.25rem;font-weight:600;margin:0 0 .375rem}
.empty-state p{font-size:.875rem;color:var(--color-text-secondary);max-width:400px;margin:0 auto}
</style>
