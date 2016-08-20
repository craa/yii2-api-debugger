<?php
/**
 *
 * Author: chenhongwei <chenhw@mysoft.com.cn>
 * Time: 2016/7/21 14:03
 */

namespace craa\ApiDebugger\components;

use craa\ApiDebugger\models\ActionDoc;
use craa\ApiDebugger\models\ControllerDoc;
use craa\ApiDebugger\models\ModuleDoc;
use Yii;
use yii\base\Exception;
use yii\helpers\Inflector;

/**
 * API注解分析服务
 * Class ApiDocService
 * @package craa\ApiDebugger\components
 */
class DocParseService
{
    private $baseModuleDir;
    private $baseModuleNamespace;

    public function __construct($baseModuleDir, $baseModuleNamespace)
    {
        $this->setBaseModuleDir($baseModuleDir);
        $this->setBaseModuleNamespace($baseModuleNamespace);
    }

    /**
     * @return mixed
     */
    public function getBaseModuleDir()
    {
        return $this->baseModuleDir;
    }

    /**
     * @param mixed $baseModuleDir
     */
    public function setBaseModuleDir($baseModuleDir)
    {
        $this->baseModuleDir = $baseModuleDir;
    }

    /**
     * @return mixed
     */
    public function getBaseModuleNamespace()
    {
        return $this->baseModuleNamespace;
    }

    /**
     * @param mixed $baseModuleNamespace
     */
    public function setBaseModuleNamespace($baseModuleNamespace)
    {
        $this->baseModuleNamespace = ltrim($baseModuleNamespace, '\\');
    }

    /**
     * 根据命名空间获取文件目录
     * @param string $moduleNamespace
     * @return mixed|string
     * @throws \Exception
     */
    private function getDirByNamespace($moduleNamespace = '')
    {
        if (empty($moduleNamespace)) {
            $moduleNamespace = $this->getBaseModuleNamespace();
        }

        $subNamespace = $this->getRelativeNamespace($moduleNamespace) . '/modules';

        return Yii::getAlias($this->getBaseModuleDir() . str_replace('\\', '/', $subNamespace));
    }

    /**
     * 获取相对命名空间
     * @param $moduleNamespace
     * @return string
     * @throws \Exception
     */
    private function getRelativeNamespace($moduleNamespace)
    {
        if (($pos = strpos($moduleNamespace, $this->getBaseModuleNamespace())) !== 0) {
            throw new \Exception('ModuleNamespace must under baseModuleNamespace!');
        }

        return substr($moduleNamespace, strlen($this->getBaseModuleNamespace()));
    }

    /**
     * @param ModuleDoc[] $moduleDocs
     * @param $moduleNamespace
     * @return ModuleDoc
     * @throws Exception
     */
    public function findModuleDoc($moduleDocs, $moduleNamespace)
    {
        $moduleIds = explode('\\', trim(str_replace('\modules', '', $this->getRelativeNamespace($moduleNamespace)), '\\'));
        if (empty($moduleIds)) {
            throw new Exception('无效的模块');
        }
        $moduleDoc = null;
        foreach ($moduleIds as $moduleId) {
            if (isset($moduleDocs[$moduleId])) {
                $moduleDoc = $moduleDocs[$moduleId];
                $moduleDocs = $moduleDoc->getModules();
                continue;
            } else {
                throw new Exception(sprintf('没找到模块 [%s]', $moduleNamespace));
            }
        }

        return $moduleDoc;
    }

    /**
     * 获取反射模块
     * @param string $moduleNamespace 模块命名空间 (例：/app/modules)
     * @param ModuleDoc $parentModule 父模块
     * @return ModuleDoc[] 文档注解对象
     * @throws \Exception
     */
    public function getModuleDocs($moduleNamespace = '', $parentModule = null)
    {
        $modules = [];
        if (empty($moduleNamespace)) {
            $moduleNamespace = $this->getBaseModuleNamespace();
        }

        $moduleDir = $this->getDirByNamespace($moduleNamespace);
        if (!is_dir($moduleDir)) {
            return $modules;
        }

        $dirs = scandir($moduleDir);

        foreach ($dirs as $subModuleId) {
            if (strpos($subModuleId, '.') === 0) continue;

            $subModuleNamespace = $moduleNamespace . '\\modules\\' . $subModuleId;
            $rc = new \ReflectionClass($subModuleNamespace . '\Module');
            $moduleDoc = new ModuleDoc($rc);
            $moduleDoc->setId($subModuleId);
            $moduleDoc->moduleDoc = $parentModule;
            if ($parentModule) {
                $moduleDoc->setRoute($parentModule->getRoute() . '/' . $subModuleId);
            }else{
                $moduleDoc->setRoute($subModuleId);
            }
            //检测是否开启
            if (!$moduleDoc->getEnable()) continue;

            if (empty($moduleDoc->getName())) throw new Yii\base\Exception(sprintf('模块[%s]缺少注解属性name', $subModuleId));

            $moduleDoc->setControllers($this->getControllerDocs($subModuleNamespace, $moduleDoc));

            $subModuleDir = $this->getDirByNamespace($subModuleNamespace);
            if (is_dir($subModuleDir)) {
                $moduleDoc->setModules($this->getModuleDocs($subModuleNamespace, $moduleDoc));
            }

            $modules[$subModuleId] = $moduleDoc;
        }

        return $modules;
    }


    /**
     * 获取模块下的控制器注解
     * @param $moduleNamespace
     * @param ModuleDoc $parentModule 父模块
     * @return array
     */
    protected function getControllerDocs($moduleNamespace, $parentModule)
    {
        $controllers = [];
        $controllerDir = substr($this->getDirByNamespace($moduleNamespace), 0, -8) . DIRECTORY_SEPARATOR . 'controllers';
        if (!is_dir($controllerDir)) {
            return $controllers;
        }
        $dirs = scandir($controllerDir);
        foreach ($dirs as $d) {
            if (preg_match('/^\..*/', $d)) continue;

            $actions = [];
            $controllerId = Inflector::camel2id(substr($d, 0, -14), '-', true);
            $class = $moduleNamespace . '\\controllers\\' . substr($d, 0, -4);
            $rc = new \ReflectionClass($class);
            $controllerDoc = new ControllerDoc($rc);
            $controllerDoc->setId($controllerId);
            $controllerDoc->moduleDoc = $parentModule;
            $controllerDoc->setRoute($parentModule->getRoute() . '/' . $controllerId);
            if (!$controllerDoc->getEnable()) continue;

            //Actions
            $rm = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($rm as $m) {
                $name = $m->getName();
                if (!strncasecmp($name, 'action', 6) && $name != 'actions') {
                    $actionId = Inflector::camel2id(substr($name, 6), '-', true);
                    $actionDoc = new ActionDoc($m);
                    if(!$actionDoc->getEnable()) continue;
                    $actionDoc->setId($actionId);
                    $actionDoc->controllerDoc = $controllerDoc;
                    $actionDoc->setRoute($controllerDoc->getRoute() . '/' . $actionId);
                    $actions[$actionId] = $actionDoc;
                }
            }
            $controllerDoc->setActions($actions);

            $controllers[$controllerId] = $controllerDoc;
        }

        return $controllers;
    }

    /**
     * 查找actionDoc
     * @param $moduleDocs
     * @param $moduleNamespace
     * @param $controllerId
     * @param $actionId
     * @return ActionDoc
     * @throws Exception
     */
    public function findActionDoc($moduleDocs, $moduleNamespace, $controllerId, $actionId)
    {
        if(empty($moduleNamespace) && !empty($moduleDocs)){
            $moduleDoc = current($moduleDocs);
        }else{
            $moduleDoc = $this->findModuleDoc($moduleDocs, $moduleNamespace);
        }
        
        $controllers = $moduleDoc->getControllers();
        if(empty($controllerId) && !empty($controllers)){
            $controllerDoc = current($controllers);
        }else{
            if(!isset($controllers[$controllerId])){
                throw new Exception(sprintf('controller [%s] 不存在', $controllerId));
            }
            $controllerDoc = $controllers[$controllerId];
        }
        
        $actions = $controllerDoc->getActions();
        if(empty($actionId) && !empty($actions)){
            $actionDoc = current($actions); 
        }else{
            if(!isset($actions[$actionId])){
                throw new Exception(sprintf('action [%s] 不存在', $actionId));
            }
            $actionDoc = $actions[$actionId];
        }
        
        return $actionDoc;
    }
}