export function required(value) {
  if (value === null || value === undefined || value === '') return 'Este campo es requerido.'
  return ''
}

export function email(value) {
  if (!value) return ''
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(value) ? '' : 'El formato del correo no es válido.'
}

export function minLength(min) {
  return (value) => {
    if (!value) return ''
    return value.length >= min ? '' : `Debe tener al menos ${min} caracteres.`
  }
}

export function maxLength(max) {
  return (value) => {
    if (!value) return ''
    return value.length <= max ? '' : `No debe exceder ${max} caracteres.`
  }
}

export function numeric(value) {
  if (!value) return ''
  return !isNaN(value) ? '' : 'Debe ser un valor numérico.'
}

export function validateForm(values, rules) {
  const errors = {}
  for (const [field, validators] of Object.entries(rules)) {
    for (const validator of validators) {
      const error = typeof validator === 'function' ? validator(values[field]) : validator
      if (error) {
        errors[field] = error
        break
      }
    }
  }
  return errors
}
