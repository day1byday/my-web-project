<template>
  <div class="layout">
    <header class="header">
      <div class="header-inner">
        <router-link to="/" class="brand">企业级项目</router-link>
        <nav class="nav">
          <router-link to="/">首页</router-link>
          <router-link to="/profile">个人资料</router-link>
          <router-link v-if="auth.hasRole('admin')" to="/admin/users">用户管理</router-link>
        </nav>
        <div class="user-area">
          <span v-if="auth.user" class="username">{{ auth.user.nickname || auth.user.username }}</span>
          <button class="btn-logout" @click="handleLogout">退出登录</button>
        </div>
      </div>
    </header>
    <main class="main">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'Login' })
}
</script>

<style scoped>
.layout {
  min-height: 100vh;
}
.header {
  background: #fff;
  border-bottom: 1px solid #e8e8e8;
}
.header-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 20px;
  height: 56px;
  display: flex;
  align-items: center;
  gap: 32px;
}
.brand {
  font-size: 17px;
  font-weight: 700;
  color: #1890ff;
  text-decoration: none;
}
.nav {
  display: flex;
  gap: 20px;
  flex: 1;
}
.nav a {
  color: #666;
  text-decoration: none;
  font-size: 14px;
}
.nav a.router-link-active {
  color: #1890ff;
}
.user-area {
  display: flex;
  align-items: center;
  gap: 12px;
}
.username {
  font-size: 13px;
  color: #333;
}
.btn-logout {
  padding: 5px 14px;
  border: 1px solid #ff4d4f;
  color: #ff4d4f;
  background: #fff;
  border-radius: 4px;
  font-size: 13px;
  cursor: pointer;
}
.btn-logout:hover {
  background: #fff2f0;
}
.main {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 20px;
}
</style>
