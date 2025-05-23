# 哈喽米表

简洁的域名展示平台（简称米表），支持展示域名、价格和联系方式。

## 主要特点

- 响应式设计，适配移动端/PC端
- 深色/浅色主题切换
- 域名批量导入和管理
- 可选择性展示价格和备注
- 支持多种联系方式和二维码展示
- 支持自定义网站名称、域名和页脚信息

## 快速安装

### 系统要求
- PHP 7.4+
- MySQL 5.7+
- PDO/GD PHP扩展

### 安装步骤
1. 上传所有文件到网站目录
2. 访问 `http://你的域名/install/` 进行安装
3. 填写数据库信息和管理员账号
4. 安装完成后删除 `install` 目录

## 配置说明

### 基本设置
- 网站名称、域名和页脚信息可在后台设置
- 联系方式和二维码可在后台配置

### 图片设置
- Logo支持SVG/PNG格式，建议高度不超过60像素
- 二维码图片需提供完整URL地址

### hCaptcha配置（选配）
- 在安装时可选择配置hCaptcha Site Key和Secret Key
- 可在后台管理页面进行修改

## 安全建议
- 安装完成后删除`install`目录
- 使用强密码并定期更改
- 定期备份数据库
