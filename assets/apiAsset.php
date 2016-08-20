<?php
/**
 * Created by PhpStorm.
 * User: chenhongwei
 * Date: 2015/5/25
 * Time: 16:35
 */
namespace craa\ApiDebugger\assets;

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