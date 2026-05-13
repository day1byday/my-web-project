<template>
  <div class="user-list-container">
    <h1>{{ title }}</h1>

    <div class="nav">
      <router-link to="/">登录页面</router-link>
      <a href="#" @click.prevent="fetchDbTest">Db 查询测试</a>
      <a href="#" @click.prevent="fetchUserProfile">用户详情(API)</a>
      <button v-if="auth.isLoggedIn" class="btn-logout" @click="handleLogout">
        退出登录
      </button>
    </div>

    <!-- 加载状态 -->
    <div v-if="loading" class="loading-state">加载数据中...</div>

    <!-- 用户表格 -->
    <table v-if="!loading">
      <tr>
        <th>ID</th>
        <th>用户名</th>
        <th>邮箱</th>
        <th>状态</th>
        <th>最后登录</th>
        <th>创建时间</th>
      </tr>
      <tr v-for="item in users" :key="item.id">
        <td>{{ item.id }}</td>
        <td>{{ item.username }}</td>
        <td>{{ item.email || '-' }}</td>
        <td>
          <span :class="['badge', item.status == 1 ? 'badge-on' : 'badge-off']">
            {{ item.status == 1 ? '正常' : '禁用' }}
          </span>
        </td>
        <td>{{ formatDate(item.last_login_time) }}</td>
        <td>{{ formatDate(item.create_time) }}</td>
      </tr>
      <tr v-if="users.length === 0">
        <td colspan="6" class="empty-row">暂无用户数据</td>
      </tr>
    </table>

    <!-- 调试面板 -->
    <div v-if="debugData" class="api-preview">
      <strong>{{ debugTitle }}</strong>
      <pre>{{ debugData }}</pre>
    </div>

    <!-- 提示信息 -->
    <div class="note">
      <strong>TP5 vs TP6 关键差异速查 ↓</strong><br>
      <b>视图路径:</b> TP5: application/index/view/  →  TP6: view/<br>
      <b>变量输出:</b> TP5模板: {$变量名}  →  TP6 PHP引擎: &lt;?= $变量名 ?&gt;<br>
      <b>循环:</b> TP5: {volist name="list" id="vo"}  →  TP6: &lt;?php foreach ($list as $vo): ?&gt;<br>
      <b>URL生成:</b> TP5: {:url('login/loginPage')}  →  TP6: &lt;?= url('/login/loginPage') ?&gt;<br>
      <b>前端重构:</b> 本页已使用 Vue 3 SPA 模式重写，通过 API 接口获取数据<br>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getUserList, getUserProfile, dbTest } from '../api'
import { auth } from '../stores/auth'

const router = useRouter()

const title = ref('用户管理 - TP6 + Vue3 示例')
const users = ref([])
const loading = ref(true)
const debugData = ref('')
const debugTitle = ref('')

// 格式化日期
function formatDate(dateStr) {
  if (!dateStr || dateStr === '0000-00-00 00:00:00') return '-'
  return dateStr
}

// 退出登录
function handleLogout() {
  auth.logout()
  router.push('/')
}

// 获取用户列表
async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await getUserList()
    if (data.code === 0) {
      users.value = data.data
    }
  } catch (error) {
    console.error('获取用户列表失败:', error)
  } finally {
    loading.value = false
  }
}

// Db 查询测试
async function fetchDbTest() {
  try {
    const { data } = await dbTest()
    debugTitle.value = 'Db 查询测试结果:'
    debugData.value = JSON.stringify(data, null, 2)
  } catch (error) {
    debugData.value = `请求失败: ${error.message}`
  }
}

// 用户详情 API 测试
async function fetchUserProfile() {
  try {
    const { data } = await getUserProfile(1)
    debugTitle.value = '用户详情(API) - ID=1:'
    debugData.value = JSON.stringify(data, null, 2)
  } catch (error) {
    debugData.value = `请求失败: ${error.message}`
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.user-list-container {
  padding: 30px;
}

h1 {
  font-size: 22px;
  margin-bottom: 20px;
  color: #333;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

th {
  background: #1890ff;
  color: #fff;
  padding: 10px 14px;
  text-align: left;
  font-size: 14px;
}

td {
  padding: 10px 14px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 13px;
}

tr:hover {
  background: #e6f7ff;
}

.empty-row {
  text-align: center;
  color: #999;
  padding: 30px 14px;
}

.badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.badge-on {
  background: #f6ffed;
  color: #52c41a;
  border: 1px solid #b7eb8f;
}

.badge-off {
  background: #fff2f0;
  color: #ff4d4f;
  border: 1px solid #ffccc7;
}

.nav {
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.nav a {
  display: inline-block;
  padding: 8px 16px;
  background: #1890ff;
  color: #fff;
  text-decoration: none;
  border-radius: 4px;
  font-size: 13px;
}

.nav a:hover {
  background: #40a9ff;
}

.btn-logout {
  padding: 8px 16px;
  background: #ff4d4f;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 13px;
  cursor: pointer;
  margin-left: auto;
}

.btn-logout:hover {
  background: #ff7875;
}

.loading-state {
  text-align: center;
  padding: 60px 0;
  font-size: 14px;
  color: #999;
}

.note {
  margin-top: 20px;
  padding: 12px 16px;
  background: #fffbe6;
  border: 1px solid #ffe58f;
  border-radius: 6px;
  font-size: 13px;
  color: #ad8b00;
  line-height: 1.8;
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
</style>
