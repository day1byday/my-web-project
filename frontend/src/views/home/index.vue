<template>
  <div class="home">
    <div class="welcome-card">
      <h1>欢迎，{{ auth.user?.nickname || auth.user?.username }}</h1>
      <p>你已成功登录企业级项目，这是一个基于 ThinkPHP 6 + Vue 3 的认证鉴权演示系统。</p>
    </div>

    <div class="info-grid">
      <div class="info-card">
        <h3>账号信息</h3>
        <ul>
          <li><span>用户名：</span>{{ auth.user?.username }}</li>
          <li><span>邮箱：</span>{{ auth.user?.email || '-' }}</li>
          <li><span>手机号：</span>{{ auth.user?.mobile || '-' }}</li>
        </ul>
      </div>
      <div class="info-card">
        <h3>角色权限</h3>
        <ul>
          <li v-for="role in auth.roles" :key="role">
            <span class="badge">{{ role }}</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="tech-card">
      <h3>已实现的企业级技术点</h3>
      <ul class="tech-list">
        <li>✅ JWT 双 Token（Access + Refresh）+ 无感刷新</li>
        <li>✅ 邮箱/手机验证码注册</li>
        <li>✅ RBAC 权限模型（用户 → 角色 → 权限）</li>
        <li>✅ 接口限流（防暴力破解/撞库）</li>
        <li>✅ 密码 bcrypt 哈希 + 登录失败锁定</li>
        <li>✅ Refresh Token 轮换 + 重用检测</li>
        <li>✅ 统一 JSON 响应 + 全局异常处理</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()

onMounted(async () => {
  // 刷新个人信息
  try {
    await auth.fetchProfile()
  } catch {
    // 忽略
  }
})
</script>

<style scoped>
.welcome-card {
  background: linear-gradient(135deg, #1890ff, #096dd9);
  color: #fff;
  padding: 32px;
  border-radius: 8px;
  margin-bottom: 20px;
}
.welcome-card h1 {
  font-size: 22px;
  margin-bottom: 10px;
}
.welcome-card p {
  font-size: 14px;
  opacity: 0.9;
}
.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}
.info-card {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
}
.info-card h3 {
  font-size: 16px;
  margin-bottom: 14px;
  color: #333;
}
.info-card ul {
  list-style: none;
}
.info-card li {
  padding: 6px 0;
  font-size: 14px;
  color: #666;
}
.info-card li span {
  color: #999;
}
.badge {
  display: inline-block;
  padding: 3px 12px;
  background: #e6f7ff;
  color: #1890ff;
  border-radius: 12px;
  font-size: 13px;
}
.tech-card {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
}
.tech-card h3 {
  font-size: 16px;
  margin-bottom: 14px;
  color: #333;
}
.tech-list {
  list-style: none;
}
.tech-list li {
  padding: 8px 0;
  font-size: 14px;
  color: #555;
  border-bottom: 1px solid #f5f5f5;
}
.tech-list li:last-child {
  border-bottom: none;
}
</style>
