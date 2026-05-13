import { reactive } from 'vue'

// 简单的认证状态管理（使用 localStorage 持久化）
const AUTH_KEY = 'tp6_vue3_auth'

function loadAuth() {
  try {
    const stored = localStorage.getItem(AUTH_KEY)
    return stored ? JSON.parse(stored) : { isLoggedIn: false, user: null }
  } catch {
    return { isLoggedIn: false, user: null }
  }
}

function saveAuth(state) {
  localStorage.setItem(AUTH_KEY, JSON.stringify({
    isLoggedIn: state.isLoggedIn,
    user: state.user
  }))
}

function clearAuth() {
  localStorage.removeItem(AUTH_KEY)
}

export const auth = reactive({
  ...loadAuth(),

  login(userData) {
    this.isLoggedIn = true
    this.user = userData
    saveAuth(this)
  },

  logout() {
    this.isLoggedIn = false
    this.user = null
    clearAuth()
  },

  checkAuth() {
    const saved = loadAuth()
    this.isLoggedIn = saved.isLoggedIn
    this.user = saved.user
  }
})
