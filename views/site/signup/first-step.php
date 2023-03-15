<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $tenantList array
 * @var $accountTypeList array
 * @var $userTypeList array
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Sign-up');
?>

<div class="col-sm-12 col-md-6 col-md-offset-3">
    <div class="login-form">
        <?= Html::beginForm(Url::current(), 'post', ['class' => 'form-horizontal']) ?>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Tenant'), null, ['class' => 'control-label']) ?>
                <?= Html::dropDownList('tenant', null, $tenantList, ['class' => 'form-control sign-up-tenant', 'required' => true]); ?>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Account type'), null, ['class' => 'control-label']) ?>
                <?= Html::dropDownList('account_type', null, $accountTypeList, ['class' => 'form-control sign-up-account-type', 'required' => true]); ?>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'User type'), null, ['class' => 'control-label']) ?>
                <?= Html::dropDownList('user_type', null, $userTypeList, ['class' => 'form-control sign-up-user-type', 'required' => true]); ?>
            </div>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Next step'), ['class' => 'btn btn-primary sign-up-first-step-submit']) ?>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>
