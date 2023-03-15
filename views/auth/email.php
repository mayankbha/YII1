<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $model \app\models\forms\CheckAuthForm
 * @var $isSent boolean
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Email Authorization');
?>

<div class="col-sm-12 col-md-6 col-md-offset-3">
    <div class="middle-form">
        <div class="middle-form-wrapper">
            <h2><?= $this->title ?> <small>(<?= Yii::t('app', 'Confirmation required') ?>)</small></h2>
            <?= \app\components\ThemeHelper::printFlashes() ?>
            <?php if ($isSent): ?>
                <p><?= Yii::t('app', 'Please check your email for a message with your code') ?></p>
                <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($model, 'confirmation_code')->textInput() ?>
                    <div class="form-submit text-right">
                        <?= Html::submitButton(Yii::t('app', 'Confirm'), ['class' => 'btn btn-primary']) ?>
                    </div>
                <?php ActiveForm::end() ?>
            <?php else: ?>
                <p><?= Yii::t('app', 'We will now send you a free text message with an activation code to your email') ?></p>
                <?= Html::a(Yii::t('app', 'Get code'), Url::current(),['data-method' => 'post', 'class' => 'btn btn-primary']) ?>
            <?php endif ?>
        </div>
    </div>
</div>