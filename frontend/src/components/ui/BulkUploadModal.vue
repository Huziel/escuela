<template>
  <Teleport to="body">
  <div class="modal fade" ref="modalRef" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-file-import"></i> Registro Masivo — {{ title }}
          </h5>
          <button type="button" class="btn-close" @click="hide"></button>
        </div>
        <div class="modal-body">
          <div class="bulk-steps">
            <div class="bulk-step">
              <div class="step-num">1</div>
              <div>
                <strong>Descarga la plantilla</strong>
                <p class="text-muted small">Archivo Excel con las columnas requeridas y ejemplos</p>
                <button class="btn btn-sm btn-secondary" @click="downloadTemplate">
                  <i class="fas fa-download"></i> {{ templateLabel }}
                </button>
              </div>
            </div>

            <div class="bulk-step">
              <div class="step-num">2</div>
              <div>
                <strong>Sube el archivo</strong>
                <p class="text-muted small">Formatos aceptados: .xlsx, .xls, .csv</p>
                <div class="upload-zone" @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="onDrop">
                  <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" hidden @change="onFileSelect" />
                  <i v-if="!file" class="fas fa-cloud-upload-alt fa-2x"></i>
                  <div v-if="!file">
                    <strong>Selecciona un archivo</strong>
                    <small class="text-muted">o arrastra y suelta aquí</small>
                  </div>
                  <div v-else class="file-selected">
                    <i class="fas fa-file-excel"></i>
                    <strong>{{ file.name }}</strong>
                    <small class="text-muted">{{ formatSize(file.size) }}</small>
                    <button class="btn btn-sm btn-outline-secondary mt-1" @click.stop="file=null;preview=null;errors=[]">
                      <i class="fas fa-times"></i> Quitar
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="bulk-step" v-if="file">
              <div class="step-num">3</div>
              <div class="w-100">
                <strong>Previsualizar datos</strong>
                <p class="text-muted small">Revisa que los datos sean correctos antes de importar</p>
                <button class="btn btn-sm btn-primary" @click="previewData" :disabled="previewing">
                  <i v-if="previewing" class="fas fa-circle-notch fa-spin"></i>
                  <i v-else class="fas fa-eye"></i> Previsualizar
                </button>

                <div v-if="preview && preview.rows" class="preview-table mt-2">
                  <div class="table-responsive">
                    <table class="data-table">
                      <thead>
                        <tr>
                          <th v-for="h in preview.headers" :key="h">{{ h }}</th>
                          <th>Estado</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(r,i) in preview.rows" :key="i" :class="{ 'row-error': r.error }">
                          <td v-for="h in preview.headers" :key="h">{{ r[h] ?? '-' }}</td>
                          <td>
                            <span v-if="r.error" class="badge badge-danger" :title="r.error">{{ r.error }}</span>
                            <span v-else class="badge badge-success">Válido</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="preview-summary mt-2">
                    <span class="badge badge-success">Válidos: {{ preview.valid }}</span>
                    <span v-if="preview.errors" class="badge badge-danger">Errores: {{ preview.errors }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="hide">Cancelar</button>
          <button class="btn btn-primary" @click="importData" :disabled="!canImport || importing">
            <i v-if="importing" class="fas fa-circle-notch fa-spin"></i>
            <i v-else class="fas fa-check"></i>
            Importar {{ importCount }} registros
          </button>
        </div>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script>
import api from '@/api/axios'

export default {
  name: 'BulkUploadModal',
  props: {
    title: { type: String, default: '' },
    type: { type: String, required: true },
    templateLabel: { type: String, default: 'Descargar plantilla Excel' },
  },
  emits: ['imported'],
  data() {
    return {
      modal: null,
      file: null,
      preview: null,
      previewing: false,
      importing: false,
      errors: [],
    }
  },
  computed: {
    canImport() { return this.preview && this.preview.valid > 0 },
    importCount() { return this.preview ? this.preview.valid : 0 },
  },
  mounted() {
    this.modal = this.$refs.modalRef
  },
  methods: {
    show() {
      const el = this.$refs.modalRef
      if (!el) return
      el.classList.add('show')
      el.style.display = 'block'
      el.setAttribute('aria-modal', 'true')
      document.body.classList.add('modal-open')
      document.body.style.overflow = 'hidden'
      if (!document.querySelector('.bulk-upload-backdrop')) {
        const bd = document.createElement('div')
        bd.className = 'modal-backdrop fade show bulk-upload-backdrop'
        document.body.appendChild(bd)
      }
    },
    hide() {
      const el = this.$refs.modalRef
      if (!el) return
      el.classList.remove('show')
      el.style.display = 'none'
      el.removeAttribute('aria-modal')
      document.body.classList.remove('modal-open')
      document.body.style.overflow = ''
      document.querySelectorAll('.bulk-upload-backdrop').forEach(b => b.remove())
    },
    formatSize(bytes) {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
    },
    async downloadTemplate() {
      try {
        const response = await api.get(`/import/template/${this.type}`, { responseType: 'blob' })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const a = document.createElement('a')
        a.href = url
        a.download = `plantilla_${this.type}.xlsx`
        a.click()
        window.URL.revokeObjectURL(url)
      } catch (e) {
        console.error(e)
      }
    },
    onFileSelect(e) {
      const f = e.target.files[0]
      if (f) this.setFile(f)
    },
    onDrop(e) {
      const f = e.dataTransfer.files[0]
      if (f) this.setFile(f)
    },
    setFile(f) {
      this.file = f
      this.preview = null
      this.errors = []
    },
    async previewData() {
      if (!this.file) return
      this.previewing = true
      const fd = new FormData()
      fd.append('file', this.file)
      try {
        const { data } = await api.post(`/import/preview/${this.type}`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
          timeout: 120000,
        })
        this.preview = data.data || data
      } catch (e) {
        this.errors = [e.response?.data?.message || 'Error al previsualizar']
      }
      this.previewing = false
    },
    async importData() {
      if (!this.file || !this.canImport) return
      this.importing = true
      const fd = new FormData()
      fd.append('file', this.file)
      try {
        const { data } = await api.post(`/import/process/${this.type}`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
          timeout: 300000,
        })
        const result = data.data || data
        this.file = null
        this.preview = null
        const Swal = (await import('sweetalert2')).default
        const errCount = (result.errors || []).length
        if (errCount > 0) {
          const errList = result.errors.slice(0, 10).map(e => `<tr><td>${e.row}</td><td>${e.matricula || ''}</td><td>${e.materia || ''}</td><td>${e.error}</td></tr>`).join('')
          Swal.fire({
            icon: 'warning',
            title: `${result.imported || 0} importados, ${errCount} errores`,
            html: `<div style="max-height:300px;overflow-y:auto;text-align:left;font-size:.75rem">
              <table style="width:100%"><thead><tr><th>Fila</th><th>Matrícula</th><th>Materia</th><th>Error</th></tr></thead>
              <tbody>${errList}</tbody></table>
              ${errCount > 10 ? `<p style="margin-top:.5rem;color:var(--color-text-muted)">...y ${errCount - 10} más</p>` : ''}
            </div>`,
            confirmButtonText: 'OK',
          })
        } else {
          Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: `${result.imported || 0} registros importados`,
            showConfirmButton: false, timer: 2500, timerProgressBar: true,
          })
        }
        this.hide()
        this.$emit('imported', result)
      } catch (e) {
        const Swal = (await import('sweetalert2')).default
        Swal.fire('Error', e.response?.data?.message || 'Error al importar', 'error')
      }
      this.importing = false
    },
  },
}
</script>

<style scoped>
.bulk-steps { display: flex; flex-direction: column; gap: 1.25rem; }
.bulk-step { display: flex; gap: .875rem; align-items: flex-start; }
.step-num {
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--color-primary); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: .8125rem; flex-shrink: 0;
}
.upload-zone {
  border: 2px dashed var(--color-border);
  border-radius: 12px; padding: 1.5rem;
  text-align: center; cursor: pointer; transition: border-color .2s;
  display: flex; flex-direction: column; align-items: center; gap: .5rem;
  margin-top: .5rem;
}
.upload-zone:hover { border-color: var(--color-primary); }
.upload-zone i { color: var(--color-text-muted); }
.file-selected { display: flex; flex-direction: column; align-items: center; gap: .25rem; }
.preview-table { max-height: 300px; overflow-y: auto; border: 1px solid var(--color-border); border-radius: 8px; }
.preview-table table { font-size: .75rem; }
.preview-table th { position: sticky; top: 0; z-index: 1; }
.row-error { background: var(--color-danger-light) !important; }
.preview-summary { display: flex; gap: .5rem; }
</style>
