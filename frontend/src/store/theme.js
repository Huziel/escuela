import { defineStore } from 'pinia'
import { themes, fonts, applyTheme, applyFont, getSavedTheme, getSavedFont } from '@/themes/themes'

export const useThemeStore = defineStore('theme', {
  state: () => ({
    activeTheme: getSavedTheme(),
    activeFont: getSavedFont(),
    customPrimary: null,
    customBg: null,
  }),
  getters: {
    currentTheme: (state) => themes[state.activeTheme] || themes['crimson-glass'],
    themeList: () => Object.entries(themes).map(([key, t]) => ({ key, ...t })),
    fontList: () => fonts,
    isDark: (state) => (themes[state.activeTheme]?.type) === 'dark',
  },
  actions: {
    setTheme(name) {
      this.activeTheme = name
      applyTheme(name)
    },
    setFont(family) {
      this.activeFont = family
      applyFont(family)
    },
    applySaved() {
      applyTheme(this.activeTheme)
      applyFont(this.activeFont)
    },
    setCustomColor(key, value) {
      document.documentElement.style.setProperty(key, value)
      if (key === '--color-primary') {
        this.customPrimary = value
        if (value) {
          const rgb = value.match(/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i)
          if (rgb) document.documentElement.style.setProperty('--color-primary-rgb', `${parseInt(rgb[1], 16)}, ${parseInt(rgb[2], 16)}, ${parseInt(rgb[3], 16)}`)
        }
      }
      if (key === '--color-bg') this.customBg = value
    },
    resetCustom() {
      this.customPrimary = null
      this.customBg = null
      this.applySaved()
    },
  },
})
