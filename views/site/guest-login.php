<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $model LoginForm
 */

use app\components\ThemeHelper;
use app\models\forms\LoginForm;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Guest login');
?>
<div class="col-sm-12 col-md-6 col-md-offset-3">
    <div class="login-form">
        <div class="form-horizontal">
            <?php ThemeHelper::printFlashes(); ?>
            <?php $form = ActiveForm::begin(['action' => ['/login']]); ?>
                <?= Html::activeHiddenInput($model, 'username') ?>
                <?= Html::activeHiddenInput($model, 'password') ?>

                <?= Html::submitButton(Yii::t('app', 'Guest login'), ['class' => 'btn btn-primary']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>