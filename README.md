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

    格式为`@param string $uid 用户ID (123) 某某平台用户ID，10位字符串`
    - *参数类型*：必填，例:`string`
        - -file- 上传文件
        - int 上传文本
        - string 上传文本
    - *参数名称*：必填，例:`$uid`
    - *参数简介*：选填，例:`'用户ID`   
    - *默认值*：小括号里的值为参数默认值，选填，例:`(123)`
    - *参数详情*：选填，例:`'某某平台用户ID，10位字符串`


- **return** 接口返回值描述，支持多行
- **exception** 接口异常信息描述，支持多行

示例：

```php
    /**
     * @name 上传用户基本信息
     * @enable true
     * @method POST
     * @version >=2.1.0
     * @function 上传平台用户信息
     * @param int $uid [100] 用户ID XX平台用户ID
     * @param string $sid 会话ID
     * @param string $name 姓名
     * @param int $age 年龄 
     * @param -file- $headImg 头像 上传用户头像
     * @param string steps json字符串 
     * @param string userList json字符串 
     * @paramDetail
     * ===steps json字符串
     * string title 审批节点名称
     * string approver_id 审批用户id
     * string sort 审批步骤
     * ===userList json字符串
     * string name 姓名
     * string sex 性别
     * int sort 排序
     * @exception
     */
     */
    public function actionUpdateInfo()
```
