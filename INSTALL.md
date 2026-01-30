# 双色球插件安装指南

## 快速开始

### 方式一：本地开发安装（推荐用于测试）

1. **配置本地路径**

在 NexusPHP 主项目的 `composer.json` 中添加本地路径仓库：

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./NexusPlugin/DoubleColorBall"
        }
    ]
}
```

2. **安装插件**

```bash
cd /Users/shu/Documents/piggo2/nexusphp
composer require samuncleorange/nexusphp-double-color-ball
```

3. **执行插件安装命令**

```bash
php artisan plugin install samuncleorange/nexusphp-double-color-ball
```

这将自动：
- 执行数据库迁移（创建 periods 和 tickets 表）
- 发布配置文件到 `config/nexus_plugin_dcb.php`
- 发布静态资源到 `public/vendor/dcb/`
- 创建初始期号

4. **配置定时任务**（如果尚未配置）

```bash
crontab -e
```

添加以下行：
```
* * * * * cd /Users/shu/Documents/piggo2/nexusphp && php artisan schedule:run >> /dev/null 2>&1
```

5. **访问插件**

- 前台：`http://your-domain/plugin/php-double-color-ball`
- 后台：Filament 管理面板 → Double Color Ball

---

### 方式二：从 GitHub 安装（生产环境）

1. **初始化 Git 仓库**（首次）

```bash
cd /Users/shu/Documents/piggo2/nexusphp/NexusPlugin/DoubleColorBall
git init
git add .
git commit -m "Initial commit: Double Color Ball plugin v0.1.0"
git branch -M main
git remote add origin https://github.com/samuncleorange/nexusphp-double-color-ball.git
git push -u origin main
```

2. **在生产服务器安装**

```bash
cd /path/to/nexusphp
composer config repositories.double-color-ball vcs https://github.com/samuncleorange/nexusphp-double-color-ball.git
composer require samuncleorange/nexusphp-double-color-ball
php artisan plugin install samuncleorange/nexusphp-double-color-ball
```

---

## 配置说明

### 游戏规则配置

编辑 `config/nexus_plugin_dcb.php`：

```php
'game_rules' => [
    'red_ball_count' => 6,      // 红球选取数量
    'red_ball_max'   => 33,     // 红球最大号码
    'blue_ball_count'=> 1,      // 蓝球选取数量
    'blue_ball_max'  => 16,     // 蓝球最大号码
],
```

### 价格和限制

```php
'price_per_ticket' => 500,          // 单注价格（魔力值）
'max_tickets_per_user' => 100,      // 单期购买上限
```

### 奖金分配

```php
'prize_allocation' => [
    1 => ['type' => 'ratio', 'value' => 0.70, 'min' => 100000],  // 一等奖：奖池70%
    2 => ['type' => 'ratio', 'value' => 0.20, 'min' => 10000],   // 二等奖：奖池20%
    3 => ['type' => 'fixed', 'value' => 3000],                   // 三等奖：固定3000
    4 => ['type' => 'fixed', 'value' => 200],                    // 四等奖：固定200
],
```

### 开奖时间

```php
'draw_schedule' => [
    'day' => 'sunday',              // 星期日
    'time' => '21:00',              // 21:00
    'timezone' => 'Asia/Shanghai',  // 时区
],
```

---

## 测试

### 手动触发开奖

```bash
php artisan dcb:draw
```

### 查看开奖日志

```bash
tail -f storage/logs/laravel.log | grep DCB
```

### 测试购买流程

1. 访问 `/plugin/php-double-color-ball`
2. 选择号码或点击"机选"
3. 点击"立即购买"
4. 检查魔力值是否正确扣除
5. 查看"我的彩票"确认购买记录

---

## 常见问题

### Q: 安装后访问 404

**A:** 确保已执行 `php artisan plugin install` 命令，并清除缓存：
```bash
php artisan route:clear
php artisan cache:clear
```

### Q: 定时任务不执行

**A:** 检查 crontab 是否正确配置，手动测试：
```bash
php artisan schedule:run
```

### Q: 比特币 API 无法访问

**A:** 如果服务器无法访问外网，插件会自动使用备用随机源。可以在配置中禁用：
```php
'bitcoin_api' => [
    'enabled' => false,  // 禁用比特币 API
],
```

### Q: 如何修改 UI 样式

**A:** 发布视图文件后自定义：
```bash
php artisan vendor:publish --tag=dcb-views
```
然后编辑 `resources/views/vendor/dcb/` 下的文件。

---

## 卸载

```bash
php artisan plugin uninstall samuncleorange/nexusphp-double-color-ball
composer remove samuncleorange/nexusphp-double-color-ball
```

**注意**：卸载会删除所有数据库表和数据，请谨慎操作！

---

## 技术支持

- GitHub Issues: https://github.com/samuncleorange/nexusphp-double-color-ball/issues
- 文档: https://github.com/samuncleorange/nexusphp-double-color-ball

---

## 开发致谢

本项目开发过程中得到了 **Google Antigravity** 与 **Gemini 3** 的技术支持与协助，特此致谢。
