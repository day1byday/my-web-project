import { defineStore } from 'pinia'
import { login as apiLogin, register as apiRegister, sendCode as apiSendCode, refresh as apiRefresh, logout as apiLogout } from '../api/auth'
import { getProfile } from '../api/user'

const USER_KEY = 'tp6_user_profile'

// 非敏感用户信息持久化到 localStorage（仅用于页面刷新后快速渲染）
function loadCachedUser() {
  try {
    return JSON.parse(localStorage.getItem(USER_KEY) || 'null')
  } catch {
    return null
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    // Access Token 只存内存（不落 localStorage，降低 XSS 风险）
    accessToken: '',
    user: loadCachedUser(),
    roles: [],
  }),

  getters: {
    isAuthenticated: (state) => !!state.accessToken,
    hasRole: (state) => (role) => state.roles.includes(role),
  },

  actions: {
    // 应用启动时用 httpOnly cookie 里的 refresh token 换 access token
    async bootstrap() {
      try {
        const data = await apiRefresh()
        this.setSession(data)
        return true
      } catch {
        return false
      }
    },

    async login(identifier, password) {
      const data = await apiLogin(identifier, password)
      this.setSession(data)
      return data
    },

    async register(payload) {
      return apiRegister(payload)
    },

    async sendCode(payload) {
      return apiSendCode(payload)
    },

    async refresh() {
      const data = await apiRefresh()
      this.setSession(data)
      return data
    },

    async logout() {
      try {
        await apiLogout()
      } finally {
        this.clearSession()
      }
    },

    async fetchProfile() {
      const data = await getProfile()
      this.user = data
      this.roles = data.roles || []
      localStorage.setItem(USER_KEY, JSON.stringify(data))
      return data
    },

    setSession(data) {
      this.accessToken = data.access_token
      if (data.user) {
        this.user = data.user
        this.roles = data.user.roles || []
        localStorage.setItem(USER_KEY, JSON.stringify(data.user))
      }
    },

    setAccessToken(token) {
      this.accessToken = token
    },

    clearSession() {
      this.accessToken = ''
      this.user = null
      this.roles = []
      localStorage.removeItem(USER_KEY)
    },

    forceLogout() {
      this.clearSession()
    },
  },
})
