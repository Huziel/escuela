import api from '@/api/axios'

const AuthService = {
  login(credentials) {
    return api.post('/login', credentials)
  },

  logout() {
    return api.post('/logout')
  },

  logoutAll() {
    return api.post('/logout-all')
  },

  fetchUser() {
    return api.get('/user')
  },

  refreshToken() {
    return api.post('/refresh-token')
  },

  changePassword(data) {
    return api.post('/change-password', data)
  },

  forgotPassword(email) {
    return api.post('/forgot-password', { email })
  },

  resetPassword(data) {
    return api.post('/reset-password', data)
  },
}

export default AuthService
