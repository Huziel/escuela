import axios from 'axios'
import Swal from 'sweetalert2'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000,
})

let isRefreshing = false
let failedQueue = []

const processQueue = (error, token = null) => {
  failedQueue.forEach((prom) => {
    if (error) {
      prom.reject(error)
    } else {
      prom.resolve(token)
    }
  })
  failedQueue = []
}

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error),
)

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config
    const status = error.response?.status
    const msg = error.response?.data?.message || 'Error de conexión'

    if (status === 401 && !originalRequest._retry) {
      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject })
        })
          .then((token) => {
            originalRequest.headers.Authorization = `Bearer ${token}`
            return api(originalRequest)
          })
          .catch((err) => Promise.reject(err))
      }

      originalRequest._retry = true
      isRefreshing = true

      try {
        const token = localStorage.getItem('token')
        if (!token) {
          throw new Error('No token')
        }
        const { data } = await axios.post(
          `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'}/refresh-token`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          },
        )
        const newToken = data.data.token
        localStorage.setItem('token', newToken)
        api.defaults.headers.common.Authorization = `Bearer ${newToken}`
        processQueue(null, newToken)
        originalRequest.headers.Authorization = `Bearer ${newToken}`
        return api(originalRequest)
      } catch (refreshError) {
        processQueue(refreshError, null)
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        window.location.href = '/login'
        return Promise.reject(refreshError)
      } finally {
        isRefreshing = false
      }
    }

    if (status === 419) {
      Swal.fire('Sesión expirada', 'Tu sesión ha expirado. Inicia sesión nuevamente.', 'warning')
      localStorage.removeItem('token')
      window.location.href = '/login'
    } else if (status === 403) {
      Swal.fire('Acceso denegado', 'No tienes permisos para realizar esta acción.', 'error')
    } else if (status === 422) {
      const errors = error.response?.data?.errors
      if (errors) {
        const firstError = Object.values(errors).flat()[0] || 'Datos inválidos'
        Swal.fire('Validación', firstError, 'warning')
      }
    } else if (status >= 500) {
      Swal.fire('Error del servidor', msg, 'error')
    }

    return Promise.reject(error)
  },
)

export default api
