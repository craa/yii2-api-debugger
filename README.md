# Yii2 API Debugger

**Yii2-API-Debugger** 是Yii2框架下支持自动生成文档的API调试模块，使用 **ReflectionClass** 与 **BootStrap**，通过提取`module``controller``action`的注释生成API菜单和文档，并且自动构建当前API请求表单，调试人员可以修改参数调用并查看返回结果。



## 安装

- composer
```bash
composer require "craa/yii2-api-debugger" "~1.0.0"
```

- 手动安装

```php
Yii::setAlias('@craa/ApiDebugger', '@app/yourpath/craa/api-debugger');
```
## 配置

```php
return [
    //...
    'modules' => [
        'api-debugger' => [
            'class' => 'craa\ApiDebugger\Module',
            'name' => '接口调试系统',
            'password' => '123',
            'allowedIPs' => ['*'],
            'baseModuleDir' => '@app',
            'baseModuleNamespace' => '\app',
            'view' => '@craa/ApiDebugger/views/default/index',
            'links' => [
                [
                    'text' => '<b>WIKI</b>',
                    'url' => 'http://github.com/craa/yii2-api-debugger',
                    'options' => [
                        'target' => '_blank',
                    ]
                ],
            ],
            'platforms' => [
                'iOS',
            ],
            'versions' => [
                '1.0.0',
            ]
        ],
    ]
];
```

然后就可以通过以下URL访问 API-Debugger:

```
http://localhost/path/to/index.php?r=api-debugger
```

## 注释规范
**Yii2-API-Debugger** 是建立在规范的注释上的，在使用前先了解一下支持的注释。

### Module
**Yii2-API-Debugger** 会从`Module`的注释中提取以下值：

- **name** 模块名称，如不填默认值为模块id
- **enable** 是否开启，设置为 'false' 时模块以及属下的接口将不会在菜单显示

示例：

```php
/**
 * common module definition class
 * @name 公共模块
 * @enable true
 */
class Module extends \yii\base\Module
```

### Controller
**Yii2-API-Debugger** 会从`Controller`的注释中提取以下值：

- **name** 控制器名称，如不填默认值为控制器id
- **enable** 是否开启，设置为 'false' 时控制器以及属下的接口将不会在菜单显示

示例：

```php
/**
 * Default controller for the `common` module
 * @name 默认控制器
 * @enable true
 */
class DefaultController extends Controller
```

### Action
**Yii2-API-Debugger** 会从`Action`的注释中提取以下值：

- **name** 接口名称，如不填默认值为ActionId
- **enable** 是否开启，设置为 'false' 时接口将不会在菜单显示
- **method** 接口请求方式，`GET`/`POST`
- **version** 支持版本，语法参考 [Composer Versions](https://getcomposer.org/doc/articles/versions.md)
- **function** 接口功能描述
- **param** 接口参数，该值可重复添加

        格式为`@param string $uid [3179827723] 用户ID 某某平台用户ID，10位字符串`
        *string* 表示参数类型，必填
        *$uid* 表示参数名，必填
        *[3179827723]* 大括号里的值为参数默认值，选填
        *用户ID* 参数简介，选填
        * 某某平台用户ID，10位字符串* 参数详情，选填

- **return** 接口返回值描述，支持多行
- **exception** 接口异常信息描述，支持多行

示例：

```php
/**
     * 获取用户基本信息
     * @name 用户信息
     * @enable true
     * @method GET
     * @version >=2.1.0
     * @function 通过uid/sid获取xx平台用户信息
     * @param int $uid [100] 用户ID XX平台用户ID
     * @param string $sid 会话ID
     * @return
     * {
     *     "result": true,
     *     "data": {
     *         "name": "张三",
     *         "age": "26"
     *     }
     * }
     * @exception
     * 10001 用户不存在
     * 10002 会话已失效
     */
    public function actionBasicInfo()
```