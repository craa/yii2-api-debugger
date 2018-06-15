<?php
namespace craa\ApiDebugger\controllers;

use craa\ApiDebugger\components\DocParseService;
use Yii;

class DefaultController extends \yii\web\Controller
{
    public $layout = 'main';

    /**
     * @brief 登入
     */
    public function actionLogin()
    {
        $model = new \craa\ApiDebugger\models\LoginForm();
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            if ($model->validate() && $model->login())
                $this->redirect(Yii::$app->user->getReturnUrl([$this->module->id.'/default/index']));
        }
        return $this->render('login', array('model' => $model));
    }

    /**
     * @brief 登出
     */
    public function actionLogout()
    {
        Yii::$app->user->logout(false);
        $this->redirect(Yii::$app->urlManager->createUrl('api/default/index'));
    }

    public function actionIndex($module = '', $controllerId = '', $actionId = '')
    {
        $docParseService = new DocParseService($this->module->baseModuleDir, $this->module->baseModuleNamespace);
        $moduleDocs = $docParseService->getModuleDocs();
        if (empty($moduleDocs)) throw new \yii\base\Exception(sprintf('未检测到模块'));
        $actionDoc = $docParseService->findActionDoc($moduleDocs, $module, $controllerId, $actionId);
        return $this->render($this->module->view, [
            'moduleDocs' => $moduleDocs,
            'actionDoc' => $actionDoc,
            'apiDebugger' => $this->module
        ]);
    }

}