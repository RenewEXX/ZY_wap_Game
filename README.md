# 罪渊 WAP

经典菜单式文字游戏：黑底绿字、链接点选、打怪升级、黑市买装备。

## 运行

需要 PHP 8+，带 SQLite。本机若没有 PHP，可先安装：

```powershell
winget install PHP.PHP.8.3 --accept-package-agreements --accept-source-agreements
```

然后在项目目录启动：

```powershell
php -S localhost:8080
```

浏览器打开 http://localhost:8080

## 玩法

1. 登记名号进入罪渊
2. 地图里点怪交手：下手 / 喝药 / 撤
3. 经验够了自动升级
4. 黑市换武器护甲，营地睡觉回满血
5. 深处有狱卒

存档在 `data/zuiyuan.sqlite`。
