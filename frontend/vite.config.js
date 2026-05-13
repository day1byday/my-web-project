import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
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
    // 生产构建输出到 TP6 的 public/static/ 目录
    outDir: '../public/static',
    assetsDir: 'assets',
    emptyOutDir: true
  }
})
