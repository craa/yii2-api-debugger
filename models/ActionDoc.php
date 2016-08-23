<?php
/**
 * @link https://github.com/craa/yii2-api-debugger
 */

namespace craa\ApiDebugger\models;

use craa\ApiDebugger\components\ApiDoc;

/**
 * Action注解模型
 * Class ActionDoc
 *
 * @author Chen Hongwei <crains@qq.com>
 * @since 1.0
 */
class ActionDoc extends ApiDoc
{
    /**
     * @var ControllerDoc $controllerDoc 所属控制器
     */
    public $controllerDoc;
    /**
     * @var string $method 请求方法[GET, POST]
     */
    protected $method;
    /**
     * @var string $version 支持版本
     */
    protected $version;
    /**
     * @var string $function 功能描述
     */
    protected $function;
    /**
     * @var Param[] $param 接口支持的参数(数组)
     */
    protected $param;
    /**
     * @var string $return 返回值描述
     */
    protected $return;
    /**
     * @var string $exception 异常信息
     */
    protected $exception;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method ? $this->method : 'GET';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version ? $this->version : '*';
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @return Param[]
     */
    public function getParam()
    {
        if (is_array($this->param) && isset($this->param[0]) && is_string($this->param[0])) {
            $params = [];
            foreach ($this->param as $paramInfo) {
                $params[] = new Param($paramInfo);
            }
            $this->param = $params;
        } elseif (is_string($this->param)) {
            $this->param = [new Param($this->param)];
        } elseif (is_null($this->param)) {
            $this->param = [];
        }

        return $this->param;
    }

    /**
     * @return string
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @return string
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * 循环获取顶级模块
     * @return ModuleDoc
     */
    public function getRootModule()
    {
        $moduleDoc = $this->controllerDoc->moduleDoc;
        if (!empty($moduleDoc)) {
            while ($moduleDoc->moduleDoc) {
                $moduleDoc = $moduleDoc->moduleDoc;
            }
        }
        return $moduleDoc;
    }

    /**
     * 获取参数说明
     * @return string
     */
    public function getParamDescription()
    {
        $str = '';
        foreach ($this->getParam() as $p) {
            $str .= $p->getName() . ' ' . $p->getBrief() . ' ' . $p->getDetail() . '<br />';
        }
        return $str;
    }
}

class Param
{
    /**
     * @var string $type 参数类型
     */
    private $type = '未知';
    /**
     * @var string $name 参数名
     */
    private $name = '未知';
    /**
     * @var string $brief 简介
     */
    private $brief = '未设置';
    /**
     * @var string $detail 详细信息
     */
    private $detail;
    /**
     * @var string $exception 异常信息
     */
    private $exception;

    /**
     * @var string $default 参数默认值
     */
    private $default = '';

    public function __construct($paramInfo)
    {
        preg_match('/([\-_\w]+) (\$\w+) ?(\[.+\])? ([^ ]*) ?(.*)?/u', $paramInfo, $part);
        if (!empty($part[1])) $this->type = $part[1];
        if (!empty($part[2])) $this->name = str_replace('$', '', $part[2]);
        if (!empty($part[3])) $this->default = trim($part[3], '[]');
        if (!empty($part[4])) $this->brief = $part[4];
        if (!empty($part[5])) $this->detail = nl2br(implode(' ', array_slice($part, 5)));
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBrief()
    {
        return $this->brief;
    }

    /**
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @return mixed
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

}