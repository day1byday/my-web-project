<template>
  <div class="login-container">
    <div class="card">
      <h1>用户登录系统</h1>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>用户名</label>
          <input
            v-model="username"
            type="text"
            placeholder="请输入用户名"
            autocomplete="off"
            @keypress.enter="handleLogin"
          >
        </div>
        <div class="form-group">
          <label>密码</label>
          <input
            v-model="password"
            type="password"
            placeholder="请输入密码"
            @keypress.enter="handleLogin"
          >
        </div>
        <button type="submit" class="btn" :disabled="isSubmitting">
          {{ isSubmitting ? '登录中...' : '登 录' }}
        </button>
      </form>

      <div v-if="resultMsg" :class="['result', resultType]">
        {{ resultIcon }} {{ resultMsg }}
      </div>

      <div v-if="isSubmitting" class="loading show">
        正在验证身份，请稍候...
      </div>

      <div v-if="showApiPreview" class="api-preview">
        <strong>接口原始返回:</strong>
        <pre>{{ apiResponse }}</pre>
      </div>

      <div class="note">
        <a href="#" @click.prevent="goToUsers">返回用户列表</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '../api'
import { auth } from '../stores/auth'

const router = useRouter()

// 表单数据 - 预填测试账号
const username = ref('admin')
const password = ref('password')

// 状态
const isSubmitting = ref(false)
const resultMsg = ref('')
const resultType = ref('')
const apiResponse = ref('')

// 计算属性
const showApiPreview = computed(() => apiResponse.value !== '')
const resultIcon = computed(() => resultType.value === 'success' ? '✅' : '❌')

// 显示消息
function showError(msg) {
  resultType.value = 'error'
  resultMsg.value = msg
}

function showSuccess(msg) {
  resultType.value = 'success'
  resultMsg.value = msg
}

function clearResult() {
  resultMsg.value = ''
  resultType.value = ''
}

// 跳转到用户列表
function goToUsers() {
  router.push('/users')
}

// 处理登录
async function handleLogin() {
  if (isSubmitting.value) return

  // 前端校验
  if (!username.value.trim()) {
    showError('用户名不能为空')
    return
  }
  if (!password.value) {
    showError('密码不能为空')
    return
  }

  clearResult()
  apiResponse.value = ''
  isSubmitting.value = true

  try {
    const { data: responseData } = await login(username.value.trim(), password.value)

    // 显示原始响应
    apiResponse.value = JSON.stringify(responseData, null, 2)

    if (responseData.code === 0) {
      // 保存登录状态
      auth.login(responseData.data)
      showSuccess(responseData.msg || '登录成功！即将跳转到用户列表...')

      setTimeout(() => {
        goToUsers()
      }, 300)
    } else {
      showError(responseData.msg || '登录失败，请检查用户名或密码')
    }
  } catch (error) {
    console.error('登录请求异常:', error)
    let friendlyMsg = '网络请求失败，请检查网络连接或联系管理员'
    if (error.message) {
      friendlyMsg = error.message.includes('Network Error')
        ? '网络连接异常，请确认后端服务已启动'
        : error.message
    }
    showError(friendlyMsg)
    apiResponse.value = `请求错误: ${error.message}`
  } finally {
    isSubmitting.value = false
  }
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
  padding: 30px 40px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  width: 380px;
}

.card h1 {
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
  margin-bottom: 4px;
}

.form-group input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
}

.form-group input:focus {
  border-color: #1890ff;
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.btn {
  width: 100%;
  padding: 9px;
  background: #1890ff;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn:hover {
  background: #40a9ff;
}

.btn:active {
  background: #096dd9;
}

.btn:disabled {
  background: #91caff;
  cursor: not-allowed;
}

.result {
  margin-top: 16px;
  padding: 10px;
  border-radius: 4px;
  font-size: 13px;
}

.result.success {
  background: #f6ffed;
  color: #52c41a;
  border: 1px solid #b7eb8f;
}

.result.error {
  background: #fff2f0;
  color: #ff4d4f;
  border: 1px solid #ffccc7;
}

.loading {
  text-align: center;
  margin-top: 12px;
  font-size: 12px;
  color: #1890ff;
  display: none;
}

.loading.show {
  display: block;
}

.api-preview {
  background: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  font-size: 12px;
  margin-top: 16px;
  border: 1px solid #e8e8e8;
  overflow-x: auto;
}

.api-preview pre {
  margin: 0;
  font-family: monospace;
  font-size: 12px;
  white-space: pre-wrap;
  word-break: break-all;
}

.note {
  text-align: center;
  margin-top: 16px;
  font-size: 12px;
  color: #999;
}

.note a {
  color: #1890ff;
  text-decoration: none;
}

.note a:hover {
  text-decoration: underline;
}
</style>
