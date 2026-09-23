# ES · 极简 PHP 框架

> **核心不到 400 行、总共 18KB** 的 PHP MVC 框架。没有 Composer、没有构建、没有魔法 —— 下载丢进目录就能跑。
> 适合：想彻底看懂「一个 Web 框架到底做了什么」的人、需要极轻量脚本型 Web 服务的场景、给同事讲 MVC 原理时当教材。

[![Stars](https://img.shields.io/github/stars/Echosong/ES?style=flat-square&color=e3b341)](https://github.com/Echosong/ES/stargazers)
[![Forks](https://img.shields.io/github/forks/Echosong/ES?style=flat-square)](https://github.com/Echosong/ES/network/members)
[![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![Code Size](https://img.shields.io/badge/core-%3C400%20lines-brightgreen?style=flat-square)]()

<!-- 建议补一张前台/后台截图，放在 res/ 或 doc/ 下，然后替换下面这行 -->
![ES 示例界面](doc/screenshot.png)

## 为什么会有 ES

接触 PHP 近十年，我用过 `laravel`、`yii`、`thinkphp`，也用过 `asp.net mvc`、`django`、`web.py`、`beego`。每个框架都很好，但在**小项目、内部工具、教学演示**这些场景里，它们都太重了。

2015 年给同事分享 MVC 核心思想时我想：**如果把框架里真正必需的部分抽出来，会有多少行？** 于是有了 ES —— 五个核心文件，不到 400 行，把路由、控制器、模型、视图四件事说清楚。

## 特性

- 🪶 **极简**：核心 5 个文件 < 400 行，总大小 18KB
- 🚫 **零依赖**：不需要 Composer，不需要 PSR 自动加载器（自带极简 autoload）
- 🧭 **灵活路由**：`index.php/module/controller/action/param` 形式的 pathinfo 路由，支持 Apache / Nginx
- 🖥 **可跑 CLI**：同一份代码支持 `php index.php module controller action param` 形式的命令行调用（写定时任务很方便）
- 🧱 **MVC 分层**：`controller` / `model` / `view` 目录即约定
- 🔌 **插件机制**：`config.php` 里 `plugins` 数组可挂载自定义目录
- 🪵 **自带错误处理**：`set_error_handler` + `register_shutdown_function` 记录 fatal error 到 `logs/`

## 环境要求

- PHP **7.4+**（推荐 8.x；PHP 5.6 理论可用但未测试）
- Web 服务器：Apache（需 `mod_rewrite`）或 Nginx
- 可选：任意 PDO 支持的数据库（框架本身不绑定数据库）

## 安装

```bash
# 1. 克隆
git clone https://github.com/Echosong/ES.git
cd ES

# 2. 让 logs 可写
mkdir -p logs && chmod 777 logs

# 3. 用 PHP 内置服务器试跑（最快）
php -S localhost:8000
# 浏览器打开 http://localhost:8000
```

### Apache

确保 `.htaccess` 被允许（`AllowOverride All`）：

```apache
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [QSA,L]
```

### Nginx

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php/$1 last;
    }
}
```

## 目录结构

```
├── index.php          # 唯一入口：定义 APP_DIR → 加载 src/core/es.php
├── src/
│   ├── config.php     # 全局配置（含 plugins 数组）
│   ├── core/
│   │   ├── es.php         # 启动文件：常量、配置、自动加载、路由分发
│   │   ├── helper.php     # 流程核心：路由解析、请求分发、日志、错误处理
│   │   ├── controller.php # 控制器基类
│   │   ├── model.php      # 模型基类
│   │   └── view.php       # 视图引擎
│   ├── controller/    # 业务控制器（按 module 分子目录）
│   ├── model/         # 业务模型（小型业务可直接写在 controller 里）
│   └── view/          # 视图模板
├── res/               # 静态资源
└── logs/              # 日志（需写权限）
```

## 五分钟上手

**1）新建控制器** `src/controller/home.php`：

```php
<?php

class home extends Controller
{
    public function index()
    {
        $this->assign('title', 'Hello ES');
        $this->display('home/index.php');
    }

    public function json()
    {
        $this->json(['code' => 0, 'msg' => 'ok']);
    }
}
```

**2）新建视图** `src/view/home/index.php`：

```php
<!DOCTYPE html>
<html>
<head><title><?php echo $title; ?></title></head>
<body>
  <h1><?php echo $title; ?></h1>
  <p>当前时间：<?php echo date('Y-m-d H:i:s'); ?></p>
</body>
</html>
```

**3）访问**

| 访问方式 | URL / 命令 |
|---|---|
| Web | `http://localhost:8000/home/index` |
| CLI | `php index.php home index` |
| 带参数 | `php index.php home index id=3` |

> 路由约定：`index.php/{module}/{controller}/{action}/{param}`。默认模块与默认控制器在 `src/config.php` 中配置。

## 配置（`src/config.php`）

```php
<?php
return [
    'startSession' => true,   // 是否自动 session_start
    'plugins'      => [],     // 额外自动加载目录，如 ['lib', 'ext']
    // ... 业务配置也可直接写在这里
];
```

## 设计思路

| 文件 | 行数级别 | 职责 |
|---|---|---|
| `es.php` | ~40 | 定义常量、载入配置、注册 autoload、交给 Helper |
| `helper.php` | 最多 | 路由解析、分发、日志、错误处理（框架的心脏） |
| `controller.php` | ~20 | 控制器基类：assign / display / json |
| `model.php` | ~20 | 模型基类：数据库操作入口 |
| `view.php` | ~30 | 视图渲染：把 assign 的变量提取到模板作用域 |

**读源码的顺序建议：`index.php` → `es.php` → `helper.php`**，半小时能全部读完。

## 常见问题（FAQ）

| 问题 | 解法 |
|---|---|
| 所有页面都 404 | 没开 rewrite；Apache 检查 `AllowOverride`，Nginx 检查 `location /` 的 rewrite |
| 提示 logs 不可写 | `chmod -R 777 logs`（生产环境请改成属主为 web 用户） |
| 想用 Composer 管理依赖 | ES 设计上不依赖 Composer，但 `index.php` 会在 `vendor/autoload.php` 存在时自动加载，可自行引入 |
| 支持多语言（i18n）吗 | 暂未内置（对应历史 issue #1），欢迎 PR；思路是在 `helper.php` 的 `start()` 前挂载语言包 |
| 能用于生产吗 | 可以用于轻量内部系统；大型项目建议用成熟框架。**ES 的定位是"足够小、足够透明"** |

## 相关项目

- **[beego_blog](https://github.com/Echosong/beego_blog)** —— Go + beego 个人博客系统（566★）
- **[轻巧之光 light](https://github.com/Echosong/light)** —— Spring Boot 低代码框架
- **[DSH Desktop](https://github.com/Echosong/dsh-desktop-go)** —— DeepSeek Harness 的 Windows 桌面客户端

## License

[MIT](LICENSE) © Echosong

## 联系

- 邮箱：songfeigang@shhuayi.com
- 博客：[cnblogs.com/echosong](https://www.cnblogs.com/echosong/)

> 用 ES 讲清楚过一次 MVC？欢迎点个 ⭐。
