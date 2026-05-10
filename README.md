# AutoRefund 产品退款插件

[![License](https://img.shields.io/badge/license-GPL-3.0-blue.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-1.3.0-green.svg)](https://github.com/W-jiyan/idcsmart-plugin-auto-refund)
[![PHP](https://img.shields.io/badge/php-7.2%2B-8892BF.svg)](https://php.net)

> 适用于 智简魔方财务 财务系统的产品退款管理插件，支持多种退款模式和自动化处理流程。

## ✨ 功能特性

### 退款类型
- **人工审核退款** - 管理员手动审核退款申请
- **自动退款** - 系统自动计算并退款到用户余额
- **API工单退款** - 向上游提交工单申请退款
- **插件间对接** - 上下游插件直接对接退款

### 退款规则
- **按时长退款** - 按实际使用时长计算退款金额
- **按月退款** - 按月计算退款金额
- **全额退款** - 全额退还首付金额

### 退款要求
- **产品首次** - 每个产品仅限首次退款
- **同类产品首次** - 同类产品仅限首次退款
- **指定时间内** - 开通后X小时内可申请

## 📋 系统要求

- PHP 7.2-7.4
- MySQL >= 5.7
- 智简魔方财务系统
- OpenSSL 扩展
- PDO 扩展

## 🚀 安装方法

1. 前往 [Release 页面](https://github.com/Wu-jiyan/idcsmart-plugin-auto-refund/releases) 下载插件压缩包
2. 将压缩包上传至 `/public/plugins/addons/` 并解压
3. 在后台插件管理中点击 `安装`
4. 配置功能设置

## 📖 使用文档

### 基础配置
1. 进入插件后台 `功能设置` 页面
2. 配置网站名称、通知渠道等基础信息
3. 设置代理可退时间、过期订单显示时间等参数

### 添加退款产品
1. 进入 `添加产品` 页面
2. 选择需要开启退款的产品
3. 配置退款类型、规则和要求
4. 保存配置

### 上游API配置（可选）
如需使用API工单退款或插件间对接功能：
1. 进入 `上游API配置` 页面
2. 添加上游API信息
3. 在产品配置中选择对应的上游配置

### 插件间对接（可选）
如需实现上下游自动对接：
1. 上游站点：用户在 `API KEY管理` 页面生成API KEY
2. 下游站点：在 `上游API配置` 中添加插件间对接配置
3. 配置产品使用插件间对接类型

## 🏗️ 项目结构

```
auto_refund/
├── AutoRefundPlugin.php          # 插件主类
├── api.php                        # 独立API接口（插件间对接）
├── menu.php                       # 后台菜单配置
├── menuclientarea.php             # 前台菜单配置
├── controller/
│   ├── AdminIndexController.php   # 后台控制器
│   └── clientarea/
│       └── IndexController.php    # 前台控制器
├── lib/
│   ├── ApiClient.php              # API客户端
│   └── EncryptUtil.php            # 加密工具
├── template/                      # 模板文件
│   ├── admin/                     # 后台模板
│   └── clientarea/                # 前台模板
└── assets/                        # 静态资源
```

## ⚠️ 重要说明

> **本项目部分核心代码由 AI 辅助编写，虽然经过了基础测试，但可能存在一些微小的 Bug 或边界情况未完全覆盖。**
>
> 如果您在使用过程中发现任何问题，欢迎提交 Issue 或 Pull Request 进行修复和优化。

## 🤝 贡献指南

我们非常欢迎社区贡献！无论是 Bug 修复、功能增强还是文档改进，您的每一份贡献都将使这个项目变得更好。

### 如何贡献

1. **Fork** 本项目
2. 创建您的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交您的修改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开一个 **Pull Request**

## 📝 更新日志

### v1.3.0
- ✨ 新增插件间对接功能
- ✨ 支持上下游自动级联退款
- ✨ 新增API KEY管理功能
- 🔧 优化数据库结构
- 🔧 修复多处显示问题

### v1.2.0
- ✨ 新增API工单退款功能
- ✨ 支持向上游提交工单

### v1.1.0
- ✨ 新增自动退款功能
- ✨ 支持多种退款规则

### v1.0.0
- 🎉 初始版本发布
- ✨ 支持人工审核退款

## 📄 开源协议

本项目采用 [GPL-3.0 license](LICENSE) 开源协议。

## 💬 联系我们

- 邮件联系：wujiyan@wujiyan.cc
- QQ联系：3452732800
- 加入QQ群：[点击链接加入群聊【AutoRefund】] (https://qm.qq.com/q/u97YNJ3Jmw)

## 🙏 致谢

感谢所有为这个项目做出贡献的开发者！

---

> **Star 🌟 这个项目，如果它对你有帮助！**
