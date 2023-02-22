ThinkCMF 5.1.7 
===============


### 开发手册
http://www.kancloud.cn/thinkcmf/doc5_1

### Git仓库

1. GitHub:https://github.com/sdsxs/taskadmin.git 主要仓库


### 环境推荐
> php7.3

> mysql 5.7+

> 打开rewrite


### 最低环境要求
> php5.6+

> mysql 5.5+ (mysql5.1安装时选择utf8编码，不支持表情符)

> 打开rewrite



1. public目录做为网站根目录,入口文件在 public/index.php
2. 配置好网站，请访问http://你的域名




### 完整版目录结构
~~~
thinkcmf  根目录
├─api                     api目录
│  ├─demo                 演示应用api目录
│  │  ├─controller        控制器目录
│  │  ├─model             模型目录
│  │  └─ ...              更多类库目录
├─app                     应用目录
│  ├─demo                 演示应用目录
│  │  ├─controller        控制器目录
│  │  ├─model             模型目录
│  │  └─ ...              更多类库目录
│  ├─ ...                 更多应用
│  ├─app.php              应用(公共)配置文件[可选]
│  ├─command.php          命令行工具配置文件[可选]
│  ├─common.php           应用公共(函数)文件[可选]
│  ├─database.php         数据库配置文件[可选]
│  ├─tags.php             应用行为扩展定义文件[可选]
├─data                    数据目录（可写）
│  ├─config               动态配置目录（可写）
│  ├─route                动态路由目录（可写）
│  ├─runtime              应用的运行时目录（可写）
│  └─ ...                 更多
├─public                  WEB 部署目录（对外访问目录）
│  ├─plugins              插件目录
│  ├─static               官方静态资源存放目录(css,js,image)，勿放自己项目文件
│  ├─themes               前后台主题目录
│  │  ├─admin_simpleboot3 后台默认主题
│  │  └─default           前台默认主题
│  ├─upload               文件上传目录
│  ├─api.php              API入口
│  ├─index.php            入口文件
│  ├─robots.txt           爬虫协议文件
│  ├─router.php           快速测试文件
│  └─.htaccess            apache重写文件
├─extend                  扩展类库目录[可选]
├─vendor                  第三方类库目录（Composer）
│  ├─thinkphp             ThinkPHP目录
│  └─...             
├─composer.json           composer 定义文件
├─LICENSE                 授权说明文件
├─README.md               README 文件
├─think                   命令行入口文件
~~~



### 更新日志
#### 5.1.7
* 重构回收站代码，添加全部删除、一键清空和全部还原功能
* 增加插件url美化
* 增加默认过滤器
* 增加插件未安装、未启用时禁止访问
* 增加`think\facade\Db`类
* 优化语言包加载顺序
* 优化前端组件
* 优化cmf版本获取
* 优化`cmf_clear_cache()`函数
* 修复用户行为产生积分或金币为空还有日志问题
* 修复管理员编辑报错
* 规范所有数据库操作用法


#### 5.1.6
* 修复插件后台权限认证问题
* 升级到tp5.1.40
* 优化后台管理添加和编辑
* 删除phpquery类jqueryServer目录
* 优化后台管理员新增和编辑
* 优化语言包加载顺序

#### 5.1.5
* 升级到tp5.1.39
* 增加模板设计数组列表图片显示
* 优化前台基类
* 取消路由排序限制

#### 5.1.4
* 优化上传逻辑，已传文件更新文件名
* 优化系统钩子初始化
* 修复编辑器锚点处理错误
* 修复部分系统函数判断问题
* 修复tp5.1.38前台控制器报错
* 修复tp5.1.38下邮件验证码发不出

#### 5.1.3
* 增加`CMF_DATA`常量（注意升级）
* 增加插件路由功能
* 增加插件URL美化功能
* 修复app_init钩子引起的命令行报错
* 修复API中文件url转化错误

#### 5.1.2
[核心]
* 升级tp到`5.1.37`
* 优化`slides,noslides`标签
* 修复头像地址获取函数
* 优化上传类支持API文件上传
* 封装后台菜单，应用钩子，用户行为导入
* 增加应用自动安装
* 优化后台百度地图链接支持https




#### 5.1.0-beta
[核心]
* `ThinkPHP 5.1`





