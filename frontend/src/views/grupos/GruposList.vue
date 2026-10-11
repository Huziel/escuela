<template>
  <div class="page-container">
    <!-- ==================== HEADER ==================== -->
    <div class="page-header">
      <div class="page-header__left">
        <i class="fas fa-users page-header__icon"></i>
        <h1 class="page-title">Grupos</h1>
      </div>
      <button class="btn btn--primary" @click="openCreateModal">
        <i class="fas fa-plus btn__icon"></i>
        <span>Nuevo Grupo</span>
      </button>
      <button class="btn btn-sm btn-secondary" @click="$refs.bulkUpload?.show()">
        <i class="fas fa-file-import"></i> Importar
      </button>
      <button class="btn btn-sm btn-outline-secondary" @click="$refs.bulkUploadHorario?.show()">
        <i class="fas fa-clock"></i> Importar Horarios
      </button>
    </div>

    <!-- ==================== FILTER TOGGLE (MOBILE) ==================== -->
    <button
      class="filter-toggle"
      type="button"
      @click="showFilters = !showFilters"
      :aria-expanded="showFilters"
    >
      <i class="fas fa-sliders"></i>
      <span>Filtros</span>
      <span v-if="activeFilterCount" class="filter-count-badge">{{ activeFilterCount }}</span>
      <i :class="showFilters ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="filter-toggle-arrow"></i>
    </button>

    <!-- ==================== FILTER BAR ==================== -->
    <div class="filter-bar" :class="{ 'filter-bar--open': showFilters }">
      <div class="filter-group filter-group--search">
        <div class="input-group">
          <span class="input-group__icon"><i class="fas fa-search"></i></span>
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Buscar..."
            @blur="fetchData"
          />
        </div>
      </div>
      <div class="filter-group">
        <label class="form-label">Estado</label>
        <select v-model="filters.activo" class="form-control" @change="fetchData">
          <option value="">Todos</option>
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="form-label">Semestre</label>
        <select v-model="filters.semestre" class="form-control" @change="fetchData">
          <option value="">Todos</option>
          <option v-for="s in 6" :key="s" :value="s">{{ s }}°</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="form-label">Especialidad</label>
        <select v-model="filters.especialidad_id" class="form-control" @change="fetchData">
          <option value="">Todas</option>
          <option v-for="e in especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="form-label">Turno</label>
        <select v-model="filters.turno" class="form-control" @change="fetchData">
          <option value="">Todos</option>
          <option value="matutino">Matutino</option>
          <option value="vespertino">Vespertino</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="form-label">Periodo</label>
        <select v-model="filters.ciclo_escolar" class="form-control" @change="fetchData">
          <option value="">Todos</option>
          <option value="2025-1">2025-1</option>
          <option value="2025-2">2025-2</option>
          <option value="2026-1">2026-1</option>
          <option value="2026-2">2026-2</option>
        </select>
      </div>
    </div>

    <!-- ==================== TABLE ==================== -->
    <div class="card">
      <div class="card-body">
        <BaseTable :columns="columns" :items="items" :loading="loading">
          <template #actions="{ item }">
            <div class="table-actions">
              <button class="btn-icon btn-icon--students" @click="openAlumnosModal(item)" title="Alumnos">
                <i class="fas fa-user-graduate"></i>
              </button>
              <button class="btn-icon btn-icon--schedule" @click="openScheduleEditor(item)" title="Horario">
                <i class="fas fa-clock"></i>
              </button>
              <button class="btn-icon btn-icon--edit" @click="openEditModal(item)">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn-icon btn-icon--delete" @click="confirmDelete(item)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </template>
          <template #cell-activo="{ value }">
            <span :class="['badge', value ? 'badge--success' : 'badge--secondary']">
              {{ value ? 'Activo' : 'Inactivo' }}
            </span>
          </template>
        </BaseTable>

        <BasePagination
          v-if="pagination.total > 0"
          :currentPage="pagination.current_page"
          :totalPages="pagination.last_page"
          @page-change="onPageChange"
        />
      </div>
    </div>

    <!-- ==================== FORM MODAL ==================== -->
    <BaseModal ref="formModal" :title="formTitle" modalId="grupoModal" size="lg">
      <div class="form-grid">
        <div class="form-group">
          <label for="nombre" class="form-label">Nombre <span class="required">*</span></label>
          <input v-model="form.nombre" id="nombre" type="text" class="form-control" required />
        </div>
        <div class="form-group">
          <label for="ciclo_escolar" class="form-label">Periodo <span class="required">*</span></label>
          <select v-model="form.ciclo_escolar" id="ciclo_escolar" class="form-control" @change="form.semestre = ''" required>
            <option value="">Seleccionar periodo</option>
            <option v-for="c in ciclos" :key="c.id" :value="c.nombre">{{ c.nombre }}</option>
          </select>
        </div>
        <div class="form-group" v-if="form.ciclo_escolar">
          <label for="semestre" class="form-label">Semestre <span class="required">*</span></label>
          <select v-model="form.semestre" id="semestre" class="form-control" required>
            <option value="">Seleccionar semestre</option>
            <option v-for="s in semestresDisponibles" :key="s" :value="s">{{ s }}° Semestre</option>
          </select>
        </div>
        <div class="form-group">
          <label for="especialidad_id" class="form-label">Especialidad <span class="required">*</span></label>
          <select v-model="form.especialidad_id" id="especialidad_id" class="form-control" required>
            <option value="">Seleccione...</option>
            <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">{{ esp.nombre }}</option>
          </select>
        </div>
        <div class="form-group">
          <label for="turno" class="form-label">Turno <span class="required">*</span></label>
          <select v-model="form.turno" id="turno" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="matutino">Matutino</option>
            <option value="vespertino">Vespertino</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Bloque Horario</label>
          <div class="d-flex gap-2">
            <div style="flex:1">
              <input type="time" v-model="form.hora_inicio" class="form-control" placeholder="Inicio" />
              <small class="text-muted">Inicio</small>
            </div>
            <div style="flex:1">
              <input type="time" v-model="form.hora_fin" class="form-control" placeholder="Fin" />
              <small class="text-muted">Fin</small>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="aula_id" class="form-label">Aula</label>
          <select v-model="form.aula_id" id="aula_id" class="form-control" @change="onAulaChange">
            <option value="">Sin aula asignada</option>
            <option v-for="a in aulas" :key="a.id" :value="a.id">{{ a.nombre }} {{ a.edificio ? '(' + a.edificio + ')' : '' }}</option>
          </select>
          <small v-if="aulaSeleccionada" class="text-muted mt-1 d-block">
            <i class="fas fa-users"></i> Capacidad: {{ aulaSeleccionada.capacidad }} alumnos
          </small>
        </div>
        <div class="form-group">
          <label for="tutor_id" class="form-label">Tutor</label>
          <SearchSelect v-model="form.tutor_id" :options="tutorOptions" placeholder="Seleccionar tutor..." />
        </div>
        <div class="form-group">
          <label for="activo" class="form-label">Activo</label>
          <select v-model="form.activo" id="activo" class="form-control">
            <option :value="true">Activo</option>
            <option :value="false">Inactivo</option>
          </select>
        </div>
      </div>
      <template #footer>
        <button class="btn btn--secondary" @click="closeModal">Cancelar</button>
        <button class="btn btn--primary" :disabled="saving" @click="save">
          <i v-if="saving" class="fas fa-spinner fa-spin"></i>
          Guardar
        </button>
      </template>
    </BaseModal>

    <!-- ==================== DOCENTES MODAL ==================== -->
    <BaseModal ref="docentesModal" :title="'Maestros - ' + grupoSeleccionado?.nombre" modalId="docentesModal" size="lg">
      <div class="mb-3">
        <label class="form-label">Asignar docente</label>
        <div class="d-flex gap-2">
          <div style="flex:1">
            <SearchSelect v-model="docenteAsignar" :options="docentesOptions" placeholder="Seleccionar docente..." />
          </div>
          <button class="btn btn--primary" :disabled="!docenteAsignar || asignando" @click="asignarDocente">
            <i v-if="asignando" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-plus"></i> Asignar
          </button>
        </div>
        <div v-if="errorDocente" class="text-danger small mt-1">{{ errorDocente }}</div>
        <div class="small text-muted mt-1">
          <i class="fas fa-info-circle"></i> Máximo {{ maxDocentesPorGrupo }} docentes por grupo | Máximo {{ maxHorasDiarias }} horas diarias
        </div>
      </div>
      <div v-if="docentesGrupo.length === 0" class="text-center text-muted py-3">Sin docentes asignados</div>
      <div v-else class="list-group">
        <div v-for="d in docentesGrupo" :key="d.id" class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong>{{ d.usuario?.nombre_completo || d.nombre_completo }}</strong>
            <br /><small class="text-muted">{{ d.especialidad || d.grado_academico || '-' }}</small>
          </div>
          <button class="btn btn-sm btn-outline-danger" @click="removerDocente(d.id)">
            <i class="fas fa-trash-can"></i>
          </button>
        </div>
      </div>
    </BaseModal>

    <!-- ==================== SCHEDULE MODAL ==================== -->
    <div class="modal fade" ref="scheduleModal" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon icon-create"><i class="fas fa-calendar-week"></i></div>
            <div>
              <h5 class="modal-title">Horario - {{ scheduleGrupo?.nombre }}</h5>
              <p class="modal-subtitle" v-if="scheduleGrupo?.hora_inicio">Bloque: {{ scheduleGrupo.hora_inicio }} – {{ scheduleGrupo.hora_fin }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-2">
            <div v-if="scheduleGrupo && !scheduleGrupo.hora_inicio" class="alert alert-warning">
              <i class="fas fa-triangle-exclamation"></i> Define el bloque horario en Editar Grupo.
            </div>

            <!-- DESKTOP: Grid 6-columnas -->
            <div v-else class="cal-wrapper desktop-only">
              <div class="cal-grid">
                <div class="cal-head">Hora</div>
                <div v-for="d in diasSemana" :key="d.val" class="cal-head">{{ d.label }}</div>
                <template v-for="slot in timeSlots" :key="slot.key">
                  <div class="cal-time">{{ slot.label }}</div>
                  <div v-for="d in diasSemana" :key="d.val" class="cal-cell" @click="openCellForm(d.val, slot)">
                    <div v-for="(h,i) in horariosEnCelda(d.val,slot)" :key="h.id||i" class="cal-block" :style="{background:h._color}" @click.stop="editBlock(h)">
                      <div class="cal-block-subject">{{ h._materia }}</div>
                      <div class="cal-block-teacher" v-if="h._docente">{{ h._docente }}</div>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <!-- MOBILE: Day list with swipe (<768px) -->
            <div class="schedule-mobile mobile-only">
              <!-- Day selector tabs (horizontally scrollable) -->
              <div class="sch-day-tabs">
                <button
                  v-for="(d, idx) in diasSemana"
                  :key="d.val"
                  class="sch-day-tab"
                  :class="{ 'sch-day-tab--active': scheduleActiveDayIndex === idx }"
                  @click="setScheduleDay(idx)"
                >
                  {{ d.label }}
                  <span class="sch-day-count">{{ horariosDelDia(d.val).length }}</span>
                </button>
              </div>

              <!-- Swipe container -->
              <div
                class="sch-day-list"
                @touchstart="onScheduleSwipeStart"
                @touchend="onScheduleSwipeEnd"
              >
                <div class="sch-day-header">
                  <button class="sch-nav-btn" :disabled="scheduleActiveDayIndex === 0" @click="scheduleActiveDayIndex--">
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <span class="sch-day-name">{{ diasSemana[scheduleActiveDayIndex].label }}</span>
                  <button class="sch-nav-btn" :disabled="scheduleActiveDayIndex === diasSemana.length - 1" @click="scheduleActiveDayIndex++">
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </div>

                <div v-if="!horariosDelDia(diasSemana[scheduleActiveDayIndex].val).length" class="sch-empty">
                  <i class="fas fa-calendar-xmark"></i>
                  <span>Sin clases este día</span>
                </div>
                <div v-else class="sch-cards">
                  <div
                    v-for="h in horariosDelDia(diasSemana[scheduleActiveDayIndex].val)"
                    :key="h.id"
                    class="sch-card"
                    :style="{ borderLeftColor: h._color }"
                    @click="editBlock(h)"
                  >
                    <div class="sch-card-bar" :style="{ background: h._color }"></div>
                    <div class="sch-card-body">
                      <div class="sch-card-subject">{{ h._materia }}</div>
                      <div class="sch-card-meta">
                        <span><i class="fas fa-clock"></i> {{ h.hora_inicio }} – {{ h.hora_fin }}</span>
                        <span v-if="h._docente"><i class="fas fa-chalkboard-user"></i> {{ h._docente }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add button for current day -->
              <button
                class="sch-add-btn"
                @click="openCellFormForDay(diasSemana[scheduleActiveDayIndex].val)"
                :disabled="!scheduleGrupo?.hora_inicio"
              >
                <i class="fas fa-plus"></i> Agregar horario
              </button>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-danger btn-sm me-auto" v-if="editingBlock" @click="deleteBlock"><i class="fas fa-trash-can"></i> Eliminar</button>
            <span class="small text-muted me-2" v-if="editingBlock">Editando horario</span>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== CELL FORM MODAL ==================== -->
    <div class="modal fade" ref="cellFormModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" :class="cellFormId?'icon-edit':'icon-create'"><i :class="cellFormId?'fas fa-pen-to-square':'fas fa-plus'"></i></div>
            <div><h5 class="modal-title">{{ cellFormId?'Editar':'Nuevo' }} Horario</h5></div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-grid cols-2">
              <div class="form-field"><label>Materia</label><select v-model="cellForm.materia_id" class="field-input" @change="onCellMateriaChange"><option value="">Seleccionar</option><option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option></select></div>
              <div class="form-field"><label>Docente</label><select v-model="cellForm.docente_id" class="field-input"><option value="">Seleccionar</option><option v-for="d in cellDocentes" :key="d.id" :value="d.id">{{ d.usuario?.nombre_completo||d.nombre_completo }}</option></select></div>
              <div class="form-field"><label>Día</label><input class="field-input" :value="cellForm.dia" disabled /></div>
              <div class="form-field"><label>Hora Inicio</label><input class="field-input" :value="cellForm.hora_inicio" disabled /></div>
              <div class="form-field">
                <label>Hora Fin <span class="req">*</span></label>
                <div class="time-selects">
                  <select v-model="cellForm._hora" class="field-input" @change="updateHoraFin">
                    <option value="">Hora</option>
                    <option v-for="h in horasDisponibles" :key="h.val" :value="h.val">{{ h.label }}</option>
                  </select>
                  <select v-model="cellForm._minuto" class="field-input" @change="updateHoraFin">
                    <option value="">Min</option>
                    <option value="00">00</option>
                    <option value="30">30</option>
                  </select>
                </div>
                <small class="text-muted">Desde {{ cellForm.hora_inicio }} hasta {{ scheduleGrupo?.hora_fin }}</small>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" @click="saveCellForm" :disabled="savingCell"><i v-if="savingCell" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== ALUMNOS MODAL (fullscreen mobile) ==================== -->
    <div class="modal fade" ref="alumnosModal" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon icon-create"><i class="fas fa-user-graduate"></i></div>
            <div>
              <h5 class="modal-title">Alumnos - {{ alumnosGrupo?.nombre }}</h5>
              <p class="modal-subtitle">{{ alumnosGrupoCount() }} / {{ alumnosGrupo?.capacidad || '?' }} cupo</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body alumnos-modal-body">
            <!-- Assigned students -->
            <div v-if="alumnosGrupoList.length" class="alumnos-section">
              <h6 class="section-label">En este grupo</h6>
              <div class="alumno-cards">
                <div v-for="a in alumnosGrupoList" :key="a.id" class="alumno-mini" @click="goToAlumno(a.id)" style="cursor:pointer">
                  <img v-if="a.usuario?.fotografia||a.user?.fotografia" :src="a.usuario?.fotografia||a.user?.fotografia" class="am-photo" />
                  <div v-else class="am-avatar" :style="{background:alumnoColor(a)}">{{ (a.usuario?.nombre_completo||a.user?.nombre_completo||a.nombre||'?').charAt(0) }}</div>
                  <div class="am-info"><strong>{{ a.usuario?.nombre_completo||a.nombre }}</strong><small>{{ a.matricula }}</small></div>
                  <button class="btn btn-sm btn-outline-danger" @click.stop="removerAlumnoDelGrupo(a)"><i class="fas fa-times"></i></button>
                </div>
              </div>
            </div>

            <!-- Add students section -->
            <h6 class="section-label">Agregar alumnos</h6>

            <!-- Sticky search bar -->
            <div class="alumnos-search-sticky">
              <div class="input-group">
                <span class="input-group__icon"><i class="fas fa-search"></i></span>
                <input
                  v-model="alumnosSearch"
                  class="form-control alumnos-search-input"
                  placeholder="Buscar por nombre o matrícula..."
                  autocomplete="off"
                />
              </div>
            </div>

            <!-- Available students -->
            <div class="alumno-cards">
              <div v-for="a in alumnosDisponiblesFiltrados" :key="a.id" class="alumno-mini" :class="{selected:selectedAlumnos.includes(a.id)}" @click="toggleAlumno(a)">
                <div class="am-check"><i :class="selectedAlumnos.includes(a.id)?'fas fa-check-circle':'far fa-circle'"></i></div>
                <img v-if="a.usuario?.fotografia||a.user?.fotografia" :src="a.usuario?.fotografia||a.user?.fotografia" class="am-photo" />
                <div v-else class="am-avatar" :style="{background:alumnoColor(a)}">{{ (a.usuario?.nombre_completo||a.user?.nombre_completo||a.nombre||'?').charAt(0) }}</div>
                <div class="am-info"><strong>{{ a.usuario?.nombre_completo||a.nombre }}</strong><small>{{ a.matricula }} · {{ a.semestre }}° · {{ a.especialidad?.nombre||'-' }}</small></div>
              </div>
              <div v-if="!alumnosDisponiblesFiltrados.length" class="text-muted p-3 text-center">Sin alumnos disponibles</div>
            </div>
          </div>
          <div class="modal-footer alumnos-modal-footer">
            <span class="small text-muted me-auto">{{ selectedAlumnos.length }} seleccionados</span>
            <button class="btn btn--secondary" data-bs-dismiss="modal">Cerrar</button>
            <button class="btn btn--primary" @click="asignarAlumnosSeleccionados" :disabled="!selectedAlumnos.length||savingAlumnos">
              <i v-if="savingAlumnos" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-user-plus"></i> Asignar
            </button>
          </div>
        </div>
      </div>
    </div>

    <BulkUploadModal ref="bulkUpload" type="grupos" title="Grupos" templateLabel="Descargar plantilla Excel" @imported="fetchData" />
    <BulkUploadModal ref="bulkUploadHorario" type="horarios" title="Horarios" templateLabel="Descargar plantilla Excel" @imported="fetchData" />
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseTable from '@/components/ui/BaseTable.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BasePagination from '@/components/ui/BasePagination.vue'
import SearchSelect from '@/components/ui/SearchSelect.vue'
import BulkUploadModal from '@/components/ui/BulkUploadModal.vue'

export default {
  name: 'GruposList',
  components: { BaseCard, BaseTable, BaseModal, BaseButton, BaseInput, BasePagination, SearchSelect, BulkUploadModal },
  data() {
    return {
      items: [],
      especialidades: [],
      docentes: [],
      aulas: [],
      ciclos: [],
      materias: [],
      loading: false,
      saving: false,
      editingId: null,
      columns: [
        { key: 'nombre', label: 'Nombre' },
        { key: 'semestre', label: 'Semestre' },
        { key: 'especialidad_nombre', label: 'Especialidad' },
        { key: 'turno', label: 'Turno' },
        { key: 'capacidad', label: 'Capacidad' },
        { key: 'ciclo_escolar', label: 'Ciclo Escolar' },
        { key: 'activo', label: 'Activo' },
      ],
      filters: {
        search: '',
        activo: '',
        semestre: '',
        especialidad_id: '',
        ciclo_escolar: '',
        turno: '',
        page: 1,
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15,
      },
      form: {
        nombre: '',
        semestre: '',
        especialidad_id: '',
        turno: '',
        capacidad: '',
        tutor_id: '',
        aula_id: '',
        ciclo_escolar: '',
        activo: true,
      },
      // --- filter toggle (mobile) ---
      showFilters: true,

      grupoSeleccionado: null,
      docentesGrupo: [],
      docenteAsignar: '',
      asignando: false,
      errorDocente: '',
      maxDocentesPorGrupo: 5,
      maxHorasDiarias: 8,
      scheduleGrupo: null,
      scheduleHorarios: [],
      scheduleGrupo: null,
      scheduleModalInstance: null,
      diasSemana: [{val:'lunes',label:'Lun'},{val:'martes',label:'Mar'},{val:'miercoles',label:'Mié'},{val:'jueves',label:'Jue'},{val:'viernes',label:'Vie'}],
      cellForm: {materia_id:'',docente_id:'',dia:'',hora_inicio:'',hora_fin:'',_hora:'',_minuto:''},
      cellFormId: null, savingCell: false, cellDocentes: [], cellModal: null, editingBlock: null, horaFinError: '',
      // --- schedule mobile swipe ---
      scheduleActiveDayIndex: 0,
      touchStartX: null,

      alumnosGrupo: null, alumnosGrupoList: [], alumnosAll: [], selectedAlumnos: [], alumnosSearch: '', savingAlumnos: false, alumnosModalInstance: null,
      timetableGrupo: null, timetableHorarios: [], timetableLoading: false, timetableModalInstance: null,
    }
  },
  computed: {
    formTitle() {
      return this.editingId ? 'Editar Grupo' : 'Nuevo Grupo'
    },
    docentesDisponibles() {
      const ids = this.docentesGrupo.map(d => d.id)
      return this.docentes.filter(d => !ids.includes(d.id))
    },
    aulaSeleccionada() {
      return this.aulas.find(a => a.id == this.form.aula_id) || null
    },
    tutorOptions() {
      return this.docentes.map(d => ({
        value: d.id,
        label: d.usuario?.nombre_completo || d.nombre || 'Sin nombre',
        sub: d.especialidad || d.grado_academico || '',
      }))
    },
    semestresDisponibles() {
      if (!this.form.ciclo_escolar) return []
      const sufijo = this.form.ciclo_escolar.split('-').pop()
      return sufijo === '1' ? [1, 3, 5] : sufijo === '2' ? [2, 4, 6] : [1,2,3,4,5,6]
    },
    alumnosDisponiblesFiltrados() {
      const all = this.alumnosAll || []
      if (!this.alumnosSearch) return all
      const q = this.alumnosSearch.toLowerCase()
      return all.filter(a => {
        const name = a.usuario?.nombre_completo || a.user?.nombre_completo || a.nombre || a.nombre_completo || ''
        const mat = a.matricula || ''
        return name.toLowerCase().includes(q) || mat.toLowerCase().includes(q)
      })
    },
    timeSlots() {
      if (!this.scheduleGrupo?.hora_inicio || !this.scheduleGrupo?.hora_fin) return []
      const slots = []; const start = this.timeToMinutes(this.scheduleGrupo.hora_inicio); const end = this.timeToMinutes(this.scheduleGrupo.hora_fin)
      for (let t = start; t < end; t += 60) {
        const h = Math.floor(t/60); const m = t%60
        slots.push({key:'s'+t,label:String(h).padStart(2,'0')+':'+String(m).padStart(2,'0'),start:t,end:t+60})
      }
      return slots
    },
    horasDisponibles() {
      if (!this.scheduleGrupo?.hora_fin || !this.cellForm.hora_inicio) return []
      const start = this.timeToMinutes(this.cellForm.hora_inicio)
      const end = this.timeToMinutes(this.scheduleGrupo.hora_fin)
      const hours = []
      for (let t = Math.ceil(start/60)*60; t < end; t += 60) {
        const h = Math.floor(t/60)
        hours.push({val:String(h).padStart(2,'0'),label:String(h).padStart(2,'0')+':00'})
      }
      return hours
    },
    timetableSlots() {
      if (!this.timetableGrupo?.hora_inicio || !this.timetableGrupo?.hora_fin) return []
      const start = this.timeToMinutes(this.timetableGrupo.hora_inicio)
      const end = this.timeToMinutes(this.timetableGrupo.hora_fin)
      const slots = []
      const interval = 30
      for (let t = start; t < end; t += interval) {
        const h = Math.floor(t / 60)
        const m = t % 60
        const label = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0')
        slots.push({ key: 's' + t, label, start: t, end: t + interval })
      }
      return slots
    },
    docentesOptions() {
      return this.docentesDisponibles.map(d => ({
        value: d.id,
        label: d.usuario?.nombre_completo || d.nombre_completo || 'Sin nombre',
        sub: d.especialidad || d.grado_academico || d.email || '',
      }))
    },
    activeFilterCount() {
      let count = 0
      if (this.filters.search) count++
      if (this.filters.activo !== '') count++
      if (this.filters.semestre !== '') count++
      if (this.filters.especialidad_id !== '') count++
      if (this.filters.turno !== '') count++
      if (this.filters.ciclo_escolar !== '') count++
      return count
    },
    horariosDelDia() {
      return (dia) => {
        return this.scheduleHorarios
          .filter(h => {
            if (!h.hora_inicio || !h.hora_fin) return false
            return String(h.dia || '').toLowerCase() === dia
          })
          .sort((a, b) => this.timeToMinutes(a.hora_inicio) - this.timeToMinutes(b.hora_inicio))
      }
    },
  },
  mounted() {
    this.fetchData()
    this.fetchEspecialidades()
    this.fetchDocentes()
    this.fetchAulas()
    this.fetchCiclos()
    this.fetchMaterias()
  },
  methods: {
    async fetchData() {
      this.loading = true
      try {
        const params = { ...this.filters, per_page: 100 }
        const { data } = await api.get('/grupos', { params })
        this.items = (data.data || []).map(item => ({
          ...item,
          especialidad_nombre: item.especialidad?.nombre || '-',
        }))
        if (data.meta) {
          this.pagination = { current_page: data.meta.current_page, last_page: data.meta.last_page, total: data.meta.total, per_page: data.meta.per_page }
        }
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    async fetchEspecialidades() {
      try {
        const { data } = await api.get('/especialidades/all')
        this.especialidades = data.data || data || []
      } catch (e) {
        console.error(e)
      }
    },
    async fetchDocentes() {
      try {
        const { data } = await api.get('/docentes/all')
        this.docentes = data.data || data || []
      } catch (e) {
        console.error(e)
      }
    },
    async fetchAulas() {
      try {
        const { data } = await api.get('/aulas/list')
        this.aulas = data.data || data || []
      } catch (e) { console.error(e) }
    },
    async fetchCiclos() {
      try {
        const { data } = await api.get('/configuracion/ciclos')
        this.ciclos = data.data || data || []
      } catch (e) { console.error(e) }
    },
    async fetchMaterias() {
      try {
        const { data } = await api.get('/materias?per_page=100')
        this.materias = data.data || data || []
      } catch (e) { console.error(e) }
    },
    onAulaChange() {
      if (this.aulaSeleccionada) {
        this.form.capacidad = this.aulaSeleccionada.capacidad
      }
    },
    onPageChange(page) {
      this.filters.page = page
      this.fetchData()
    },
    openCreateModal() {
      this.editingId = null
      this.resetForm()
      this.$refs.formModal.show()
    },
    openEditModal(item) {
      this.editingId = item.id
      this.form = {
        grupo_id: item.id,
        nombre: item.nombre || '',
        semestre: item.semestre || '',
        especialidad_id: item.especialidad_id || '',
        turno: item.turno || '',
        capacidad: item.capacidad || '',
        hora_inicio: item.hora_inicio || '',
        hora_fin: item.hora_fin || '',
        tutor_id: item.tutor_id || '',
        aula_id: item.aula_id || '',
        ciclo_escolar: item.ciclo_escolar || '',
        activo: item.activo !== undefined ? item.activo : true,
      }
      this.$refs.formModal.show()
    },
    resetForm() {
      this.form = {
        nombre: '',
        semestre: '',
        especialidad_id: '',
        turno: '',
        capacidad: '',
        hora_inicio: '',
        hora_fin: '',
        tutor_id: '',
        aula_id: '',
        ciclo_escolar: '',
        activo: true,
      }
    },
    closeModal() {
      this.$refs.formModal.hide()
    },
    async save() {
      this.saving = true
      try {
        if (this.editingId) {
          await api.put(`/grupos/${this.editingId}`, this.form)
          Swal.fire('Actualizado', 'Grupo actualizado correctamente.', 'success')
        } else {
          await api.post('/grupos', this.form)
          Swal.fire('Creado', 'Grupo registrado correctamente.', 'success')
        }
        this.closeModal()
        this.fetchData()
      } catch (e) {
        console.error(e)
      } finally {
        this.saving = false
      }
    },
    confirmDelete(item) {
      Swal.fire({
        title: 'Eliminar grupo',
        text: `¿Estás seguro de eliminar el grupo ${item.nombre}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {
          this.deleteItem(item.id)
        }
      })
    },
    async deleteItem(id) {
      try {
        await api.delete(`/grupos/${id}`)
        Swal.fire('Eliminado', 'Grupo eliminado correctamente.', 'success')
        this.fetchData()
      } catch (e) {
        console.error(e)
      }
    },
    async openDocentesModal(item) {
      this.grupoSeleccionado = item
      this.docenteAsignar = ''
      this.errorDocente = ''
      try {
        const { data } = await api.get('/grupos/' + item.id + '/docentes')
        this.docentesGrupo = data.data || data || []
      } catch (e) { this.docentesGrupo = [] }
      this.$refs.docentesModal.show()
    },
    async asignarDocente() {
      this.errorDocente = ''; this.asignando = true
      try {
        await api.post('/grupos/' + this.grupoSeleccionado.id + '/docentes', { docente_id: this.docenteAsignar })
        const { data } = await api.get('/grupos/' + this.grupoSeleccionado.id + '/docentes')
        this.docentesGrupo = data.data || data || []
        this.docenteAsignar = ''
      } catch (e) {
        this.errorDocente = e.response?.data?.message || 'Error al asignar docente.'
      }
      this.asignando = false
    },
    async removerDocente(docenteId) {
      try {
        await api.delete('/grupos/' + this.grupoSeleccionado.id + '/docentes/' + docenteId)
        this.docentesGrupo = this.docentesGrupo.filter(d => d.id !== docenteId)
      } catch (e) { /* */ }
    },
    async openScheduleEditor(item) {
      this.loadScheduleData(item)
    },
    async openScheduleModal(item) {
      this.loadScheduleData(item)
    },
    async loadScheduleData(item) {
      this.scheduleActiveDayIndex = 0
      this.scheduleGrupo = {...item, hora_inicio:(item.hora_inicio||'').substring(0,5), hora_fin:(item.hora_fin||'').substring(0,5)}
      this.scheduleHorarios = []
      try {
        const { data } = await api.get('/horarios', { params: { grupo_id: item.id, per_page: 50 } })
        this.scheduleHorarios = (data.data || data || []).map(h => ({
          ...h, _key:'e'+h.id+Date.now(),
          hora_inicio: (h.hora_inicio||'').includes(' ')?(h.hora_inicio||'').split(' ')[1]:h.hora_inicio,
          hora_fin: (h.hora_fin||'').includes(' ')?(h.hora_fin||'').split(' ')[1]:h.hora_fin,
          _color: this.slotColor(h.materia_id||0),
          _materia: this.getMateriaName(h.materia_id),
          _docente: h.docente?.user?.nombre_completo || h.docente?.nombre || h.docente_nombre || '',
        }))
      } catch(e) { this.scheduleHorarios = [] }
      if (!this.scheduleModalInstance) this.scheduleModalInstance = new Modal(this.$refs.scheduleModal)
      this.scheduleModalInstance.show()
    },
    horariosEnCelda(dia, slot) {
      return this.scheduleHorarios.filter(h => {
        if (!h.hora_inicio || !h.hora_fin) return false
        const hDia = String(h.dia||'').toLowerCase()
        if (hDia !== dia) return false
        const hStart = this.timeToMinutes(h.hora_inicio)
        const hEnd = this.timeToMinutes(h.hora_fin)
        return hStart < slot.end && hEnd > slot.start
      })
    },
    openCellForm(dia, slot) {
      const hh = Math.floor(slot.start/60); const mm = slot.start%60
      this.cellForm = {materia_id:'',docente_id:'',dia,hora_inicio:String(hh).padStart(2,'0')+':'+String(mm).padStart(2,'0'),hora_fin:'',_hora:'',_minuto:''}
      this.cellFormId = null; this.cellDocentes = []; this.horaFinError = ''
      if (!this.cellModal) this.cellModal = new Modal(this.$refs.cellFormModal)
      this.cellModal.show()
    },
    openCellFormForDay(dia) {
      // For mobile: open cell form with the first available time slot for the selected day
      const slots = this.timeSlots
      if (!slots.length) return
      const slot = slots[0]
      const hh = Math.floor(slot.start/60); const mm = slot.start%60
      this.cellForm = {materia_id:'',docente_id:'',dia,hora_inicio:String(hh).padStart(2,'0')+':'+String(mm).padStart(2,'0'),hora_fin:'',_hora:'',_minuto:''}
      this.cellFormId = null; this.cellDocentes = []; this.horaFinError = ''
      if (!this.cellModal) this.cellModal = new Modal(this.$refs.cellFormModal)
      this.cellModal.show()
    },
    editBlock(h) {
      const fin = (h.hora_fin||'').substring(0,5)
      const [fh, fm] = fin.split(':')
      this.cellForm = {materia_id:h.materia_id,docente_id:h.docente_id,dia:String(h.dia||'').toLowerCase(),hora_inicio:(h.hora_inicio||'').substring(0,5),hora_fin:fin,_hora:fh||'',_minuto:fm||''}
      this.cellFormId = h.id; this.cellDocentes = []; this.horaFinError = ''
      this.onCellMateriaChange()
      if (!this.cellModal) this.cellModal = new Modal(this.$refs.cellFormModal)
      this.cellModal.show()
    },
    async onCellMateriaChange() {
      this.cellForm.docente_id = ''; this.cellDocentes = []
      if (!this.cellForm.materia_id) return
      try {
        const { data } = await api.get('/horarios/docentes-por-materia', {params:{materia_id:this.cellForm.materia_id}})
        this.cellDocentes = data.data || data || []
      } catch(e) {/* */}
    },
    updateHoraFin() {
      if (this.cellForm._hora && this.cellForm._minuto !== '') {
        this.cellForm.hora_fin = this.cellForm._hora + ':' + (this.cellForm._minuto || '00')
      }
    },
    async saveCellForm() {
      if (!this.cellForm.materia_id || !this.cellForm.docente_id || !this.cellForm._hora) {
        Swal.fire('Campos incompletos','Selecciona materia, docente y hora fin.','warning'); return
      }
      const hInicio = this.cellForm.hora_inicio
      const hFin = this.cellForm.hora_fin
      const bloqueFin = this.scheduleGrupo?.hora_fin
      if (hFin <= hInicio) {
        Swal.fire('Hora inválida','La hora de fin debe ser posterior a la de inicio.','warning'); return
      }
      if (bloqueFin && hFin > bloqueFin) {
        Swal.fire('Fuera de bloque','La hora fin no puede superar el bloque del grupo ('+bloqueFin+').','warning'); return
      }
      this.savingCell = true
      try {
        const p = {grupo_id:this.scheduleGrupo.id,materia_id:Number(this.cellForm.materia_id),docente_id:Number(this.cellForm.docente_id),dia:this.cellForm.dia,hora_inicio:this.cellForm.hora_inicio,hora_fin:this.cellForm.hora_fin}
        if (!p.hora_fin || p.hora_fin <= p.hora_inicio) {
          Swal.fire('Hora inválida','Selecciona una hora fin válida.','warning'); this.savingCell = false; return
        }
        if (this.cellFormId) { await api.put('/horarios/'+this.cellFormId,p) } else { await api.post('/horarios',p) }
        this.cellModal.hide(); this.loadScheduleData(this.scheduleGrupo)
      } catch(e) { Swal.fire('Error','No se pudo guardar.','error') }
      this.savingCell = false
    },
    async deleteBlock() {
      if (!this.cellFormId) return
      const r = await Swal.fire({title:'Eliminar horario',text:'¿Estás seguro?',icon:'warning',showCancelButton:true,confirmButtonText:'Eliminar',confirmButtonColor:'#DC2626'})
      if (!r.isConfirmed) return
      try { await api.delete('/horarios/'+this.cellFormId); this.cellModal.hide(); this.loadScheduleData(this.scheduleGrupo) } catch(e) { Swal.fire('Error','No se pudo eliminar.','error') }
    },
    async openAlumnosModal(item) {
      this.alumnosGrupo = item; this.selectedAlumnos = []; this.alumnosSearch = ''
      try {
        const [resG, resD] = await Promise.all([api.get('/grupos/'+item.id+'/alumnos'),api.get('/alumnos',{params:{per_page:200}})])
        this.alumnosGrupoList = resG.data.data || resG.data || []
        this.alumnosAll = (resD.data.data || resD.data || []).filter(a => !this.alumnosGrupoList.find(g => g.id === a.id) && a.estatus !== 'irregular')
      } catch(e) { this.alumnosGrupoList = []; this.alumnosAll = [] }
      if (!this.alumnosModalInstance) this.alumnosModalInstance = new Modal(this.$refs.alumnosModal)
      this.alumnosModalInstance.show()
    },
    alumnosGrupoCount() { return this.alumnosGrupoList?.length || 0 },
    alumnosDisponibles() {
      if (!this.alumnosSearch) return this.alumnosAll
      const q = this.alumnosSearch.toLowerCase()
      return this.alumnosAll.filter(a => (a.usuario?.nombre_completo||a.nombre||'').toLowerCase().includes(q) || (a.matricula||'').toLowerCase().includes(q))
    },
    toggleAlumno(a) { const i = this.selectedAlumnos.indexOf(a.id); i>=0 ? this.selectedAlumnos.splice(i,1) : this.selectedAlumnos.push(a.id) },
    async asignarAlumnosSeleccionados() {
      this.savingAlumnos = true
      try { const {data}=await api.post('/grupos/'+this.alumnosGrupo.id+'/alumnos',{alumno_ids:this.selectedAlumnos}); this.alumnosModalInstance.hide(); Swal.fire({toast:true,position:'top-end',icon:'success',title:data.data?.asignados+' asignados',showConfirmButton:false,timer:2000}) } catch(e) { Swal.fire('Error',e.response?.data?.message||'Error','error') }
      this.savingAlumnos = false
    },
    async removerAlumnoDelGrupo(a) { try { await api.delete('/grupos/'+this.alumnosGrupo.id+'/alumnos/'+a.id); this.alumnosGrupoList=this.alumnosGrupoList.filter(x=>x.id!==a.id); this.alumnosAll.push(a) } catch(e){} },
    goToAlumno(id) { if(this.alumnosModalInstance)this.alumnosModalInstance.hide(); this.$router.push('/alumnos/'+id) },
    alumnoColor(a) { const c=['#A91E22','#1E40AF','#059669','#D97706','#7C3AED'];let h=0;const n=(a.usuario?.nombre_completo||a.user?.nombre_completo||a.nombre||a.nombre_completo||'');for(let i=0;i<n.length;i++)h=n.charCodeAt(i)+((h<<5)-h);return c[Math.abs(h)%c.length] },
    timeToMinutes(t) {
      if (!t) return 0; return t.substring(0, 5).split(':').reduce((h, m) => +h * 60 + +m, 0)
    },

    // --- schedule mobile swipe ---
    setScheduleDay(idx) {
      this.scheduleActiveDayIndex = idx
    },
    onScheduleSwipeStart(e) {
      this.touchStartX = e.touches[0].clientX
    },
    onScheduleSwipeEnd(e) {
      if (this.touchStartX === null) return
      const diff = this.touchStartX - e.changedTouches[0].clientX
      if (Math.abs(diff) > 50) {
        if (diff > 0 && this.scheduleActiveDayIndex < this.diasSemana.length - 1) {
          this.scheduleActiveDayIndex++
        } else if (diff < 0 && this.scheduleActiveDayIndex > 0) {
          this.scheduleActiveDayIndex--
        }
      }
      this.touchStartX = null
    },

    async openTimetableModal(item) {
      this.timetableGrupo = item
      this.timetableLoading = true
      this.timetableHorarios = []
      try {
        const { data } = await api.get('/horarios', { params: { grupo_id: item.id, per_page: 50, _t: Date.now() } })
        const raw = data.data || data || []
        this.timetableHorarios = raw.filter(h => h.hora_inicio && h.hora_fin && h.dia)
        console.log('Timetable loaded:', this.timetableHorarios.length, 'items. Days:', this.timetableHorarios.map(h => h.dia))
      } catch (e) { this.timetableHorarios = [] }
      this.timetableLoading = false
      if (!this.timetableModalInstance) this.timetableModalInstance = new Modal(this.$refs.timetableModal)
      this.timetableModalInstance.show()
    },
    getSlotsForDay(dia) {
      return this.timetableHorarios.filter(h => {
        if (!h.dia || !h.hora_inicio || !h.hora_fin) return false
        return String(h.dia).toLowerCase() === String(dia).toLowerCase()
      })
    },
    timeOverlapsSlot(h, slot) {
      const hStart = this.timeToMinutes(h.hora_inicio)
      const hEnd = this.timeToMinutes(h.hora_fin)
      return hStart < slot.end && hEnd > slot.start
    },
    slotTop(h, slot) {
      const hStart = this.timeToMinutes(h.hora_inicio)
      const blockStart = slot.start
      const blockDuration = slot.end - slot.start
      return ((Math.max(hStart, blockStart) - blockStart) / blockDuration) * 100 + '%'
    },
    slotHeight(h, slot) {
      const hStart = this.timeToMinutes(h.hora_inicio)
      const hEnd = this.timeToMinutes(h.hora_fin)
      const blockStart = slot.start
      const blockEnd = slot.end
      const blockDuration = blockEnd - blockStart
      const visibleStart = Math.max(hStart, blockStart)
      const visibleEnd = Math.min(hEnd, blockEnd)
      return ((visibleEnd - visibleStart) / blockDuration) * 100 + '%'
    },
    slotColor(id) {
      const colors = ['#A91E22','#1E40AF','#059669','#D97706','#7C3AED','#0D9488','#DC2626','#B99A53','#4F46E5','#0891B2']
      return colors[(id || 0) % colors.length]
    },
    getMateriaName(id) {
      const m = this.materias.find(x => String(x.id) === String(id))
      return m ? m.nombre : 'Materia ' + id
    },
    async refreshTimetable() {
      if (!this.timetableGrupo?.id) return
      try {
        const { data } = await api.get('/horarios', { params: { grupo_id: this.timetableGrupo.id, per_page: 50 } })
        this.timetableHorarios = data.data || data || []
      } catch (e) { this.timetableHorarios = [] }
    },
    editScheduleItem(h) {
      if (this.timetableModalInstance) this.timetableModalInstance.hide()
      this.$nextTick(() => { this.loadScheduleData(this.timetableGrupo) })
    },
    addScheduleForSlot(dia, slot) {
      const hasClass = this.getSlotsForDay(dia).some(h => this.timeOverlapsSlot(h, slot))
      if (hasClass) return
      if (this.timetableModalInstance) this.timetableModalInstance.hide()
      this.$nextTick(() => { this.loadScheduleData(this.timetableGrupo) })
    },
    editScheduleByDay(dia) {
      if (this.timetableModalInstance) this.timetableModalInstance.hide()
      this.$nextTick(() => { this.loadScheduleData(this.timetableGrupo) })
    },
  },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

*,
*::before,
*::after {
  box-sizing: border-box;
}

/* ============================================================
   BASE / MOBILE-FIRST
   ============================================================ */
.page-container {
  font-family: 'Inter', sans-serif;
  color: var(--color-text);
  padding: 1rem;
}

/* ============================================================
   HEADER
   ============================================================ */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  gap: 0.75rem;
}
.page-header__left {
  display: flex;
  align-items: center;
  gap: 0.625rem;
}
.page-header__icon {
  font-size: 1.25rem;
  color: var(--color-primary);
}
.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-primary);
  margin: 0;
  letter-spacing: -0.02em;
}

/* ============================================================
   FILTER TOGGLE (mobile)
   ============================================================ */
.filter-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.75rem 1rem;
  min-height: 48px;
  background: var(--color-bg-secondary);
  border: 1.5px solid var(--color-border);
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text);
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
  margin-bottom: 0.5rem;
}
.filter-toggle:active {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(169, 30, 34, 0.1);
}
.filter-toggle i:first-child {
  color: var(--color-primary);
  font-size: 0.9375rem;
}
.filter-toggle-arrow {
  margin-left: auto;
  font-size: 0.75rem;
  color: var(--color-text-muted);
  transition: transform 0.25s ease;
}
.filter-count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 11px;
  background: var(--color-primary);
  color: #fff;
  font-size: 0.6875rem;
  font-weight: 700;
  line-height: 1;
}

/* ============================================================
   FILTER BAR (collapsible on mobile)
   ============================================================ */
.filter-bar {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transition: max-height 0.35s ease, opacity 0.25s ease, margin-bottom 0.35s ease;
  margin-bottom: 0;
}
.filter-bar--open {
  max-height: 400px;
  opacity: 1;
  margin-bottom: 0.75rem;
}
.filter-group--search {
  width: 100%;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

/* ============================================================
   CARD
   ============================================================ */
.card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border: 1px solid var(--glass-border);
  border-radius: 12px;
  box-shadow: var(--glass-shadow);
  overflow: hidden;
}
.card-body {
  padding: 1rem;
}

/* ============================================================
   INPUT / FORM
   ============================================================ */
.input-group {
  position: relative;
  display: flex;
  align-items: center;
}
.input-group__icon {
  position: absolute;
  left: 0.75rem;
  color: var(--color-text-muted);
  font-size: 0.875rem;
  pointer-events: none;
  z-index: 1;
}
.input-group .form-control {
  padding-left: 2.25rem;
}
.form-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text);
  text-transform: uppercase;
  letter-spacing: 0.025em;
}
.form-control {
  display: block;
  width: 100%;
  padding: 0.625rem 0.75rem;
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem;
  color: var(--color-text);
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  outline: none;
  line-height: 1.5;
  min-height: 44px;
}
.form-control:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--color-primary-light);
}
.form-control::placeholder { color: #9CA3AF; }
select.form-control {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%233E3E3E' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding-right: 2.25rem;
  cursor: pointer;
}
.required { color: var(--color-primary); }

/* ============================================================
   BUTTONS
   ============================================================ */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  min-height: 44px;
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 8px;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.15s ease;
  line-height: 1.5;
  white-space: nowrap;
}
.btn:disabled { opacity: 0.65; cursor: not-allowed; }
.btn__icon { font-size: 0.8125rem; }
.btn--primary {
  background-color: var(--color-primary);
  color: #ffffff;
  border-color: var(--color-primary);
}
.btn--primary:hover:not(:disabled) {
  background-color: #8E1A1D;
  border-color: #8E1A1D;
}
.btn--secondary {
  background-color: var(--color-bg-tertiary);
  color: var(--color-text);
  border-color: var(--color-border);
}
.btn--secondary:hover:not(:disabled) {
  background-color: var(--color-bg-elevated);
  border-color: var(--color-border-hover);
}

/* ============================================================
   TABLE ACTIONS (touch targets 48px min)
   ============================================================ */
.table-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: flex-end;
  flex-wrap: wrap;
}
.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  min-width: 44px;
  min-height: 44px;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  background: var(--color-bg-tertiary);
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.15s ease;
  -webkit-tap-highlight-color: transparent;
}
.btn-icon:hover {
  border-color: var(--color-primary);
  color: var(--color-text-inverse);
  background: var(--color-primary);
}
.btn-icon--delete:hover {
  border-color: #DC2626;
  background: #DC2626;
}
.btn-icon--schedule { color: var(--color-text-secondary); }
.btn-icon--schedule:hover { color: var(--color-primary); background: var(--color-primary-light); }
.btn-icon--students { color: var(--color-text-secondary); }
.btn-icon--students:hover { color: #7C3AED; background: #F5F3FF; }

/* ============================================================
   BADGE
   ============================================================ */
.badge {
  display: inline-block;
  padding: 0.2rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 9999px;
  line-height: 1.4;
}
.badge--success {
  background-color: var(--color-success-light);
  color: var(--color-success);
}
.badge--secondary {
  background-color: var(--color-bg-tertiary);
  color: var(--color-text-secondary);
}

/* ============================================================
   FORM GRID (modals)
   ============================================================ */
.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}
.form-grid.cols-2 { grid-template-columns: 1fr; }
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}
.form-field { display: flex; flex-direction: column; gap: 0.25rem; }
.form-field label { font-size: 0.8125rem; font-weight: 500; }
.form-field .req { color: var(--color-danger); }
.field-input {
  padding: 0.625rem 0.75rem;
  border: 1.5px solid var(--color-border);
  border-radius: 10px;
  font-size: 0.875rem;
  font-family: var(--font-family);
  color: var(--color-text);
  background: var(--color-bg);
  outline: none;
  width: 100%;
  min-height: 44px;
}
.field-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(169,30,34,0.08); }
.field-input:disabled { background: var(--color-bg-tertiary); opacity: 0.7; cursor: not-allowed; }
select.field-input {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.6rem center;
  padding-right: 2rem;
}
.time-selects { display: flex; gap: 0.5rem; }
.time-selects select { flex: 1; }

/* ============================================================
   MODAL BASE OVERRIDES
   ============================================================ */
:deep() .modal-content {
  border: 1px solid var(--color-border);
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}
:deep() .modal-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border);
}
:deep() .modal-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-primary);
}
:deep() .modal-body { padding: 1.25rem; }
:deep() .modal-footer {
  padding: 0.875rem 1.25rem;
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  flex-wrap: wrap;
}
:deep() .modal-footer > * { margin: 0; }

.modal-icon {
  width: 42px; height: 42px;
  border-radius: 12px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: #fff;
}
.icon-create { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); }
.icon-edit { background: linear-gradient(135deg, #D97706, #F59E0B); }
.modal-subtitle { font-size: 0.8125rem; color: var(--color-text-muted); margin: 2px 0 0; }

/* ============================================================
   TABLE
   ============================================================ */
:deep() .table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}
:deep() .table thead {
  background-color: var(--color-bg-tertiary);
}
:deep() .table thead th {
  padding: 0.75rem 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  text-align: left;
  border-bottom: 2px solid var(--color-border);
}
:deep() .table tbody td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--color-border);
  color: var(--color-text);
}
:deep() .table tbody tr:hover {
  background-color: var(--color-primary-light);
}
:deep() .table tbody tr:last-child td {
  border-bottom: none;
}

/* ============================================================
   SCHEDULE GRID (desktop only)
   ============================================================ */
.cal-wrapper { overflow-x: auto; }
.cal-grid {
  display: grid;
  grid-template-columns: 60px repeat(5, 1fr);
  gap: 1px;
  background: var(--color-border-light);
  border-radius: 12px;
  overflow: hidden;
  min-width: 600px;
}
.cal-head {
  padding: 0.5rem 0.25rem;
  text-align: center;
  font-weight: 700;
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--color-text-muted);
  background: var(--color-bg-tertiary);
  position: sticky;
  top: 0;
  z-index: 1;
}
.cal-time {
  padding: 0.25rem 0.5rem;
  font-size: 0.6875rem;
  color: var(--color-text-muted);
  background: var(--color-bg);
  text-align: right;
  font-family: var(--font-family-mono);
}
.cal-cell {
  background: var(--color-bg);
  min-height: 72px;
  padding: 2px;
  cursor: pointer;
  transition: background 0.1s;
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.cal-cell:hover { background: var(--color-primary-light); }
.cal-block {
  border-radius: 5px;
  padding: 3px 5px;
  color: #fff;
  font-size: 0.625rem;
  cursor: pointer;
  transition: filter 0.15s;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.cal-block:hover { filter: brightness(1.15); }
.cal-block-subject { font-weight: 700; line-height: 1.2; }
.cal-block-teacher { font-size: 0.5625rem; opacity: 0.85; }

/* ============================================================
   SCHEDULE MOBILE DAY LIST (<768px)
   ============================================================ */
.schedule-mobile {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Day tabs (horizontally scrollable) */
.sch-day-tabs {
  display: flex;
  gap: 0.25rem;
  overflow-x: auto;
  padding-bottom: 0.25rem;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}
.sch-day-tabs::-webkit-scrollbar { display: none; }

.sch-day-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  min-width: 56px;
  min-height: 48px;
  padding: 0.5rem 0.75rem;
  border: 1.5px solid var(--color-border);
  border-radius: 10px;
  background: var(--color-bg-secondary);
  color: var(--color-text-secondary);
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
  -webkit-tap-highlight-color: transparent;
}
.sch-day-tab--active {
  border-color: var(--color-primary);
  background: var(--color-primary-light);
  color: var(--color-primary);
}
.sch-day-count {
  font-size: 0.625rem;
  font-weight: 500;
  opacity: 0.7;
}

/* Day header with nav arrows */
.sch-day-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 0.5rem 0;
}
.sch-day-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text);
  min-width: 60px;
  text-align: center;
}
.sch-nav-btn {
  width: 40px;
  height: 40px;
  min-width: 40px;
  min-height: 40px;
  border-radius: 50%;
  border: 1.5px solid var(--color-border);
  background: var(--color-bg-secondary);
  color: var(--color-text-secondary);
  font-size: 0.8125rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
  -webkit-tap-highlight-color: transparent;
}
.sch-nav-btn:active { border-color: var(--color-primary); color: var(--color-primary); }
.sch-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; }

/* Swipe area */
.sch-day-list {
  flex: 1;
  min-height: 120px;
  touch-action: pan-y;
}

/* Empty state */
.sch-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 2rem 1rem;
  color: var(--color-text-muted);
  font-size: 0.875rem;
}
.sch-empty i { font-size: 1.5rem; opacity: 0.5; }

/* Schedule cards for mobile */
.sch-cards {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.sch-card {
  display: flex;
  border-radius: 10px;
  border: 1px solid var(--color-border-light);
  border-left: 4px solid;
  background: var(--color-bg-secondary);
  overflow: hidden;
  cursor: pointer;
  transition: background 0.15s ease, transform 0.1s ease;
  -webkit-tap-highlight-color: transparent;
}
.sch-card:active { transform: scale(0.985); }
.sch-card-bar {
  width: 4px;
  flex-shrink: 0;
}
.sch-card-body {
  padding: 0.75rem 0.875rem;
  flex: 1;
  min-width: 0;
}
.sch-card-subject {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-text);
  margin-bottom: 0.375rem;
  line-height: 1.3;
}
.sch-card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.625rem;
  font-size: 0.75rem;
  color: var(--color-text-muted);
}
.sch-card-meta span {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}
.sch-card-meta i { font-size: 0.6875rem; }

/* Add button */
.sch-add-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  min-height: 48px;
  padding: 0.75rem;
  border: 1.5px dashed var(--color-border);
  border-radius: 10px;
  background: transparent;
  color: var(--color-primary);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  -webkit-tap-highlight-color: transparent;
}
.sch-add-btn:active {
  border-color: var(--color-primary);
  background: var(--color-primary-light);
}
.sch-add-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Legacy schedule styles kept for compatibility */
.schedule-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; }
.sch-row-num { width: 28px; height: 28px; border-radius: 50%; background: var(--color-bg-secondary); display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; flex-shrink: 0; color: var(--color-text-muted); }
.sch-row-fields { display: flex; align-items: center; gap: .375rem; flex: 1; flex-wrap: wrap; }
.sch-row-fields select, .sch-row-fields input { min-width: 90px; padding: .4375rem .625rem; border: 1.5px solid var(--color-border); border-radius: 8px; font-size: .8125rem; outline: none; font-family: var(--font-family); color: var(--color-text); background: var(--color-bg); }
.sch-row-fields select:focus, .sch-row-fields input:focus { border-color: var(--color-primary); }
.sch-row-fields select:disabled, .sch-row-fields input:disabled { background: var(--color-bg-tertiary); opacity: .7; cursor: not-allowed; }
.schedule-timeline { padding: 0 .5rem; }
.timeline-label { margin-bottom: 4px; }
.timeline-bar { position: relative; height: 28px; background: var(--color-bg-secondary); border-radius: 14px; overflow: hidden; }
.timeline-segment { position: absolute; top: 0; bottom: 0; border-radius: 14px; opacity: .85; }
.timeline-ticks { display: flex; justify-content: space-between; font-size: .6875rem; color: var(--color-text-muted); margin-top: 4px; }

.timetable-grid {
  display: grid; grid-template-columns: 65px repeat(5, 1fr);
  font-size: .75rem;
}
.tt-header {
  padding: .5rem .25rem; text-align: center; font-weight: 700;
  background: var(--color-bg-tertiary); border-bottom: 1px solid var(--color-border);
  font-size: .6875rem; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text-muted);
  position: sticky; top: 0; z-index: 1;
}
.tt-time {
  padding: .25rem .5rem; text-align: right; font-size: .6875rem; color: var(--color-text-muted);
  border-right: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light);
  font-family: var(--font-family-mono);
}
.tt-cell {
  position: relative; border-right: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light);
  min-height: 28px; cursor: pointer; transition: background .1s;
}
.tt-cell:hover { background: rgba(169,30,34,0.03); }
.tt-class {
  position: absolute; left: 1px; right: 1px; border-radius: 4px; padding: 2px 4px;
  overflow: hidden; z-index: 0; cursor: pointer; transition: filter .15s;
}
.tt-class:hover { filter: brightness(1.15); }
.tt-subject { font-size: .625rem; font-weight: 700; color: #fff; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tt-teacher { font-size: .5625rem; color: rgba(255,255,255,.8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ============================================================
   ALUMNOS MODAL
   ============================================================ */
.alumnos-modal-body {
  max-height: 60vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1rem !important;
}
.alumnos-section {
  margin-bottom: 0.25rem;
}

/* Sticky search */
.alumnos-search-sticky {
  position: sticky;
  top: 0;
  z-index: 2;
  background: var(--color-bg-glass, var(--color-bg));
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  padding: 0.5rem 0;
  margin: -0.5rem 0 0.25rem;
}
.alumnos-search-input {
  min-height: 48px;
  font-size: 0.9375rem;
}

/* Student cards */
.alumno-cards {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}
.alumno-mini {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.625rem 0.75rem;
  min-height: 52px;
  border: 1px solid var(--color-border-light);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s;
  -webkit-tap-highlight-color: transparent;
}
.alumno-mini:hover { background: var(--color-bg-tertiary); }
.alumno-mini:active { transform: scale(0.99); }
.alumno-mini.selected {
  background: var(--color-primary-light);
  border-color: var(--color-primary);
}
.am-check { width: 24px; font-size: 1.125rem; color: var(--color-text-muted); flex-shrink: 0; }
.alumno-mini.selected .am-check { color: var(--color-primary); }
.am-avatar {
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 0.8125rem;
  flex-shrink: 0;
}
.am-photo {
  width: 36px; height: 36px;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
}
.am-info { flex: 1; min-width: 0; }
.am-info strong {
  display: block;
  font-size: 0.8125rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.am-info small { font-size: 0.6875rem; color: var(--color-text-muted); }
.section-label {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--color-text-muted);
  margin: 0 0 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--color-border-light);
}

.alumnos-modal-footer {
  flex-shrink: 0;
}

/* ============================================================
   VISIBILITY HELPERS
   ============================================================ */
.desktop-only { display: none; }
.mobile-only { display: block; }

/* ============================================================
   RESPONSIVE: >= 640px (tablet portrait)
   ============================================================ */
@media (min-width: 640px) {
  .page-container { padding: 1.25rem; }
  .page-title { font-size: 1.375rem; }
  .card-body { padding: 1.25rem; }
  .form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
  .filter-bar--open { max-height: 400px; }
  .alumnos-modal-body { max-height: 65vh; }
}

/* ============================================================
   RESPONSIVE: >= 768px (tablet landscape / desktop)
   ============================================================ */
@media (min-width: 768px) {
  .page-container { padding: 1.5rem; }
  .page-title { font-size: 1.5rem; }
  .page-header { margin-bottom: 1.5rem; }
  .card-body { padding: 1.5rem; }

  /* Hide filter toggle, show filter bar as row */
  .filter-toggle { display: none; }

  .filter-bar {
    flex-direction: row;
    align-items: flex-end;
    flex-wrap: wrap;
    max-height: none;
    opacity: 1;
    overflow: visible;
    margin-bottom: 1.5rem;
    gap: 1rem;
  }
  .filter-bar--open {
    max-height: none;
    margin-bottom: 1.5rem;
  }
  .filter-group--search {
    flex: 1;
    min-width: 220px;
    max-width: 380px;
  }

  /* Schedule: show desktop grid, hide mobile */
  .desktop-only { display: block; }
  .mobile-only { display: none; }

  .form-grid { grid-template-columns: 1fr 1fr; }

  /* Alumnos modal */
  .alumnos-modal-body { max-height: 60vh; }

  /* Bigger touch targets for desktop too */
  .btn-icon {
    width: 44px;
    height: 44px;
  }
}

/* ============================================================
   RESPONSIVE: >= 992px
   ============================================================ */
@media (min-width: 992px) {
  .btn-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
  }
}
</style>
