# API Doc generate
===========================
api-doc-generate 是一个根据框架路由定义文件生成接口文档的php工具。

目前支持的框架有 `thinkphp`

## thinkphp 使用


### 安装

```
composer require iayoo/apidoc-generate
```

### 使用

在控制器入口加入注释

```php

class Index
{
    /**
     * admin-v1-index
     * @title 登录
     * @validate app\admin\validate\TestValidate.login
     */
    public function login(){

    }
}
```

给成员方法注释用来解析接口内容

- title 接口标题
- validate 验证类：验证类名.场景

验证类定义如下：

```php

namespace app\admin\validate;


use Iayoo\ApidocGenerate\provider\thinkphp\ApiDocValidate;

class TestValidate extends ApiDocValidate
{
    protected $rule = [
        'account'=>'require',
        'password'=>'require',
    ];

    protected $field = [
        'account'=>'账号',
        'password'=>'密码'
    ];

    protected $message = [
        'account.require'=>'账号不能为空',
        'password.require'=>'密码不能为空'
    ];

    protected $scene = [
        'login' => ['account','password'],
    ];
}
```

其中的 `field` 定义的就是 `postman` 文件中的请求参数名

`Validate` 必须继承 `Iayoo\ApidocGenerate\provider\thinkphp\ApiDocValidate` 

`Iayoo\ApidocGenerate\provider\thinkphp\ApiDocValidate` 继承了thinkphp 框架的 `Validate`

### 生成文件

在 `config/console.php` 配置文件中加入以下配置
  
```php
return [
    'api-doc'=>\Iayoo\ApidocGenerate\provider\thinkphp\ApiDocGenerateCommand::class
];
```

命令行执行 `php think api-doc` 

执行完成后会在项目根目录生成 `api-dco.postman-*.json` 的 `json` 文件。
