<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $model \app\models\forms\CheckAuthForm
 * @var $question string|boolean
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Authorization with secret question');
?>

<div class="col-sm-12 col-md-6 col-md-offset-3">
    <div class="middle-form">
        <div class="middle-form-wrapper">
            <h2><?= $this->title ?></h2>
            <?= \app\components\ThemeHelper::printFlashes() ?>
            <br />
            <?php $form = ActiveForm::begin() ?>
                <?= $form->field($model, 'confirmation_code')->textInput()->label($question) ?>
                <div class="form-submit text-right">
                    <?= Html::submitButton(Yii::t('app', 'Confirm'), ['class' => 'btn btn-primary']) ?>
                </div>
            <?php ActiveForm::end() ?>
        </div>
    </div>
</div>