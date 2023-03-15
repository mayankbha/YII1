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
	.account-wall { background-color: <?php echo $sign_up_style['background_color']; ?>; }

	.form-signin
	{
		max-width: 330px;
		padding: 15px;
		margin: 0 auto;
	}
	.form-signin .form-signin-heading, .form-signin .checkbox
	{
		margin-bottom: 10px;
	}
	.form-signin .checkbox
	{
		font-weight: normal;
	}
	.form-signin .form-control
	{
		position: relative;
		font-size: 16px;
		height: auto;
		padding: 10px;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	.form-signin .form-control:focus
	{
		z-index: 2;
	}
	.form-signin input[type="text"]
	{
		margin-bottom: -1px;
		border-bottom-left-radius: 0;
		border-bottom-right-radius: 0;
	}
	.form-signin input[type="password"]
	{
		margin-bottom: 10px;
		border-top-left-radius: 0;
		border-top-right-radius: 0;
	}
	.account-wall
	{
		margin-top: 20px;
		padding: 40px 0px 20px 0px;
		background-color: #f7f7f7;
		-moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
		-webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
		box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	}
	.profile-img
	{
		width: 96px;
		height: 96px;
		margin: 0 auto 10px;
		display: block;
		-moz-border-radius: 50%;
		-webkit-border-radius: 50%;
		border-radius: 50%;
	}

	.btn {
		background-color: <?php //echo $sign_up_style['background_color']; ?>;
		border-color: <?php echo $sign_up_style['border_color']; ?>;
		color: <?php echo $sign_up_style['text_color']; ?>;
		font-size: <?php //echo Yii::$app->params['loginConfig']['body_font_size']; ?>;
	}

	.form-control { border-color: <?php echo $sign_up_style['field_border_color']; ?>; }
	.form-control:focus { border-color : <?php echo $sign_up_style['field_border_selected_color']; ?> !important; }
	.has-success .form-control { border-color: <?php //echo Yii::$app->params['loginConfig']['field_success_border_color']; ?>; }
	.has-error .form-control { border-color: <?php //echo Yii::$app->params['loginConfig']['field_error_border_color']; ?>; }
</style>

<div class="container" style="margin-top: 50px;">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">
			<div class="account-wall">
                <img class="profile-img" src="<?php echo Url::toRoute(Yii::$app->params['loginConfig']['sign_up3_background_image'], null); ?>" />

				<?= Html::beginForm(Url::current(), 'post') ?>
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

					<?= Html::submitButton(Yii::t('app', 'Next step'), ['class' => 'btn btn-block btn-primary sign-up-first-step-submit', 'name' => 'login-button']) ?>
				<?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
