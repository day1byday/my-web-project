import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/login/index.vue'),
    meta: { title: '登录', guestOnly: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/register/index.vue'),
    meta: { title: '注册', guestOnly: true },
  },
  {
    path: '/forgot',
    name: 'Forgot',
    component: () => import('../views/forgot/index.vue'),
    meta: { title: '重置密码', guestOnly: true },
  },
  {
    path: '/',
    component: () => import('../layouts/DefaultLayout.vue'),
    children: [
      {
        path: '',
        name: 'Home',
        component: () => import('../views/home/index.vue'),
        meta: { title: '首页', requiresAuth: true },
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('../views/profile/index.vue'),
        meta: { title: '个人资料', requiresAuth: true },
      },
      {
        path: 'admin/users',
        name: 'AdminUsers',
        component: () => import('../views/admin/users.vue'),
        meta: { title: '用户管理', requiresAuth: true, requiresRole: 'admin' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory('/app/'),
  routes,
})

// 标记是否已尝试过 bootstrap
let bootstrapped = false

router.beforeEach(async (to) => {
  document.title = (to.meta.title || '系统') + ' - 企业级项目'

  const auth = useAuthStore()

  // 已登录用户访问登录/注册页 → 跳首页
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'Home' }
  }

  // 需要登录的页面
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    // 首次进入时尝试用 refresh cookie 恢复登录态
    if (!bootstrapped) {
      bootstrapped = true
      const ok = await auth.bootstrap()
      if (ok && auth.isAuthenticated) {
        // 恢复成功，继续角色检查
      } else {
        return { name: 'Login', query: { redirect: to.fullPath } }
      }
    } else {
      return { name: 'Login', query: { redirect: to.fullPath } }
    }
  }

  // 角色检查
  if (to.meta.requiresRole && !auth.hasRole(to.meta.requiresRole)) {
    return { name: 'Home' }
  }

  return true
})

export default router
