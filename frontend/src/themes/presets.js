export const particlePresets = {
  dots: {
    name: 'Puntos',
    count: 70,
    speed: 0.6,
    sizeMin: 0.5,
    sizeMax: 3,
    linkDistance: 120,
    connectToMouse: true,
  },
  stars: {
    name: 'Estrellas',
    count: 120,
    speed: 0.3,
    sizeMin: 0.3,
    sizeMax: 2,
    linkDistance: 140,
    connectToMouse: true,
  },
  geometric: {
    name: 'Geométrico',
    count: 50,
    speed: 0.4,
    sizeMin: 1,
    sizeMax: 4,
    linkDistance: 180,
    connectToMouse: true,
    shape: 'square',
  },
  flow: {
    name: 'Flujo',
    count: 90,
    speed: 0.8,
    sizeMin: 0.4,
    sizeMax: 2.5,
    linkDistance: 100,
    connectToMouse: false,
  },
  mist: {
    name: 'Neblina',
    count: 40,
    speed: 0.2,
    sizeMin: 2,
    sizeMax: 6,
    linkDistance: 200,
    connectToMouse: true,
  },

  /* ═══ FF Crystal Presets ═══ */
  crystals: {
    name: 'Cristales',
    count: 55,
    speed: 0.35,
    sizeMin: 1.2,
    sizeMax: 4.5,
    linkDistance: 140,
    connectToMouse: true,
    shape: 'diamond',
  },
  'mako-flow': {
    name: 'Flujo Mako',
    count: 75,
    speed: 0.5,
    sizeMin: 0.6,
    sizeMax: 2.8,
    linkDistance: 100,
    connectToMouse: false,
    wave: true,
    waveAmp: 0.35,
    waveFreq: 0.002,
  },
}

export const cursorPresets = {
  glow: {
    name: 'Brillo',
    size: 5,
    glow: [12, 22],
    trail: 30,
    trailSize: 4,
  },
  minimal: {
    name: 'Minimal',
    size: 3,
    glow: [8],
    trail: 15,
    trailSize: 2,
  },
  ring: {
    name: 'Anillo',
    size: 3,
    glow: [10],
    trail: 20,
    trailSize: 3,
    ring: true,
  },
  crosshair: {
    name: 'Precisión',
    size: 8,
    glow: [],
    trail: 0,
    trailSize: 0,
    crosshair: true,
  },

  /* ═══ FF Crystal Cursor ═══ */
  crystal: {
    name: 'Cristal',
    size: 5,
    glow: [14, 26],
    trail: 25,
    trailSize: 4,
    diamond: true,
  },
}

export function getSavedParticlePreset() {
  return localStorage.getItem('sice-particles') || 'dots'
}

export function getSavedCursorPreset() {
  return localStorage.getItem('sice-cursor') || 'glow'
}

export function saveParticlePreset(name) {
  localStorage.setItem('sice-particles', name)
}

export function saveCursorPreset(name) {
  localStorage.setItem('sice-cursor', name)
}

export function getPrimaryRGB() {
  const style = getComputedStyle(document.documentElement)
  const rgb = style.getPropertyValue('--color-primary-rgb').trim()
  if (rgb) return rgb
  const hex = style.getPropertyValue('--color-primary').trim()
  if (hex.startsWith('#')) {
    const r = parseInt(hex.slice(1, 3), 16)
    const g = parseInt(hex.slice(3, 5), 16)
    const b = parseInt(hex.slice(5, 7), 16)
    return `${r}, ${g}, ${b}`
  }
  return '198, 40, 40'
}
