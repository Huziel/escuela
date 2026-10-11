export function formatDate(dateString, format = 'short') {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return dateString

  const options = {
    short: { year: 'numeric', month: '2-digit', day: '2-digit' },
    long: { year: 'numeric', month: 'long', day: 'numeric' },
    datetime: { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' },
  }

  return date.toLocaleDateString('es-MX', options[format] || options.short)
}

export function statusBadge(status) {
  const map = {
    activo: 'bg-success',
    inactivo: 'bg-secondary',
    egresado: 'bg-info',
    baja: 'bg-danger',
    bloqueado: 'bg-warning',
    aprobada: 'bg-success',
    reprobada: 'bg-danger',
    cursando: 'bg-primary',
    pendiente: 'bg-warning',
    presente: 'bg-success',
    falta: 'bg-danger',
    justificada: 'bg-info',
    regular: 'bg-success',
    irregular: 'bg-warning',
  }
  return map[status] || 'bg-secondary'
}

export function truncateText(text, maxLength = 50) {
  if (!text) return ''
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

export function getInitials(name) {
  if (!name) return '?'
  return name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

export function avatarColor(name) {
  const colors = ['#C62828', '#E53935', '#EF5350', '#FF8A80', '#B71C1C', '#FF5252', '#FFCDD2', '#D32F2F']
  let hash = 0
  for (let i = 0; i < (name || '').length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
}

export function debounce(fn, delay = 300) {
  let timer
  return function (...args) {
    clearTimeout(timer)
    timer = setTimeout(() => fn.apply(this, args), delay)
  }
}
