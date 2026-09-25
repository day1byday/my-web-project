# 企业级 Web 平台

基于 **ThinkPHP 6 + Vue 3** 的全栈企业级项目，通过实现多个功能模块来实践主流后端/前端开发技术。重点在于技术深度与工程化实践，而非业务内容本身。

## 技术栈

| 层次 | 技术 |
|------|------|
| 后端框架 | ThinkPHP 6.1 |
| 前端框架 | Vue 3 + Vite + Pinia + Vue Router |
| 数据库 | MySQL 8.0 |
| 缓存 | Redis 7 |
| 消息队列 | RabbitMQ 3.13 |
| 对象存储 | MinIO（S3 兼容） |
| 搜索引擎 | ElasticSearch 7.17（预留） |
| 认证方案 | JWT（Access + Refresh 双 Token） |
| 部署方式 | Docker Compose 全容器化 |

## 功能模块

### ✅ 模块一：用户系统（已完成）

企业级认证鉴权体系：

- **JWT 双 Token**：Access Token（15 分钟）+ Refresh Token（7 天）+ 无感刷新
- **Refresh Token 轮换**：SHA-256 哈希存储、重用检测（防盗用）
- **注册验证**：邮箱 / 手机验证码（6 位，10 分钟有效）
- **密码重置**：忘记密码 → 验证码校验 → 重置
- **RBAC 权限**：用户 → 角色 → 权限（渐进式，预留扩展）
- **安全防护**：bcrypt 哈希、暴力破解锁定、接口限流、安全版本号即时吊销

### ⏳ 模块二：AI 对话（待实现）

对接 DeepSeek 大模型，SSE 流式输出 + 混合计费。

### ⏳ 模块三：资源上传下载（待实现）

MinIO 对象存储 + 分片上传 + 秒传 + 防盗链。

### ⏳ 模块四：三方功能（待实现）

商城 + 内容社区 + RabbitMQ + ElasticSearch。

## 项目结构

```
├── app/
│   ├── api/                    # API 应用（多应用模式）
│   │   ├── controller/v1/      # Auth / User / admin 控制器
│   │   ├── middleware/         # JwtAuth / Cors / Throttle / Permission
│   │   └── route/app.php       # /api/v1/* 路由
│   ├── common/                 # 共享代码
│   │   ├── lib/ApiResponse.php # 统一响应体
│   │   ├── exception/          # 业务异常
│   │   ├── service/            # Auth / User / Jwt / Token / Rbac 服务层
│   │   └── validate/           # 自定义参数验证器
│   └── ExceptionHandle.php     # 全局异常 → JSON
├── db/                         # 数据访问层（Base + 各表 Repository）
├── db/sql/                     # 建表 SQL + 种子数据
├── frontend/                   # Vue3 SPA 源码
├── public/                     # Web 根目录（含构建产物 public/app/）
├── docker/                     # 容器镜像与部署配置
│   ├── php-dev.Dockerfile      #   开发环境 PHP 镜像（内置服务器）
│   ├── php.Dockerfile          #   生产环境 PHP 镜像（PHP-FPM）
│   ├── nginx.conf              #   生产环境 Nginx 配置
│   └── docker-compose.prod.yml #   生产环境编排（预留，云服务器部署用）
├── docs/                       # 项目文档
│   ├── 需求.md                 #   需求文档
│   ├── 开发指南-项目结构与开发.md # 开发指南（代码组织与分层）
│   ├── 开发指南-启动与部署.md     # 开发指南（启动与部署）
│   └── LICENSE.txt             #   许可协议
├── docker-compose.yml          # 开发环境全容器编排（日常使用这个）
└── README.md                   # 项目说明（本文件）
```

### 架构分层

```
Controller（薄层：校验输入 → 调 Service → 返回响应）
    ↓
Service（业务逻辑，app/common/service/）
    ↓
Repository（数据访问，db/ 目录，继承 db\Base）
```

## 快速开始

### 环境要求

- Docker + Docker Compose（无需本地安装 PHP / Node / MySQL）

### 启动步骤

```bash
# 1. 启动所有服务（PHP + MySQL + Redis + RabbitMQ + MinIO）
docker compose up -d

# 2. 导入数据库（仅首次）
docker exec -i mwp-mysql mysql -uroot -pdev123456 \
  --default-character-set=utf8mb4 my_web_project < db/sql/module1_user.sql

# 3. 访问
#    前端：http://localhost:8001/app/
#    API：http://localhost:8001/api/v1/auth/login
```

### 测试账号

| 账号 | 密码 | 角色 |
|------|------|------|
| admin | Admin@123456 | 管理员 |
| testuser | test123456 | 普通用户 |

### 服务端口

| 服务 | 端口 | 说明 |
|------|------|------|
| PHP 应用 | 8001 | ThinkPHP 内置服务器 |
| MySQL | 3306 | 数据库 |
| Redis | 6379 | 缓存 |
| RabbitMQ | 5672 / 15672 | 消息队列 / 管理台 |
| MinIO | 9000 / 9001 | 对象存储 / 控制台 |
| ElasticSearch | 9200 | 预留（`--profile es` 启动） |

## 前端开发

```bash
# 前端源码在 frontend/，构建产物输出到 public/app/
# 由于使用容器化，构建也走 Docker：
docker run --rm -v "$PWD":/app -w /app/frontend node:20-alpine \
  sh -c "npm install && node node_modules/vite/bin/vite.js build"
```

## API 规范

统一响应格式：

```json
{
  "code": 0,
  "message": "success",
  "data": {}
}
```

错误码：`40000` 参数错误 · `40100` 未登录 · `40101` Token过期 · `40300` 无权限 · `42900` 请求频繁

## 文档导航

| 文档 | 说明 |
|------|------|
| [docs/需求.md](docs/需求.md) | 项目需求与功能规划 |
| [docs/开发指南-项目结构与开发.md](docs/开发指南-项目结构与开发.md) | 代码组织、分层架构、开发流程 |
| [docs/开发指南-启动与部署.md](docs/开发指南-启动与部署.md) | 启动步骤、访问路径、部署流程 |
