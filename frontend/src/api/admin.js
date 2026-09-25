import service from './request'

// 用户列表
export function getUserList(params = {}) {
  return service.get('/admin/users', { params })
}

// 用户详情
export function getUserDetail(id) {
  return service.get(`/admin/users/${id}`)
}

// 启用/禁用用户
export function setUserStatus(id, status) {
  return service.put(`/admin/users/${id}/status`, new URLSearchParams({ status }))
}

// 分配用户角色
export function setUserRoles(id, roleIds) {
  const params = new URLSearchParams()
  roleIds.forEach((rid) => params.append('role_ids[]', rid))
  return service.put(`/admin/users/${id}/roles`, params)
}

// 角色列表
export function getRoles() {
  return service.get('/admin/roles')
}

// 权限列表
export function getPermissions() {
  return service.get('/admin/permissions')
}
