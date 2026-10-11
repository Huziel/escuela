<template>
  <div class="theme-panel">
    <div class="tp-header">
      <i class="fas fa-palette"></i>
      <h3 class="tp-title">Temas</h3>
    </div>

    <div class="tp-section">
      <h4 class="tp-section-title">Tema Principal</h4>
      <div class="tp-grid">
        <div
          v-for="t in themeStore.themeList"
          :key="t.key"
          class="tp-card"
          :class="{ active: themeStore.activeTheme === t.key }"
          @click="setTheme(t.key)"
        >
          <div class="tp-preview" :class="t.type">
            <div class="tp-preview-bar" :style="{ background: t.colors['--color-primary'] }"></div>
            <div class="tp-preview-body">
              <div class="tp-preview-block" :style="{ background: t.colors['--color-bg-secondary'] }"></div>
              <div class="tp-preview-row">
                <div class="tp-preview-dot" :style="{ background: t.colors['--color-primary'] }"></div>
                <div class="tp-preview-line" :style="{ background: t.colors['--color-text-secondary'] }"></div>
              </div>
            </div>
          </div>
          <div class="tp-info">
            <div class="tp-name">
              <i v-if="t.icon" :class="'fas ' + t.icon" class="tp-theme-icon"></i>
              {{ t.name }}
            </div>
            <div class="tp-meta">{{ t.type === 'dark' ? '🌙' : '☀️' }} {{ t.style === 'glass' ? 'Vidrio' : 'Mate' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="tp-section">
      <h4 class="tp-section-title">Tipografía</h4>
      <div class="tp-font-grid">
        <button
          v-for="f in themeStore.fontList"
          :key="f.name"
          class="tp-font-btn"
          :class="{ active: themeStore.activeFont === f.value }"
          :style="{ fontFamily: f.value }"
          @click="themeStore.setFont(f.value)"
        >
          {{ f.name }}
        </button>
      </div>
    </div>

    <div class="tp-section">
      <h4 class="tp-section-title">Particulas de Fondo</h4>
      <div class="tp-font-grid">
        <button
          v-for="(p, key) in particlePresets"
          :key="key"
          class="tp-font-btn"
          :class="{ active: activeParticles === key }"
          @click="setParticlePreset(key)"
        >
          {{ p.name }}
        </button>
      </div>
    </div>

    <div class="tp-section">
      <h4 class="tp-section-title">Estilo del Puntero</h4>
      <div class="tp-font-grid">
        <button
          v-for="(p, key) in cursorPresets"
          :key="key"
          class="tp-font-btn"
          :class="{ active: activeCursor === key }"
          @click="setCursorPreset(key)"
        >
          {{ p.name }}
        </button>
      </div>
    </div>

    <div class="tp-section">
      <h4 class="tp-section-title">Estilo de Íconos</h4>
      <div class="tp-font-grid">
        <button
          v-for="s in iconStyles"
          :key="s.value"
          class="tp-font-btn"
          :class="{ active: activeIconStyle === s.value }"
          @click="setIconStyle(s.value)"
        >
          {{ s.name }}
        </button>
      </div>
    </div>

    <div class="tp-section" v-if="customizing">
      <h4 class="tp-section-title">Personalizar Colores</h4>
      <div class="tp-color-grid">
        <div class="tp-color-item">
          <label>Primario</label>
          <input type="color" @input="e => themeStore.setCustomColor('--color-primary', e.target.value)" />
        </div>
        <div class="tp-color-item">
          <label>Fondo</label>
          <input type="color" @input="e => themeStore.setCustomColor('--color-bg', e.target.value)" />
        </div>
      </div>
      <button class="btn btn-sm btn-outline-secondary mt-2" @click="themeStore.resetCustom(); customizing = false">
        <i class="fas fa-undo"></i> Restaurar
      </button>
    </div>
    <button v-else class="btn btn-sm btn-secondary mt-2" @click="customizing = true">
      <i class="fas fa-sliders"></i> Personalizar colores
    </button>
  </div>
</template>

<script>
import { useThemeStore } from '@/store/theme'
import { particlePresets, cursorPresets, getSavedParticlePreset, getSavedCursorPreset, saveParticlePreset, saveCursorPreset } from '@/themes/presets'
import { iconStyles, applyIconStyle, getSavedIconStyle } from '@/themes/themes'
export default {
  name: 'ThemeSwitcher',
  data() {
    return {
      customizing: false,
      activeParticles: getSavedParticlePreset(),
      activeCursor: getSavedCursorPreset(),
      activeIconStyle: getSavedIconStyle(),
      iconStyles,
      particlePresets,
      cursorPresets,
    }
  },
  computed: {
    themeStore() { return useThemeStore() },
  },
  methods: {
    setParticlePreset(name) {
      this.activeParticles = name
      saveParticlePreset(name)
      document.dispatchEvent(new CustomEvent('theme-particles-changed', { detail: name }))
    },
    setCursorPreset(name) {
      this.activeCursor = name
      saveCursorPreset(name)
      document.dispatchEvent(new CustomEvent('theme-cursor-changed', { detail: name }))
    },
    setIconStyle(name) {
      this.activeIconStyle = name
      applyIconStyle(name)
    },
    setTheme(name) {
      this.themeStore.setTheme(name)
      this.$nextTick(() => {
        document.dispatchEvent(new CustomEvent('theme-colors-changed'))
      })
    },
  },
}
</script>

<style scoped>
.theme-panel { padding: 1rem; }
.tp-header { display: flex; align-items: center; gap: .625rem; margin-bottom: 1.25rem; }
.tp-header i { color: var(--color-primary); font-size: 1.25rem; }
.tp-title { font-size: 1.125rem; font-weight: 700; margin: 0; }
.tp-section { margin-bottom: 1.25rem; }
.tp-section-title { font-size: .6875rem; text-transform: uppercase; letter-spacing: .08em; color: var(--color-text-muted); margin: 0 0 .75rem; font-weight: 600; }
.tp-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .625rem; }
.tp-card { background: var(--color-bg-tertiary); border: 1px solid var(--color-border); border-radius: 12px; padding: .625rem; cursor: pointer; transition: all .15s; }
.tp-card:hover { border-color: var(--color-primary); }
.tp-card.active { border-color: var(--color-primary); background: var(--color-primary-light); }
.tp-preview { border-radius: 8px; overflow: hidden; margin-bottom: .5rem; }
.tp-preview.light { border: 1px solid rgba(0,0,0,.1); }
.tp-preview.dark { border: 1px solid rgba(255,255,255,.06); }
.tp-preview-bar { height: 8px; }
.tp-preview-body { padding: .375rem; display: flex; flex-direction: column; gap: .25rem; }
.tp-preview.dark .tp-preview-body { background: #0D1117; }
.tp-preview.light .tp-preview-body { background: #F3F4F6; }
.tp-preview-block { width: 100%; height: 18px; border-radius: 3px; }
.tp-preview-row { display: flex; align-items: center; gap: .375rem; padding: 0 .25rem; }
.tp-preview-dot { width: 8px; height: 8px; border-radius: 50%; }
.tp-preview-line { flex: 1; height: 4px; border-radius: 2px; }
.tp-name { font-size: .6875rem; font-weight: 700; line-height: 1.2; display: flex; align-items: center; gap: .3rem; }
.tp-theme-icon { font-size: .625rem; color: var(--color-primary); opacity: 0.85; flex-shrink: 0; }
.tp-meta { font-size: .625rem; color: var(--color-text-muted); }
.tp-font-grid { display: flex; gap: .375rem; flex-wrap: wrap; }
.tp-font-btn { padding: .375rem .75rem; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-bg-tertiary); color: var(--color-text); font-size: .8125rem; cursor: pointer; transition: all .15s; }
.tp-font-btn:hover { border-color: var(--color-primary); }
.tp-font-btn.active { border-color: var(--color-primary); background: var(--color-primary-light); color: var(--color-primary); font-weight: 600; }
.tp-color-grid { display: flex; gap: 1rem; }
.tp-color-item { display: flex; flex-direction: column; gap: .25rem; font-size: .75rem; color: var(--color-text-secondary); }
.tp-color-item input[type="color"] { width: 40px; height: 32px; border: 1px solid var(--color-border); border-radius: 6px; cursor: pointer; background: none; padding: 2px; }
@media (max-width: 600px) { .tp-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
