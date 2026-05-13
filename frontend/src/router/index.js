import { createRouter, createWebHistory } from 'vue-router'
import { auth } from '../stores/auth'

const routes = [
  {
    path: '/',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { title: '用户登录' }
  },
  {
    path: '/users',
    name: 'UserList',
    component: () => import('../views/UserListView.vue'),
    meta: { title: '用户管理', requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// 路由守卫：需要登录的页面重定向到登录页
router.beforeEach((to, from, next) => {
  document.title = (to.meta.title || '系统管理中心') + ' - TP6 + Vue3'

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
  } else {
    next()
  }
})

export default router
