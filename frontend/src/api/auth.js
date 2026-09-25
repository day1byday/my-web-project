import service from './request'

// 登录
export function login(identifier, password) {
  return service.post('/auth/login', new URLSearchParams({ identifier, password }))
}

// 注册
export function register(payload) {
  return service.post('/auth/register', new URLSearchParams(payload))
}

// 发送验证码
export function sendCode(payload) {
  return service.post('/auth/send-code', new URLSearchParams(payload))
}

// 刷新令牌（httpOnly cookie 自动携带 refresh token）
export function refresh() {
  return service.post('/auth/refresh')
}

// 登出
export function logout() {
  return service.post('/auth/logout')
}

// 忘记密码：发送重置验证码
export function forgotPassword(payload) {
  return service.post('/auth/forgot-password', new URLSearchParams(payload))
}

// 重置密码
export function resetPassword(payload) {
  return service.post('/auth/reset-password', new URLSearchParams(payload))
}
