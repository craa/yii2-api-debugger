<?php
namespace craa\ApiDebugger;

class Module extends \yii\base\Module
{
    /**
     * @var string 调试器名称，显示在左上角
     */
    public $name = 'API-Debugger';

    /**
     * @var string the password that can be used to access apiModule.
     * If this property is set false, then apiModule can be accessed without password
     * (DO NOT DO THIS UNLESS YOU KNOW THE CONSEQUENCE!!!)
     */
    public $password = '123';

    /**
     * @var array the IP filters that specify which IP addresses are allowed to access apiModule.
     * Each array element represents a single filter. A filter can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * If you want to allow all IPs to access api, you may set this property to be false
     * (DO NOT DO THIS UNLESS YOU KNOW THE CONSEQUENCE!!!)
     * The default value is array('127.0.0.1', '::1'), which means apiModule can only be accessed
     * on the localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];

    /**
     * The view to be rendered can be specified in one of the following formats:
     *
     * - path alias (e.g. "@app/views/site/index");
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of [[module]].
     * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
     *
     * To determine which layout should be applied, the following two steps are conducted:
     *
     * 1. In the first step, it determines the layout name and the context module:
     *
     * - If [[layout]] is specified as a string, use it as the layout name and [[module]] as the context module;
     * - If [[layout]] is null, search through all ancestor modules of this controller and find the first
     *   module whose [[Module::layout|layout]] is not null. The layout and the corresponding module
     *   are used as the layout name and the context module, respectively. If such a module is not found
     *   or the corresponding layout is not a string, it will return false, meaning no applicable layout.
     *
     * 2. In the second step, it determines the actual layout file according to the previously found layout name
     *    and context module. The layout name can be:
     *
     * - a path alias (e.g. "@app/views/layouts/main");
     * - an absolute path (e.g. "/main"): the layout name starts with a slash. The actual layout file will be
     *   looked for under the [[Application::layoutPath|layout path]] of the application;
     * - a relative path (e.g. "main"): the actual layout file will be looked for under the
     *   [[Module::layoutPath|layout path]] of the context module.
     *
     * If the layout name does not contain a file extension, it will use the default one `.php`.
     *
     * @var string $view the view name.
     */
    public $view = '@craa/ApiDebugger/views/default/index';

    /**
     * @var string $baseModuleDir 根模块所在目录，支持绝对路径和别名
     */
    public $baseModuleDir = '@app';

    /**
     * @var string $baseModuleNamespace 根模块命名空间
     */
    public $baseModuleNamespace = 'app';

    /**
     * 在导航中加入自定义链接
     *
     * [
     *     [
     *         'text' => 'wiki',
     *         'url' => 'http://github.com/craa/yii2-api-debugger',
     *         'options' => [
     *             'target' => '_blank',
     *         ],
     *     ],
     *     ...
     * ]
     *
     * @var array
     * @see \yii\helpers\Html::a()
     */
    public $links = [];

    /**
     * @var array $platforms 平台列表
     */
    public $platforms = ['iOS'];

    /**
     * @var array $versions 版本列表
     */
    public $versions = ['1.0.0'];

    /**
     * @var string $_assetsUrl 资源所在路径
     */
    private $_assetsUrl;

    /**
     * Initializes the api module.
     */
    public function init()
    {
        parent::init();
        \Yii::$app->setComponents([
            'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'craa\ApiDebugger\models\User',
                'loginUrl' => \Yii::$app->urlManager->createUrl($this->id . '/default/login'),
            ],
        ]);
    }

    /**
     * @return string the base URL that contains all published asset files of api.
     */
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl === null)
            $this->_assetsUrl = \Yii::$app->getAssetManager()->publish(__DIR__ . '/assets')[1];
        return $this->_assetsUrl;
    }

    /**
     * @param string $value the base URL that contains all published asset files of api.
     */
    public function setAssetsUrl($value)
    {
        $this->_assetsUrl = $value;
    }

    /**
     * Performs access check to api.
     * This method will check to see if user IP and password are correct if they attempt
     * to access actions other than "default/login" and "default/error".
     * @param \yii\base\Controller $controller the controller to be accessed.
     * @param \yii\base\Action $action the action to be accessed.
     * @return boolean whether the action should be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $route = \Yii::$app->controller->id . '/' . $action->id;
            if (!$this->allowIp(\Yii::$app->request->userIP) && $route !== 'default/error')
                throw new \yii\web\HttpException(403, "You are not allowed to access this page.");

            $publicPages = [
                'default/login',
                'default/error',
            ];
            if ($this->password !== false && \Yii::$app->user->isGuest && !in_array($route, $publicPages))
                \Yii::$app->user->loginRequired();
            else
                return true;
        }
        return false;
    }

    /**
     * Checks to see if the user IP is allowed by {@link allowedIPs}.
     * @param string $ip the user IP
     * @return boolean whether the user IP is allowed by {@link allowedIPs}.
     */
    protected function allowIp($ip)
    {
        if (empty($this->allowedIPs))
            return true;
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos)))
                return true;
        }
        return false;
    }
}