<?php
use \yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \craa\ApiDebugger\models\ModuleDoc[] $moduleDocs 所有模块
 * @var \craa\ApiDebugger\models\ActionDoc $actionDoc 当前action
 * @var \craa\ApiDebugger\Module $apiDebugger API调试模块
 */

$this->title = $apiDebugger->name;

?>

<div class="row-fluid">
    <div class="row">
        <!-- 顶部导航 -->
        <nav role="navigation" class="navbar navbar-default">
            <div class="navbar-header">
                <button data-target="#navbar-collapse" data-toggle="collapse" class="navbar-toggle"
                        type="button">
                    <span class="sr-only">切换导航</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="<?= \yii\helpers\Url::to(['']) ?>" class="navbar-brand"><?= $apiDebugger->name ?></a>
            </div>
            <div id="navbar-collapse" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><span>&nbsp;&nbsp;&nbsp;&nbsp;</span></li>
                    <?php foreach ($moduleDocs as $moduleDoc): ?>
                        <?= Html::tag('li', Html::a($moduleDoc->getName(), ['', 'module' => $moduleDoc->getNamespace()]), ['class' => $actionDoc->getRootModule()->getId() == $moduleDoc->getId() ? 'active' : '']) ?>
                    <?php endforeach ?>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <?php foreach ($apiDebugger->links as $link): ?>
                        <?= Html::tag('li', Html::a($link['text'], $link['url'], $link['options'])) ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-2">
            <!-- 左侧菜单 -->
            <?= $this->render('@craa/ApiDebugger/views/default/_left_menu', ['moduleDoc' => $actionDoc->getRootModule(), 'actionDoc' => $actionDoc]); ?>
        </div>

        <div id="outputPanel" class="col-md-5">
            <!-- 调试输入 -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">接口调试</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($actionDoc)): ?>
                        未选择接口或获取参数失败
                    <?php else: ?>
                        <form id="invokeForm" class="form-horizontal" role="form"
                              method="<?= $actionDoc->getMethod() ?>"
                              action="<?= \Yii::$app->urlManager->createAbsoluteUrl($actionDoc->getRoute()) ?>">
                            <div class="form-group">
                                <label for="loginForm-platform" class="col-sm-2 control-label">平台</label>

                                <div class="col-sm-9">
                                    <select class="form-control"
                                            name="c_platform"
                                            id="loginForm-platform">
                                        <?php foreach ($apiDebugger->platforms as $i => $platform): ?>
                                            <option
                                                value="<?= $platform ?>" <?= $i === 0 ? 'selected' : '' ?>><?= $platform ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="loginForm-version" class="col-sm-2 control-label">版本号</label>

                                <div class="col-sm-9">
                                    <select class="form-control"
                                            name="c_version"
                                            id="loginForm-version">
                                        <?php foreach ($apiDebugger->versions as $i => $version): ?>
                                            <option
                                                value="<?= $version ?>" <?= $i === 0 ? 'selected' : '' ?>><?= $version ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php foreach ($actionDoc->getParam() as $i => $param): ?>
                                <div class="form-group">
                                    <?= Html::label($param->getBrief(), "param-{$i}-{$param->getName()}", ['class' => 'col-sm-2 control-label']) ?>
                                    <div class="col-sm-9">
                                        <?php
                                        if ($param->getType() == '-file-') {
                                            echo Html::fileInput($param->getName(), $param->getDefault(),
                                                ['id' => "param-{$i}-{$param->getName()}"]);
                                        } else {
                                            echo Html::textInput($param->getName(), $param->getDefault(),
                                                ['class' => 'form-control', 'id' => "param-{$i}-{$param->getName()}", 'placeholder' => $param->getType() . ' ' . $param->getName()]);
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                            <?= Html::hiddenInput('c_method', $actionDoc->getMethod()) ?>
                            <?= Html::hiddenInput('c_interface', \Yii::$app->urlManager->createAbsoluteUrl($actionDoc->getRoute())) ?>
                            <?= Html::hiddenInput('sign', '', ['id' => 'signInput']) ?>
                            <?= Html::hiddenInput('c_nonce', time(), ['id' => 'nonceInput']) ?>
                            <?= Html::hiddenInput('c_identity', 'api-debugger') ?>
                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-10">
                                    <button type="button" class="btn btn-danger" id="invokeBtn">调用接口</button>
                                </div>
                            </div>
                        </form>
                    <?php endif ?>
                </div>
            </div>
            <!-- 调试输出 -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <ul class="nav nav-pills">
                        <li class="active"><a href="#result" data-toggle="tab">输出结果</a></li>
                        <!--                        <li><a href="#thrift" data-toggle="tab">DEBUG输出</a></li>-->
                        <li style="float:right;">
                            <a id="outputExpand" href="javascript:;" style="color:gray">展开&gt;&gt;</a>
                            <a id="outputCollapse" href="javascript:;" style="color:gray;display: none">&lt;&lt;缩回</a>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="result">未调用或调用失败</div>
                        <div class="tab-pane" id="thrift">未调用或调用失败</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 接口描述 -->
        <div id="descPanel" class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">接口说明</h3>
                </div>
                <div class="panel-body">
                    <p><span style="display: inline-block;padding-right: 5px;font-weight: bold;">调用地址:</span>
                        <span
                            class="text-success"><?= \Yii::$app->urlManager->createUrl($actionDoc->getRoute()) ?></span>
                    </p>

                    <p><span
                            style="display: inline-block;padding-right: 5px;font-weight: bold;">调用方法:</span><span
                            class="text"><?= $actionDoc->getMethod() ?></span>
                    </p>

                    <p><span
                            style="display: inline-block;padding-right: 5px;font-weight: bold;">支持版本:</span><span
                            class="text-success"><?= $actionDoc->getVersion() ?></span>
                    </p>

                    <p><span
                            style="display: inline-block;padding-right: 5px;font-weight: bold;">接口功能:</span><span
                            class="text"><?= $actionDoc->getName() ?></span>
                    </p>

                    <p><span
                            style="display: inline-block;padding-right: 5px;font-weight: bold;">接口详述:</span><span
                            class="text"><?= $actionDoc->getFunction() ?></span>
                    </p>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">调用参数说明</h3>
                </div>
                <div class="panel-body">
                    <?= nl2br($actionDoc->getParamDescription()) ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">返回值说明</h3>
                </div>
                <div class="panel-body">
                    <?= $actionDoc->getReturn() ? '<pre>' . $actionDoc->getReturn() . '</pre>' : '' ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="font-weight: bold;color:red">异常说明</h3>
                </div>
                <div class="panel-body">
                    <?= nl2br($actionDoc->getException()) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        //展开
        $('#outputExpand').click(function () {
            $('#outputPanel').addClass('col-md-10').removeClass('col-md-5');
            $('#descPanel').hide();
            $(this).hide();
            $('#outputCollapse').show();
        });
        //收起
        $('#outputCollapse').click(function () {
            $('#outputPanel').addClass('col-md-5').removeClass('col-md-10');
            $('#descPanel').show();
            $(this).hide();
            $('#outputExpand').show();
        });

        var folder = '<?=$apiDebugger->getAssetsUrl()?>';

        //调试
        $('#invokeBtn').click(function (e) {
            e.preventDefault();

            //提交表单
            $('#invokeForm').ajaxSubmit({
                success: function (resp) {
                    try {
                        if (typeof resp == "string") {
                            JSON.parse(resp);
                        }
                        new JsonFormater({
                            dom: '#result',
                            imgCollapsed: folder + "/jsonFormater/images/Collapsed.gif",
                            imgExpanded: folder + "/jsonFormater/images/Expanded.gif"
                        }).doFormat(resp);
                    } catch (err) {
                        $("#result").html(resp);
                    }
                },
                error: function (xhr) {
                    console.log(xhr);
                    var warn = $('<div class="alert alert-danger" role="alert"><strong>' + xhr.status + '</strong>, '
                        + xhr.statusText + '</div><div>' + xhr.responseText + '</div>');
                    $("#result").html(warn);
                }
            });

        });

    });
</script>