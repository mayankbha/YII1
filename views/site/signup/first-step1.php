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

//echo "<pre>"; print_r($sign_up_style); die;

?>

<style>
	.btn {
		background-color: <?php echo $sign_up_style['background_color']; ?>;
		border-color: <?php echo $sign_up_style['border_color']; ?>;
		color: <?php //echo $sign_up_style['sign_up_btn_font_color']; ?>;
		font-size: <?php //echo $sign_up_style['sign_up_btn_font_size']; ?>;
	}

	.form-control { border-color: <?php echo $sign_up_style['field_border_color']; ?>; }
	.has-success .form-control { border-color: <?php //echo $sign_up_style['field_border_color']; ?>; }
	.has-error .form-control { border-color: <?php //echo $sign_up_style['sign_up_field_error_border_color']; ?>; }
	.login-form { background: url("<?php echo Yii::$app->params['loginConfig']['sign_up1_background_image']; ?>") no-repeat center top; }
</style>

<div class="col-sm-12 col-md-6 col-md-offset-3">
    <div class="login-form">
		<div class="form-horizontal">
			<?= Html::beginForm(Url::current(), 'post', ['class' => 'form-horizontal']) ?>
				<div class="form-group">
					<?= Html::label('Tenant', null, ['class' => 'control-label']) ?>
					<?= Html::dropDownList('tenant', null, $tenantList, ['class' => 'form-control sign-up-tenant', 'required' => true]); ?>
				</div>

				<div class="form-group">
					<?= Html::label('Account type', null, ['class' => 'control-label']) ?>
					<?= Html::dropDownList('account_type', null, $accountTypeList, ['class' => 'form-control sign-up-account-type', 'required' => true]); ?>
				</div>

				<div class="form-group">
					<?= Html::label('User type', null, ['class' => 'control-label']) ?>
					<?= Html::dropDownList('user_type', null, $userTypeList, ['class' => 'form-control sign-up-user-type', 'required' => true]); ?>
				</div>

				<div class="form-submit text-center">
					<?= Html::submitButton('Next step', ['class' => 'btn btn-primary sign-up-first-step-submit']) ?>
				</div>
			<?= Html::endForm() ?>
		</div>
    </div>
</div>
