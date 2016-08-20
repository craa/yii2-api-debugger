<?php
/**
 * @link https://github.com/craa/weixin-sdk
 */

namespace craa\ApiDebugger\models;

use craa\ApiDebugger\components\ApiDoc;

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