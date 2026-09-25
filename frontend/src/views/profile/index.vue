<template>
  <div class="profile">
    <h2>个人资料</h2>

    <div class="card">
      <h3>基本信息</h3>
      <form @submit.prevent="handleUpdateProfile">
        <div class="form-group">
          <label>昵称</label>
          <input v-model="nickname" type="text" placeholder="昵称" />
        </div>
        <button type="submit" class="btn" :disabled="saving">保存</button>
      </form>
      <div v-if="msg" :class="['msg', msgType]">{{ msg }}</div>
    </div>

    <div class="card">
      <h3>修改密码</h3>
      <form @submit.prevent="handleChangePassword">
        <div class="form-group">
          <label>原密码</label>
          <input v-model="oldPassword" type="password" />
        </div>
        <div class="form-group">
          <label>新密码</label>
          <input v-model="newPassword" type="password" placeholder="至少6位" />
        </div>
        <button type="submit" class="btn" :disabled="savingPwd">修改密码</button>
      </form>
      <div v-if="pwdMsg" :class="['msg', pwdMsgType]">{{ pwdMsg }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { updateProfile, changePassword } from '../../api/user'

const auth = useAuthStore()

const nickname = ref('')
const oldPassword = ref('')
const newPassword = ref('')
const saving = ref(false)
const savingPwd = ref(false)
const msg = ref('')
const msgType = ref('')
const pwdMsg = ref('')
const pwdMsgType = ref('')

onMounted(async () => {
  await auth.fetchProfile()
  nickname.value = auth.user?.nickname || ''
})

async function handleUpdateProfile() {
  saving.value = true
  msg.value = ''
  try {
    await updateProfile({ nickname: nickname.value })
    showMsg('保存成功', 'success')
    await auth.fetchProfile()
  } catch (e) {
    showMsg(e.message || '保存失败', 'error')
  } finally {
    saving.value = false
  }
}

async function handleChangePassword() {
  if (!oldPassword.value) return showPwdMsg('请输入原密码', 'error')
  if (!newPassword.value || newPassword.value.length < 6) return showPwdMsg('新密码至少6位', 'error')

  savingPwd.value = true
  pwdMsg.value = ''
  try {
    await changePassword({ old_password: oldPassword.value, new_password: newPassword.value })
    oldPassword.value = ''
    newPassword.value = ''
    showPwdMsg('密码已修改，请重新登录', 'success')
  } catch (e) {
    showPwdMsg(e.message || '修改失败', 'error')
  } finally {
    savingPwd.value = false
  }
}

function showMsg(text, type) {
  msg.value = text
  msgType.value = type
}
function showPwdMsg(text, type) {
  pwdMsg.value = text
  pwdMsgType.value = type
}
</script>

<style scoped>
.profile h2 {
  font-size: 20px;
  margin-bottom: 20px;
  color: #333;
}
.card {
  background: #fff;
  padding: 24px;
  border-radius: 8px;
  margin-bottom: 20px;
}
.card h3 {
  font-size: 16px;
  margin-bottom: 16px;
  color: #333;
}
.form-group {
  margin-bottom: 14px;
}
.form-group label {
  display: block;
  font-size: 13px;
  color: #666;
  margin-bottom: 6px;
}
.form-group input {
  width: 100%;
  max-width: 360px;
  padding: 8px 12px;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  font-size: 14px;
  outline: none;
}
.btn {
  padding: 8px 20px;
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
  margin-top: 12px;
  font-size: 13px;
}
.msg.success {
  color: #52c41a;
}
.msg.error {
  color: #ff4d4f;
}
</style>
