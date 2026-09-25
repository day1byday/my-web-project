<template>
  <div class="forgot-container">
    <div class="card">
      <h1>重置密码</h1>
      <form @submit.prevent="handleReset">
        <div class="form-group">
          <label>邮箱 / 手机号</label>
          <input v-model="target" type="text" placeholder="请输入注册时的邮箱或手机号" />
        </div>
        <div class="form-group code-row">
          <label>验证码</label>
          <div class="code-input">
            <input v-model="code" type="text" placeholder="6位验证码" maxlength="6" />
            <button type="button" class="btn-code" :disabled="countdown > 0" @click="handleSendCode">
              {{ countdown > 0 ? countdown + 's' : '发送验证码' }}
            </button>
          </div>
        </div>
        <div class="form-group">
          <label>新密码</label>
          <input v-model="newPassword" type="password" placeholder="至少6位" />
        </div>
        <button type="submit" class="btn" :disabled="loading">{{ loading ? '提交中...' : '重置密码' }}</button>
      </form>
      <div v-if="msg" :class="['msg', msgType]">{{ msg }}</div>
      <div class="note">
        想起密码了？<router-link to="/login">返回登录</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { forgotPassword, resetPassword } from '../../api/auth'

const router = useRouter()

const target = ref('')
const code = ref('')
const newPassword = ref('')
const loading = ref(false)
const countdown = ref(0)
const msg = ref('')
const msgType = ref('')

// 自动判断渠道：含 @ 为邮箱，否则手机号
const channel = computed(() => (target.value.includes('@') ? 'email' : 'mobile'))

async function handleSendCode() {
  if (!target.value.trim()) return showMsg('请输入邮箱或手机号', 'error')

  try {
    await forgotPassword({ channel: channel.value, target: target.value.trim() })
    showMsg('验证码已发送', 'success')
    startCountdown()
  } catch (e) {
    showMsg(e.message || '发送失败', 'error')
  }
}

function startCountdown() {
  countdown.value = 60
  const timer = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) clearInterval(timer)
  }, 1000)
}

async function handleReset() {
  if (loading.value) return
  if (!target.value.trim()) return showMsg('请输入邮箱或手机号', 'error')
  if (!code.value) return showMsg('请输入验证码', 'error')
  if (!newPassword.value || newPassword.value.length < 6) return showMsg('新密码至少6位', 'error')

  loading.value = true
  msg.value = ''
  try {
    await resetPassword({
      channel: channel.value,
      target: target.value.trim(),
      code: code.value,
      new_password: newPassword.value,
    })
    showMsg('密码已重置，即将跳转登录', 'success')
    setTimeout(() => router.push({ name: 'Login' }), 800)
  } catch (e) {
    showMsg(e.message || '重置失败', 'error')
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
.forgot-container {
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
  width: 400px;
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
.code-row .code-input {
  display: flex;
  gap: 10px;
}
.code-row input {
  flex: 1;
}
.btn-code {
  padding: 0 14px;
  border: 1px solid #1890ff;
  color: #1890ff;
  background: #fff;
  border-radius: 4px;
  font-size: 13px;
  cursor: pointer;
  white-space: nowrap;
}
.btn-code:disabled {
  color: #999;
  border-color: #d9d9d9;
  cursor: not-allowed;
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
