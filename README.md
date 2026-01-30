# NexusPHP Double Color Ball Plugin

一个为 NexusPHP 设计的趣味双色球彩票插件，允许用户使用魔力值购买彩票，系统定期自动开奖并发放奖励。

## ✨ 功能特性

- 🎲 **可配置游戏规则** - 红球/蓝球数量和范围可在后台灵活调整
- 🔒 **公平性保证** - 基于比特币区块哈希的 Provably Fair 算法，确保开奖结果公正透明
- ⚡ **自动化流程** - 定时开奖、自动计算中奖、自动发放奖励
- 🎨 **趣味 UI** - 适配"猪猪儿童区"风格的可爱交互界面
- 📊 **完整管理** - Filament 后台管理所有配置和记录
- 🌍 **多语言支持** - 支持中文和英文

## 📋 环境要求

- PHP >= 8.2
- Laravel >= 12.0
- NexusPHP (最新版本)
- MySQL/MariaDB
- Redis (用于缓存和锁)

## 🚀 快速安装

### 1. 添加仓库

```bash
composer config repositories.double-color-ball vcs https://github.com/samuncleorange/nexusphp-double-color-ball.git
```

### 2. 安装插件

```bash
composer require samuncleorange/nexusphp-double-color-ball
```

### 3. 执行安装命令

```bash
php artisan plugin install samuncleorange/nexusphp-double-color-ball
```

此命令将自动：
- 执行数据库迁移
- 发布配置文件
- 注册服务提供者

### 4. 配置定时任务

在服务器 Crontab 中添加（如果尚未配置）：

```bash
* * * * * cd /path/to/nexusphp && php artisan schedule:run >> /dev/null 2>&1
```

## ⚙️ 配置说明

### 游戏规则配置

在 Filament 后台的"双色球配置"页面，您可以自定义：

- **红球配置**：选取数量（3-10）、号码范围（10-50）
- **蓝球配置**：选取数量（0-2）、号码范围（5-20）
- **单注价格**：每注消耗的魔力值
- **奖金分配**：各奖级的奖金比例和固定金额

### 开奖时间配置

默认每周日 21:00 开奖，可在配置文件中修改：

```php
// config/nexus_plugin_dcb.php
'draw_schedule' => [
    'day' => 'sunday',  // 星期日
    'time' => '21:00',  // 21:00
    'timezone' => 'Asia/Shanghai'
]
```

## 📖 使用说明

### 前台功能

访问 `/plugin/php-double-color-ball` 即可进入双色球页面（需登录）。

- **选号购买** - 手动选号或机选，提交购买
- **我的彩票** - 查看当前期和历史期的购买记录
- **开奖历史** - 查看历史开奖结果和中奖统计
- **公平性验证** - 验证开奖结果的公正性

### 后台管理

在 Filament 后台（仅管理员可访问）：

- **期号管理** - 查看所有期号、中奖详情、手动创建新期
- **注码管理** - 查看所有用户的购买记录和中奖情况
- **配置管理** - 调整游戏规则、奖金分配等参数

## 🔐 公平性说明

### Provably Fair 算法

本插件采用"比特币区块哈希 + 确定性算法"确保开奖结果不可操控：

1. **外部种子** - 使用开奖时间点后产生的第一个比特币区块哈希值
2. **公开算法** - 使用 `HMAC-SHA512(区块哈希, 期号)` 生成伪随机流
3. **计算号码** - 将随机流按位取模得到中奖号码
4. **完全开源** - 所有算法代码完全公开

### 如何验证

用户可在"公平性验证器"页面：

1. 输入期号
2. 查看该期的比特币区块哈希
3. 系统实时计算并显示中奖号码
4. 与实际开奖结果对比验证

## 🎮 游戏规则

### 默认规则（可后台配置）

- 从 1-33 中选择 6 个红球
- 从 1-16 中选择 1 个蓝球
- 单注价格：500 魔力值

### 奖级设置

| 等级 | 中奖条件 | 奖励 |
|------|---------|------|
| 一等奖 | 6红+1蓝全中 | 奖池 70% + 保底 10 万 |
| 二等奖 | 6红或5红+1蓝 | 奖池 20% + 保底 1 万 |
| 三等奖 | 5红或4红+1蓝 | 固定 3000 |
| 四等奖 | 4红或3红+1蓝 | 固定 200 |

*注：所有奖级配置均可在后台自定义*

## 🛠️ 开发与测试

### 本地开发

1. 克隆仓库到 NexusPHP 项目目录：
   ```bash
   cd /path/to/nexusphp
   git clone https://github.com/samuncleorange/nexusphp-double-color-ball.git NexusPlugin/DoubleColorBall
   ```

2. 在主项目 `composer.json` 中添加本地路径：
   ```json
   "repositories": [
       {
           "type": "path",
           "url": "./NexusPlugin/DoubleColorBall"
       }
   ]
   ```

3. 安装插件：
   ```bash
   composer require samuncleorange/nexusphp-double-color-ball
   php artisan plugin install samuncleorange/nexusphp-double-color-ball
   ```

### 手动触发开奖

```bash
php artisan dcb:draw
```

## 📝 更新日志

查看 [CHANGELOG.md](CHANGELOG.md) 了解版本更新历史。

## 📄 开源协议

本项目采用 [GPL-2.0](LICENSE) 开源协议。

## 🙏 致谢

本项目开发过程中得到了 **Google Antigravity** 与 **Gemini 3** 的技术支持与协助，特此致谢。

## 📮 反馈与支持

如有问题或建议，欢迎提交 Issue 或 Pull Request。

---

**Enjoy the game! 🎉**
