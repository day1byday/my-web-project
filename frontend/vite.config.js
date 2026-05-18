import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  base: '/app/',
  plugins: [vue()],
  server: {
    port: 3000,
    proxy: {
      // 将所有 /login API 请求转发到 TP6 后端
      '/login': {
        target: 'http://localhost:8001',
        changeOrigin: true
      }
    }
  },
  build: {
    // 方案C: Vue SPA 输出到 public/app/，URL 基路径 /app/
    outDir: '../public/app',
    assetsDir: 'assets',
    emptyOutDir: true
  }
})
