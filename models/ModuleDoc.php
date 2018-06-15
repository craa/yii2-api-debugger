<?php
/**
 * @link https://github.com/craa/yii2-api-debugger
 */

namespace craa\ApiDebugger\models;

use craa\ApiDebugger\components\ApiDoc;
use yii\log\Logger;

/**
 * 模块注解模型
 * Class ModuleDoc
 *
 * @author Chen Hongwei <crains@qq.com>
 * @since 1.0
 */
class ModuleDoc extends ApiDoc
{
    /**
     * @var ModuleDoc $moduleDoc 父模块
     */
    public $moduleDoc;
    private $modules = [];
    private $controllers = [];

    /**
     * @return ModuleDoc[]
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @param ModuleDoc[] $modules
     */
    public function setModules($modules)
    {
        $this->modules = $modules;
    }

    /**
     * @return ControllerDoc[]
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * @param array $controllers
     */
    public function setControllers($controllers)
    {
        $this->controllers = $controllers;
    }

    public function getRoute()
    {
        $ns = $this->getReflection()->getName();
        $id = str_replace(['modules','Module'], '', $ns);
        $id = str_replace(['//', '\\\\'], '/', $id);
        $id = trim($id, '/\\');
        $id = substr($id, strpos($id, '/')+1);

        try{
            if($id){
                $c = \Yii::$app->getModule($id);
                return $c?$c->getUniqueId():"";
            }
        }catch (\Exception $e){
        }
    }

}