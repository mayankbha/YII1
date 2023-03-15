<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Login');
?>
<div class="col-sm-12 col-md-6 col-md-offset-3">
    <div class="login-form">
        <div class="form-horizontal">
            <?php \app\components\ThemeHelper::printFlashes(); ?>
            <?php $form = ActiveForm::begin(['id' => 'forgot-form']); ?>
            <div class="form-group" style="margin-bottom: 4px">
                <div class="col-sm-12">
                    <?= $form->field($model, 'username', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->hiddenInput()->label(false)  ?>
                    <?= $form->field($model, 'code', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->input('text', ['placeholder' => Yii::t('app', 'Code')])->label(false) ?>
                </div>
            </div>

            <div class="form-submit text-center">
                <?= Html::submitButton(Yii::t('app', 'Send new password'), ['class' => 'btn btn-primary', 'name' => 'send-password']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>