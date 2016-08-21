<?php
/**
 * @link https://github.com/craa/yii2-api-debugger
 */

namespace craa\ApiDebugger\models;

use craa\ApiDebugger\components\ApiDoc;

/**
 * 控制器注解模型
 * Class ControllerDoc
 *
 * @author Chen Hongwei <crains@qq.com>
 * @since 1.0
 */
class ControllerDoc extends ApiDoc
{
    /**
     * @var ModuleDoc $moduleDoc 父模块
     */
    public $moduleDoc;
    private $actions = [];

    /**
     * @return ActionDoc[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param mixed $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }

}