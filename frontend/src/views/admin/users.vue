<template>
  <div class="admin-users">
    <h2>用户管理</h2>

    <div class="toolbar">
      <input v-model="keyword" type="text" placeholder="搜索用户名/邮箱/昵称" @keyup.enter="fetchUsers" />
      <button class="btn" @click="fetchUsers">搜索</button>
    </div>

    <table class="table" v-if="!loading">
      <thead>
        <tr>
          <th>ID</th>
          <th>用户名</th>
          <th>邮箱</th>
          <th>手机号</th>
          <th>角色</th>
          <th>状态</th>
          <th>最后登录</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="u in users" :key="u.id">
          <td>{{ u.id }}</td>
          <td>{{ u.username }}</td>
          <td>{{ u.email || '-' }}</td>
          <td>{{ u.mobile || '-' }}</td>
          <td>
            <span v-for="r in u.roles" :key="r" class="badge">{{ r }}</span>
          </td>
          <td>
            <span :class="['badge', u.status == 1 ? 'badge-on' : 'badge-off']">
              {{ u.status == 1 ? '正常' : '禁用' }}
            </span>
          </td>
          <td>{{ u.last_login_time || '-' }}</td>
          <td>
            <button class="btn-sm" @click="toggleStatus(u)">
              {{ u.status == 1 ? '禁用' : '启用' }}
            </button>
          </td>
        </tr>
        <tr v-if="users.length === 0">
          <td colspan="8" class="empty">暂无用户</td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="loading">加载中...</div>

    <div class="pagination" v-if="total > size">
      <button :disabled="page <= 1" @click="goPage(page - 1)">上一页</button>
      <span>第 {{ page }} / {{ pages }} 页（共 {{ total }} 条）</span>
      <button :disabled="page >= pages" @click="goPage(page + 1)">下一页</button>
    </div>

    <div v-if="msg" :class="['msg', msgType]">{{ msg }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getUserList, setUserStatus } from '../../api/admin'

const users = ref([])
const loading = ref(true)
const keyword = ref('')
const page = ref(1)
const size = ref(10)
const total = ref(0)
const pages = ref(1)
const msg = ref('')
const msgType = ref('')

async function fetchUsers() {
  loading.value = true
  try {
    const data = await getUserList({ page: page.value, size: size.value, keyword: keyword.value })
    users.value = data.list || []
    total.value = data.total || 0
    pages.value = data.pages || 1
  } catch (e) {
    showMsg(e.message || '加载失败', 'error')
  } finally {
    loading.value = false
  }
}

function goPage(p) {
  page.value = p
  fetchUsers()
}

async function toggleStatus(u) {
  const target = u.status == 1 ? 0 : 1
  try {
    await setUserStatus(u.id, target)
    showMsg('操作成功', 'success')
    fetchUsers()
  } catch (e) {
    showMsg(e.message || '操作失败', 'error')
  }
}

function showMsg(text, type) {
  msg.value = text
  msgType.value = type
}

onMounted(fetchUsers)
</script>

<style scoped>
.admin-users h2 {
  font-size: 20px;
  margin-bottom: 20px;
  color: #333;
}
.toolbar {
  display: flex;
  gap: 10px;
  margin-bottom: 16px;
}
.toolbar input {
  padding: 8px 12px;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  font-size: 14px;
  width: 260px;
  outline: none;
}
.btn {
  padding: 8px 16px;
  background: #1890ff;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}
.table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}
th {
  background: #fafafa;
  color: #333;
  padding: 10px 12px;
  text-align: left;
  font-size: 13px;
}
td {
  padding: 10px 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 13px;
  color: #555;
}
.badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  margin-right: 4px;
  background: #e6f7ff;
  color: #1890ff;
}
.badge-on {
  background: #f6ffed;
  color: #52c41a;
}
.badge-off {
  background: #fff2f0;
  color: #ff4d4f;
}
.btn-sm {
  padding: 4px 10px;
  font-size: 12px;
  border: 1px solid #1890ff;
  color: #1890ff;
  background: #fff;
  border-radius: 4px;
  cursor: pointer;
}
.empty {
  text-align: center;
  color: #999;
  padding: 30px;
}
.loading {
  text-align: center;
  padding: 40px;
  color: #999;
}
.pagination {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 16px;
  font-size: 13px;
  color: #666;
}
.pagination button {
  padding: 6px 14px;
  border: 1px solid #d9d9d9;
  background: #fff;
  border-radius: 4px;
  cursor: pointer;
}
.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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
