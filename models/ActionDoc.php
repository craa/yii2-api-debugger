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

    protected $paramDetail;

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

    /**
     * 获取参数说明
     * @return array
     */
    public function getParamDescriptionList()
    {
        $data = [];
        foreach ($this->getParam() as $p) {
            $data[] = [
                "key"=>$p->getName(),
                "brief"=>$p->getBrief(),
                "type"=>$p->getType(),
                "detail"=>$p->getDetail(),
                "defaultValue"=>$p->getDefault(),
            ];
        }

        return $data;
    }

    /**
     * 获取参数的子数据结构的数据结构的详情
     *
     * @return array
     */
    public function getParamDetail()
    {
        return $this->paramDetail?$this->formatReturnData($this->paramDetail):[];
    }

    /**
     * 获取返回的数据结构
     *
     * @return array
     */
    public function getReturnDataDetail()
    {
        return $this->return?$this->formatReturnData($this->return):[];
    }

    /**
     * 格式化返回的数据结构
     *
     * @param $return
     * @return array
     */
    public function formatReturnData($return)
    {
        $returnArr = explode("===",trim($return));
        $returnDataDetail = [];
        foreach ($returnArr as $item) {
            if(!$item)
            {
                continue;
            }

            $returnArrItem = explode("\n",trim($item));

            if(!$returnArrItem)
            {
                continue;
            }

            $dataKey = str_replace(array("\r\n", "\r", "\n","<br />"), "", $returnArrItem[0]);

            unset($returnArrItem[0]);

            foreach ($returnArrItem as $dataDetailKey=>$dataDetail)
            {
                $dataDetailKey = str_replace(PHP_EOL, '', $dataDetailKey);

                if(!$dataDetail)
                {
                    continue;
                }
                $returnArrItem[$dataDetailKey] = $this->extractReturnDataInfo(strip_tags($dataDetail));
            }

            $returnDataDetail[$dataKey] = $returnArrItem;
        }

        return $returnDataDetail;
    }

    /**
     * 提取返回的参数详情
     * @param $paramInfo
     * @return array
     */
    public function extractReturnDataInfo($paramInfo)
    {
        if(!$paramInfo)
        {
            return [];
        }

        $param = [
            //参数数据类型
            'type'=>'',
            //参数key
            'key'=>'',
            //参数名称
            'brief'=>'',
            //参数补充说明
            'detail'=>'',
            'defaultValue'=>'',
        ];

        $part = explode(' ', trim($paramInfo));
        if(!empty($part[0])) $param['type'] = $part[0];
        if(!empty($part[1])) $param['key'] = $part[1];
        if(!empty($part[2])) $param['brief'] = $part[2];
        if(!empty($part[3])) $param['detail'] = $part[3];

        return $param;
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
        //preg_match('/([\-_\w]+) (\$\w+) ?(\[.+\])? ([^ ]*) ?(.*)?/u', $paramInfo, $part);
        // if (!empty($part[1])) $this->type = $part[1];
        // if (!empty($part[2])) $this->name = str_replace('$', '', $part[2]);
        // if (!empty($part[3])) $this->name = $this->name.$part[3];
        // if (!empty($part[4])) $this->brief = $part[4];
        // if (!empty($part[6])) $this->detail = $part[6];
        // if (!empty($part[5])) $this->default = trim($part[5], '()');

        $part = explode(" ", $paramInfo);
        if (!empty($part[0])) $this->type = $part[0];
        if (!empty($part[1])) $this->name = str_replace('$', '', $part[1]);
        if (!empty($part[2])) $this->brief = $part[2];
        if (!empty($part[3])) $this->default = trim($part[3], '()');
        if (!empty($part[4])) $this->detail = $part[4];
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