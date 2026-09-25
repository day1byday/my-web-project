<template>
  <div class="register-container">
    <div class="card">
      <h1>用户注册</h1>
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>用户名</label>
          <input v-model="username" type="text" placeholder="2-20位用户名" />
        </div>
        <div class="form-group">
          <label>邮箱（或手机号，二选一）</label>
          <input v-model="email" type="text" placeholder="邮箱地址" />
        </div>
        <div class="form-group">
          <label>手机号（选填）</label>
          <input v-model="mobile" type="text" placeholder="11位手机号" />
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
          <label>密码</label>
          <input v-model="password" type="password" placeholder="至少6位" />
        </div>
        <div class="form-group">
          <label>确认密码</label>
          <input v-model="password2" type="password" placeholder="再次输入密码" />
        </div>
        <button type="submit" class="btn" :disabled="loading">{{ loading ? '注册中...' : '注 册' }}</button>
      </form>
      <div v-if="msg" :class="['msg', msgType]">{{ msg }}</div>
      <div class="note">
        已有账号？<router-link to="/login">去登录</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const username = ref('')
const email = ref('')
const mobile = ref('')
const code = ref('')
const password = ref('')
const password2 = ref('')
const loading = ref(false)
const countdown = ref(0)
const msg = ref('')
const msgType = ref('')

async function handleSendCode() {
  // 至少填一个邮箱或手机号
  const channel = email.value ? 'email' : mobile.value ? 'mobile' : ''
  const target = channel === 'email' ? email.value : mobile.value

  if (!channel) return showMsg('请先填写邮箱或手机号', 'error')

  try {
    await auth.sendCode({ scene: 'register', channel, target })
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

async function handleRegister() {
  if (loading.value) return
  if (!username.value) return showMsg('请输入用户名', 'error')
  if (!email.value && !mobile.value) return showMsg('邮箱和手机号至少填一个', 'error')
  if (!code.value) return showMsg('请输入验证码', 'error')
  if (!password.value || password.value.length < 6) return showMsg('密码至少6位', 'error')
  if (password.value !== password2.value) return showMsg('两次密码不一致', 'error')

  loading.value = true
  msg.value = ''
  try {
    const payload = {
      username: username.value.trim(),
      password: password.value,
      password2: password2.value,
      code: code.value,
    }
    if (email.value) payload.email = email.value.trim()
    if (mobile.value) payload.mobile = mobile.value.trim()

    await auth.register(payload)
    showMsg('注册成功，即将跳转登录', 'success')
    setTimeout(() => router.push({ name: 'Login' }), 800)
  } catch (e) {
    showMsg(e.message || '注册失败', 'error')
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
.register-container {
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
