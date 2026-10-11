<template>
  <router-view />
</template>

<script>
import { particlePresets, cursorPresets, getSavedParticlePreset, getSavedCursorPreset, getPrimaryRGB } from '@/themes/presets'

export default {
  name: 'App',
  mounted() {
    const bgCanvas = document.getElementById('particles-canvas')
    const crCanvas = document.getElementById('cursor-canvas')
    if (!bgCanvas || !crCanvas) return
    const bgCtx = bgCanvas.getContext('2d')
    const crCtx = crCanvas.getContext('2d')
    let w, h, particles = [], trail = [], mouse = { x: -1000, y: -1000 }
    let config = { color: getPrimaryRGB(), preset: null, cursorPreset: null }
    let time = 0

    function applyPreset(name) {
      config.preset = particlePresets[name] || particlePresets.dots
      particles = []
      for (let i = 0; i < config.preset.count; i++) {
        particles.push({
          x: Math.random() * w,
          y: Math.random() * h,
          vx: (Math.random() - 0.5) * config.preset.speed,
          vy: (Math.random() - 0.5) * config.preset.speed,
          r: Math.random() * (config.preset.sizeMax - config.preset.sizeMin) + config.preset.sizeMin,
          o: Math.random() * 0.5 + 0.2,
          phase: Math.random() * Math.PI * 2,
        })
      }
    }

    function applyCursor(name) {
      config.cursorPreset = cursorPresets[name] || cursorPresets.glow
    }

    function resize() {
      w = bgCanvas.width = crCanvas.width = window.innerWidth
      h = bgCanvas.height = crCanvas.height = window.innerHeight
    }
    resize()
    window.addEventListener('resize', resize)

    applyPreset(getSavedParticlePreset())
    applyCursor(getSavedCursorPreset())

    document.addEventListener('mousemove', e => {
      mouse.x = e.clientX; mouse.y = e.clientY
      const cp = config.cursorPreset
      if (cp.trail > 0) {
        trail.push({ x: e.clientX, y: e.clientY, age: 0 })
        if (trail.length > cp.trail) trail.shift()
      }
    })

    document.addEventListener('theme-particles-changed', e => {
      applyPreset(e.detail)
    })
    document.addEventListener('theme-cursor-changed', e => {
      applyCursor(e.detail)
    })
    document.addEventListener('theme-colors-changed', () => {
      config.color = getPrimaryRGB()
    })

    function drawShape(ctx, x, y, r, shape) {
      ctx.beginPath()
      if (shape === 'square') {
        ctx.rect(x - r, y - r, r * 2, r * 2)
      } else if (shape === 'diamond') {
        ctx.moveTo(x, y - r)
        ctx.lineTo(x + r, y)
        ctx.lineTo(x, y + r)
        ctx.lineTo(x - r, y)
        ctx.closePath()
      } else {
        ctx.arc(x, y, r, 0, Math.PI * 2)
      }
    }

    function drawBg(timestamp) {
      time = timestamp
      bgCtx.clearRect(0, 0, w, h)
      const c = config.color
      const pre = config.preset
      for (let i = 0; i < particles.length; i++) {
        const p = particles[i]
        if (pre.wave) {
          const amp = pre.waveAmp || 0.3
          const freq = pre.waveFreq || 0.002
          p.x += Math.sin(p.phase + time * freq) * amp * 0.6
          p.y += Math.cos(p.phase + time * freq * 1.3) * amp * 0.4
        }
        p.x += p.vx; p.y += p.vy
        if (p.x < 0) p.x = w; if (p.x > w) p.x = 0
        if (p.y < 0) p.y = h; if (p.y > h) p.y = 0

        drawShape(bgCtx, p.x, p.y, p.r, pre.shape)
        bgCtx.fillStyle = `rgba(${c},${p.o})`; bgCtx.fill()

        if (pre.connectToMouse) {
          const dx = mouse.x - p.x, dy = mouse.y - p.y
          const dist = Math.sqrt(dx * dx + dy * dy)
          if (dist < 180) {
            bgCtx.beginPath(); bgCtx.moveTo(p.x, p.y); bgCtx.lineTo(mouse.x, mouse.y)
            bgCtx.strokeStyle = `rgba(${c},${0.3 * (1 - dist / 180)})`
            bgCtx.lineWidth = 0.6; bgCtx.stroke()
          }
        }
        for (let j = i + 1; j < particles.length; j++) {
          const q = particles[j]
          const d = Math.hypot(p.x - q.x, p.y - q.y)
          if (d < pre.linkDistance) {
            bgCtx.beginPath(); bgCtx.moveTo(p.x, p.y); bgCtx.lineTo(q.x, q.y)
            bgCtx.strokeStyle = `rgba(${c},${0.12 * (1 - d / pre.linkDistance)})`
            bgCtx.lineWidth = 0.5; bgCtx.stroke()
          }
        }
      }
      requestAnimationFrame(drawBg)
    }

    function drawCursor() {
      crCtx.clearRect(0, 0, w, h)
      const c = config.color
      const cp = config.cursorPreset

      for (let i = 0; i < trail.length; i++) {
        const t = trail[i]; t.age++
        const a = Math.max(0, 1 - t.age / cp.trail)
        crCtx.beginPath(); crCtx.arc(t.x, t.y, cp.trailSize * a, 0, Math.PI * 2)
        crCtx.fillStyle = `rgba(${c},${a * 0.5})`; crCtx.fill()
        crCtx.beginPath(); crCtx.arc(t.x, t.y, cp.trailSize * a * 1.8, 0, Math.PI * 2)
        crCtx.fillStyle = `rgba(${c},${a * 0.08})`; crCtx.fill()
      }
      if (mouse.x > 0) {
        if (cp.crosshair) {
          crCtx.strokeStyle = `rgba(${c},0.8)`; crCtx.lineWidth = 1.5
          crCtx.beginPath(); crCtx.moveTo(mouse.x - cp.size, mouse.y); crCtx.lineTo(mouse.x + cp.size, mouse.y); crCtx.stroke()
          crCtx.beginPath(); crCtx.moveTo(mouse.x, mouse.y - cp.size); crCtx.lineTo(mouse.x, mouse.y + cp.size); crCtx.stroke()
          crCtx.beginPath(); crCtx.arc(mouse.x, mouse.y, cp.size * 0.6, 0, Math.PI * 2)
          crCtx.strokeStyle = `rgba(${c},0.5)`; crCtx.stroke()
        } else if (cp.diamond) {
          const ds = cp.size + 4
          crCtx.save()
          crCtx.translate(mouse.x, mouse.y)
          crCtx.beginPath()
          crCtx.moveTo(0, -ds)
          crCtx.lineTo(ds, 0)
          crCtx.lineTo(0, ds)
          crCtx.lineTo(-ds, 0)
          crCtx.closePath()
          crCtx.strokeStyle = `rgba(${c},0.8)`
          crCtx.lineWidth = 1.5
          crCtx.stroke()
          crCtx.restore()
          crCtx.beginPath()
          crCtx.arc(mouse.x, mouse.y, cp.size * 0.4, 0, Math.PI * 2)
          crCtx.fillStyle = `rgba(${c},0.95)`
          crCtx.fill()
          for (const g of cp.glow) {
            crCtx.beginPath()
            crCtx.arc(mouse.x, mouse.y, g, 0, Math.PI * 2)
            crCtx.fillStyle = `rgba(${c},${0.15 / (g / 12)})`
            crCtx.fill()
          }
        } else {
          if (cp.ring) {
            crCtx.beginPath(); crCtx.arc(mouse.x, mouse.y, cp.size + 4, 0, Math.PI * 2)
            crCtx.strokeStyle = `rgba(${c},0.8)`; crCtx.lineWidth = 1.5; crCtx.stroke()
          }
          crCtx.beginPath(); crCtx.arc(mouse.x, mouse.y, cp.size, 0, Math.PI * 2)
          crCtx.fillStyle = `rgba(${c},0.95)`; crCtx.fill()
          for (const g of cp.glow) {
            crCtx.beginPath(); crCtx.arc(mouse.x, mouse.y, g, 0, Math.PI * 2)
            crCtx.fillStyle = `rgba(${c},${0.15 / (g / 12)})`; crCtx.fill()
          }
        }
      }
      requestAnimationFrame(drawCursor)
    }

    drawBg(0)
    drawCursor()
  }
}
</script>

<style>
#particles-canvas {
  position: fixed; top: 0; left: 0;
  width: 100%; height: 100%;
  z-index: -1; pointer-events: none;
  background: var(--color-bg);
}
#cursor-canvas {
  position: fixed; top: 0; left: 0;
  width: 100%; height: 100%;
  z-index: 10000; pointer-events: none;
}
* { cursor: none; }
.keep-cursor, .keep-cursor *, a, button, input, select, textarea, [role="button"] { cursor: auto; }
</style>
