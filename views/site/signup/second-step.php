<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $screen array
 * @var $tenantCode string
 * @var $accountType string
 * @var $userType string
 * @var $secretQuestions array
 */

use app\components\RenderTabHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Sign-up second step');
$renderHelper = new RenderTabHelper();
?>

<div class="cf sub-content-wrapper">
    <?= Html::beginForm('', 'post', ['class' => 'registration-main-form']) ?>
        <div class="alert-wrap" style="display: none;">
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <span class="alert-icon"><span class="icon"></span></span>
                <span class="alert-message"></span>
            </div>
        </div>

        <?php if(!empty($secretQuestions)): ?>
            <div class="panel panel-default panel-window" style="width: 100%;">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Yii::t('app', 'Secret questions answer') ?></h3>
                </div>
                <div class="panel-body">
                    <?php foreach($secretQuestions as $item): ?>
                        <div class="form-group">
                            <?= Html::label($item['description']) ?>
                            <?= Html::textInput("_secretQuestions[{$item['list_name']}.{$item['entry_name']}]", null, ['class' => 'form-control', 'required' => true]) ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>

        <?= $renderHelper->render($screen, 'insert') ?>

        <?= Html::hiddenInput('tenant_code', $tenantCode) ?>
        <?= Html::hiddenInput('account_type', $accountType) ?>
        <?= Html::hiddenInput('account_security_type', $userType) ?>

        <?= Html::submitInput(Yii::t('app', 'Submit'), ['name' => '_registration_data', 'class' => 'btn btn-primary pull-right']) ?>
    <?= Html::endForm() ?>
</div>


