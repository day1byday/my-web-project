<template>
  <div class="login-container">
    <div class="card">
      <h1>用户登录</h1>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>用户名 / 邮箱 / 手机号</label>
          <input v-model="identifier" type="text" placeholder="请输入登录账号" autocomplete="off" />
        </div>
        <div class="form-group">
          <label>密码</label>
          <input v-model="password" type="password" placeholder="请输入密码" />
        </div>
        <button type="submit" class="btn" :disabled="loading">{{ loading ? '登录中...' : '登 录' }}</button>
      </form>
      <div v-if="msg" :class="['msg', msgType]">{{ msg }}</div>
      <div class="note">
        还没有账号？<router-link to="/register">立即注册</router-link>
        <span class="divider">|</span>
        <router-link to="/forgot">忘记密码</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const identifier = ref('admin')
const password = ref('Admin@123456')
const loading = ref(false)
const msg = ref('')
const msgType = ref('')

async function handleLogin() {
  if (loading.value) return
  if (!identifier.value.trim()) return showMsg('请输入账号', 'error')
  if (!password.value) return showMsg('请输入密码', 'error')

  loading.value = true
  msg.value = ''
  try {
    await auth.login(identifier.value.trim(), password.value)
    showMsg('登录成功，正在跳转...', 'success')
    setTimeout(() => {
      const redirect = route.query.redirect || '/'
      router.push(redirect)
    }, 300)
  } catch (e) {
    showMsg(e.message || '登录失败', 'error')
  } finally {
    loading.value = false
  }
}

function showMsg(text, type) {
  msg.value = text
  msgType.value = type
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}
.card {
  background: #fff;
  padding: 32px 40px;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  width: 380px;
}
h1 {
  font-size: 20px;
  text-align: center;
  margin-bottom: 24px;
  color: #333;
}
.form-group {
  margin-bottom: 16px;
}
.form-group label {
  display: block;
  font-size: 13px;
  color: #666;
  margin-bottom: 6px;
}
.form-group input {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  font-size: 14px;
  outline: none;
}
.form-group input:focus {
  border-color: #1890ff;
}
.btn {
  width: 100%;
  padding: 10px;
  background: #1890ff;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.msg {
  margin-top: 14px;
  padding: 9px;
  border-radius: 4px;
  font-size: 13px;
  text-align: center;
}
.msg.success {
  background: #f6ffed;
  color: #52c41a;
  border: 1px solid #b7eb8f;
}
.msg.error {
  background: #fff2f0;
  color: #ff4d4f;
  border: 1px solid #ffccc7;
}
.note {
  text-align: center;
  margin-top: 16px;
  font-size: 13px;
  color: #999;
}
.note a {
  color: #1890ff;
  text-decoration: none;
}
</style>
