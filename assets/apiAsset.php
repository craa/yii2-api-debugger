<?php
/**
 * @link https://github.com/craa/yii2-api-debugger
 */

namespace craa\ApiDebugger\assets;

/**
 * 前端资源
 * Class apiAsset
 *
 * @author Chen Hongwei <crains@qq.com>
 * @since 1.0
 */
class apiAsset extends \yii\web\AssetBundle
{
    public $sourcePath = __DIR__;
    public $css = [
        'bootstrap-3.2.0/dist/css/bootstrap.min.css',
        'jsonFormater/jsonFormater.css',
    ];
    public $js = [
        'js/jquery.min.js',
        'bootstrap-3.2.0/dist/js/bootstrap.min.js',
        'jsonFormater/jsonFormater.js',
        'js/jquery.form.js',
        'js/hashes.js',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];
}