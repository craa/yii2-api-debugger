<?php
/**
 *
 * Author: chenhongwei <chenhw@mysoft.com.cn>
 * Time: 2016/7/22 15:16
 */
use yii\helpers\Html;

/**
 * @var \craa\ApiDebugger\models\ModuleDoc $moduleDoc
 * @var \craa\ApiDebugger\models\ActionDoc $actionDoc
 */
?>
<div class="panel-group" id="accordion-<?= $moduleDoc->getReflection()->getName() ?>">
    <?php foreach ($moduleDoc->getControllers() as $cd): ?>
        <div class="panel panel-success">
            <div class="panel-heading" style="cursor: pointer;" data-toggle="collapse"
                 data-parent="#accordion-<?= $moduleDoc->getReflection()->getName() ?>"
                 href="#collapse<?= str_replace('\\', '-', $cd->getReflection()->getName()) ?>">
                <span href="#" class="accordion-toggle"
                   style="text-decoration:none;display: block;outline: none;"
                   title="<?= '【controller】' . $cd->getReflection()->getName() ?>"><strong class="text-info pull-right">C</strong> <?= $cd->getName() ?></span>
            </div>
            <div id="collapse<?= str_replace('\\', '-', $cd->getReflection()->getName()) ?>"
                 class="list-group panel-collapse collapse <?= strpos($actionDoc->getRoute(), $cd->getRoute()) === 0 ? 'in' : '' ?>">
                <?php foreach ($cd->getActions() as $ad): ?>
                    <?php echo Html::a($ad->getName(),
                        ['', 'module' => $cd->moduleDoc->getNamespace(), 'controllerId' => $cd->getId(), 'actionId' => $ad->getId()],
                        ['title' => '【action】' . $ad->getReflection()->getName(), 'id' => 'collapse' . $ad->getNamespace(), 'class' => 'list-group-item' . ($ad->getRoute() == $actionDoc->getRoute() ? ' active' : '')]) ?>
                <?php endforeach ?>
            </div>
        </div>
    <?php endforeach ?>
    <?php foreach ($moduleDoc->getModules() as $m): ?>
        <div class="panel panel-warning">
            <div class="panel-heading" style="cursor: pointer;" data-toggle="collapse"
                 data-parent="#accordion-<?= $moduleDoc->getReflection()->getName() ?>"
                 href="#collapse<?= str_replace('\\', '-', $m->getReflection()->getName()) ?>">
                <span href="#" class="accordion-toggle"
                   style="text-decoration:none;display: block;outline: none;"
                   title="<?= '【module】' . $m->getReflection()->getName() ?>"><strong class="text-danger pull-right">M</strong> <?= $m->getName() ?></span>
            </div>
            <div id="collapse<?= str_replace('\\', '-', $m->getReflection()->getName()) ?>"
                 class="list-group panel-collapse collapse <?= strpos($actionDoc->getRoute(), $m->getRoute()) === 0 ? 'in' : '' ?>">
                <?= $this->renderAjax('_left_menu', ['moduleDoc' => $m, 'actionDoc' => $actionDoc]); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
