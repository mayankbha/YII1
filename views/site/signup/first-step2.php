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
	@import url(http://fonts.googleapis.com/css?family=Roboto:400);

	.custom_container {
		padding: 25px;
		position: fixed;
		margin-top: 70px;
	}

	.form-login {
		background-color: <?php echo $sign_up_style['background_color']; ?>;
		padding-top: 10px;
		padding-bottom: 20px;
		padding-left: 20px;
		padding-right: 20px;
		border-radius: 15px;
		border-color:#d2d2d2;
		border-width: 5px;
		box-shadow:0 1px 0 #cfcfcf;
	}

	.btn {
		background-color: <?php //echo $sign_up_style['background_color']; ?>;
		border-color: <?php echo $sign_up_style['border_color']; ?>;
		color: <?php echo $sign_up_style['text_color']; ?>;
		font-size: <?php //echo $sign_up_style['sign_up_btn_font_size']; ?>;
	}

	.form-control { border-color: <?php echo $sign_up_style['field_border_color']; ?>; }
	.form-control:focus { border-color : <?php echo $sign_up_style['field_border_selected_color']; ?> !important; }
	.has-success .form-control { border-color: <?php //echo $sign_up_style['sign_up_field_success_border_color']; ?>; }
	.has-error .form-control { border-color: <?php //echo $sign_up_style['sign_up_field_error_border_color']; ?>; }

	h4 { 
		border:0 solid #fff; 
		border-bottom-width:1px;
		padding-bottom:10px;
		text-align: center;
	}

	.form-control {
		border-radius: 10px;
	}

	.wrapper {
		text-align: center;
	}
</style>

<div class="container custom_container">
    <div class="row">
        <div class="col-md-12 col-md-12">
			<?= Html::beginForm(Url::current(), 'post', ['class' => 'form-horizontal']) ?>
				<div class="form-login">
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

					<div class="wrapper">
						<span class="group-btn">
							<?= Html::submitButton(Yii::t('app', 'Next step'), ['class' => 'btn sign-up-first-step-submit', 'name' => 'login-button']) ?>
						</span>
					</div>
				</div>
			<?= Html::endForm() ?>
        </div>
    </div>
</div>
