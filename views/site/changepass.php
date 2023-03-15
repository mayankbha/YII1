<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $model \app\models\forms\ChangePasswordForm
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\password\PasswordInput;

$this->title = Yii::t('app', 'Change password');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="cf sub-content-wrapper">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <h1><?= $this->title ?></h1>
            <?php \app\components\ThemeHelper::printFlashes(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="panel-default panel-window panel-settings">
                <?php $form = ActiveForm::begin() ?>
                <?= $form->field($model, 'oldPassword')->input('password'); ?>
                <?= $form->field($model, 'password')->widget(PasswordInput::class, ['options' => ['value' => '']]) ?>
                <?= $form->field($model, 'repeat')->passwordInput() ?>
            </div>
            <br/>
            <div class="form-group pull-right">
                <?= Html::a(Yii::t('app', 'Back'), Url::toRoute('index'), ['class' => 'btn btn-link']); ?>
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']); ?>
            </div>
            <?php ActiveForm::end() ?>
        </div>
    </div>
</div>