import service from './request'

// 获取个人信息
export function getProfile() {
  return service.get('/user/profile')
}

// 更新个人信息
export function updateProfile(payload) {
  return service.put('/user/profile', new URLSearchParams(payload))
}

// 修改密码
export function changePassword(payload) {
  return service.put('/user/password', new URLSearchParams(payload))
}

// 绑定邮箱
export function bindEmail(payload) {
  return service.put('/user/email', new URLSearchParams(payload))
}

// 绑定手机号
export function bindMobile(payload) {
  return service.put('/user/mobile', new URLSearchParams(payload))
}
