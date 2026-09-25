import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import router from '../router'

// Axios 实例
const service = axios.create({
  baseURL: '/api/v1',
  timeout: 15000,
  withCredentials: true, // 携带 httpOnly refresh token cookie
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
})

// 单飞刷新锁：并发 401 时只触发一次 refresh
let refreshPromise = null

// 请求拦截器：附加 access token
service.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.accessToken) {
    config.headers.Authorization = `Bearer ${auth.accessToken}`
  }
  return config
})

// 响应拦截器：统一处理 token 续期 + 无感刷新 + 错误
service.interceptors.response.use(
  (response) => {
    const auth = useAuthStore()

    // 后端主动续期：更新 access token
    const newToken = response.headers['x-access-token']
    if (newToken) {
      auth.setAccessToken(newToken)
    }

    const body = response.data
    if (body.code === 0) {
      return body.data
    }

    return Promise.reject(body)
  },
  async (error) => {
    const auth = useAuthStore()
    const { response, config } = error

    // 网络错误 / 无响应
    if (!response) {
      return Promise.reject({ code: -1, message: '网络错误，请稍后重试' })
    }

    const body = response.data

    // access token 过期 → 无感刷新后重放
    if (body && body.code === 40101 && !config._retried) {
      config._retried = true

      // 单飞锁
      if (!refreshPromise) {
        refreshPromise = auth
          .refresh()
          .then(() => {
            refreshPromise = null
          })
          .catch((e) => {
            refreshPromise = null
            auth.forceLogout()
            router.push({ name: 'Login', query: { redirect: router.currentRoute.value.fullPath } })
            throw e
          })
      }

      try {
        await refreshPromise
        return service(config) // 用新 token 重放
      } catch (e) {
        return Promise.reject(e)
      }
    }

    return Promise.reject(body || { code: -1, message: '请求失败' })
  },
)

export default service
