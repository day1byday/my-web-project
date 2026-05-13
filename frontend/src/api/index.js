import axios from 'axios'

// Axios 实例 - 开发时走 Vite 代理，生产时同域
const api = axios.create({
  baseURL: '',
  timeout: 10000,
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
})

/**
 * 登录
 * @param {string} username
 * @param {string} password
 */
export function login(username, password) {
  const formData = new URLSearchParams()
  formData.append('username', username)
  formData.append('password', password)
  return api.post('/login/doLogin', formData)
}

/**
 * 获取用户列表
 */
export function getUserList() {
  return api.get('/login/userList')
}

/**
 * 获取用户详情
 * @param {number} id
 */
export function getUserProfile(id) {
  return api.get(`/login/profile/${id}`)
}

/**
 * 数据库测试
 */
export function dbTest() {
  return api.get('/login/dbTest')
}
