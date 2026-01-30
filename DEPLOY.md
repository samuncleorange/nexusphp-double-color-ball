# VPS 部署测试指南

## 快速部署步骤

### 1. 在 VPS 上克隆仓库

```bash
cd /path/to/nexusphp/NexusPlugin
git clone https://github.com/YOUR_USERNAME/nexusphp-double-color-ball.git DoubleColorBall
```

### 2. 安装依赖

```bash
cd DoubleColorBall
composer install --no-dev
```

### 3. 发布资源

在 NexusPHP 根目录执行：

```bash
# 发布配置文件
php artisan vendor:publish --tag=dcb-config

# 发布前端资源
php artisan vendor:publish --tag=dcb-assets

# 发布语言包
php artisan vendor:publish --tag=dcb-lang
```

### 4. 运行数据库迁移

```bash
php artisan migrate
```

### 5. 配置定时任务

编辑 crontab：
```bash
crontab -e
```

添加（如果还没有 Laravel 调度器）：
```
* * * * * cd /path/to/nexusphp && php artisan schedule:run >> /dev/null 2>&1
```

### 6. 测试访问

访问：`https://your-domain.com/plugin/php-double-color-ball`

## 更新代码

当有新的代码推送时：

```bash
cd /path/to/nexusphp/NexusPlugin/DoubleColorBall
git pull origin main
composer install --no-dev
php artisan migrate
php artisan vendor:publish --tag=dcb-assets --force
php artisan cache:clear
```

## 手动触发开奖（测试用）

```bash
php artisan dcb:draw --force
```

## 查看日志

```bash
tail -f storage/logs/laravel.log
```

## 常见问题

### 1. 404 错误
- 检查路由缓存：`php artisan route:clear`
- 重新缓存路由：`php artisan route:cache`

### 2. 资源文件 404
- 重新发布资源：`php artisan vendor:publish --tag=dcb-assets --force`
- 检查 public/vendor/dcb 目录是否存在

### 3. 定时任务不执行
- 检查 crontab 配置
- 手动运行：`php artisan schedule:run`
- 查看日志：`storage/logs/laravel.log`

## 开发模式

如果需要实时看到前端修改：

```bash
# 监听文件变化
php artisan vendor:publish --tag=dcb-assets --force
```

## 卸载

```bash
# 删除数据表（注意：会删除所有数据！）
php artisan migrate:rollback --path=vendor/samuncleorange/nexusphp-double-color-ball/database/migrations

# 删除插件目录
rm -rf NexusPlugin/DoubleColorBall
```
