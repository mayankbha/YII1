<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\forms\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Login');
$password_text = (Yii::$app->params['loginConfig']['forgotPasswordText']) ? Yii::$app->params['loginConfig']['forgotPasswordText'] : Yii::t('app', 'Forgot your password?');
?>

<!-- All the files that are required -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link href='http://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js"></script>

<style>
/*=== 3. Text Outside the Box ===*/
.etc-login-form {
  color: #919191;
  padding: 10px 20px;
}
.etc-login-form p {
  margin-bottom: 5px;
}
/*=== 4. Main Form ===*/
.login-form-1 {
  border-radius: 5px;
}
.main-login-form {
  position: relative;
}
.login-form-1 .form-control {
  border: 0;
  box-shadow: 0 0 0;
  border-radius: 0;
  background: transparent;
  color: #555555;
  padding: 7px 0;
  font-weight: bold;
  height:auto;
}
.login-form-1 .form-control::-webkit-input-placeholder {
  color: #999999;
}
.login-form-1 .form-control:-moz-placeholder,
.login-form-1 .form-control::-moz-placeholder,
.login-form-1 .form-control:-ms-input-placeholder {
  color: #999999;
}
.login-form-1 .form-group {
  margin-bottom: 0;
  border-bottom: 2px solid <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>;
  padding-right: 20px;
  position: relative;
}
.login-form-1 .form-group:last-child {
  border-bottom: 0;
}
.login-group {
  background: #ffffff;
  color: #999999;
  border-radius: 8px;
  padding: 10px 20px;
  height: 250px;
}
.login-group-checkbox {
  padding: 5px 0;
}
/*=== 5. Login Button ===*/
.login-form-1 .login-button {
  position: absolute;
  right: -25px;
  top: 50%;
  background: #ffffff;
  color: #999999;
  padding: 11px 0;
  width: 50px;
  height: 50px;
  margin-top: -25px;
  border: 5px solid #efefef;
  border-radius: 50%;
  transition: all ease-in-out 500ms;
}
.login-form-1 .login-button:hover {
  color: #555555;
  transform: rotate(450deg);
}
.login-form-1 .login-button.clicked {
  color: #555555;
}
.login-form-1 .login-button.clicked:hover {
  transform: none;
}
.login-form-1 .login-button.clicked.success {
  color: #2ecc71;
}
.login-form-1 .login-button.clicked.error {
  color: #e74c3c;
}
/*=== 6. Form Invalid ===*/
label.form-invalid {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 5;
  display: block;
  margin-top: -25px;
  padding: 7px 9px;
  background: #777777;
  color: #ffffff;
  border-radius: 5px;
  font-weight: bold;
  font-size: 11px;
}
label.form-invalid:after {
  top: 100%;
  right: 10px;
  border: solid transparent;
  content: " ";
  height: 0;
  width: 0;
  position: absolute;
  pointer-events: none;
  border-color: transparent;
  border-top-color: #777777;
  border-width: 6px;
}
/*=== 7. Form - Main Message ===*/
.login-form-main-message {
  background: #ffffff;
  color: #999999;
  border-left: 3px solid transparent;
  border-radius: 3px;
  margin-bottom: 8px;
  font-weight: bold;
  height: 0;
  padding: 0 20px 0 17px;
  opacity: 0;
  transition: all ease-in-out 200ms;
}
.login-form-main-message.show {
  height: auto;
  opacity: 1;
  padding: 10px 20px 10px 17px;
}
.login-form-main-message.success {
  border-left-color: #2ecc71;
}
.login-form-main-message.error {
  border-left-color: #e74c3c;
}

.logo {
  padding: 15px 0;
  font-size: 25px;
  color: #aaaaaa;
  font-weight: bold;
}
</style>

<?= \app\components\ThemeHelper::printFlashes(); ?>

<div class="text-center" style="margin-top: 80px;">
	<!-- Main Form -->
	<div class="login-form-1">
		<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
			<div class="login-form-main-message"></div>

			<div class="main-login-form">
				<div class="login-group">
					<div class="form-group" style="margin-top: 30px;">
						<label for="lg_username" class="sr-only">Username</label>
						<?= $form->field($model, 'username', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->input('username', ['placeholder' => Yii::t('app', 'User name')])->label(false) ?>
					</div>

					<div class="form-group" style="margin-top: 30px;">
						<label for="lg_password" class="sr-only">Password</label>
						<?= $form->field($model, 'password', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->passwordInput(['class' => 'form-control allow_to_submit_by_enter', 'placeholder' => Yii::t('app', 'Password')])->label(false) ?>
					</div>

					<div class="form-group login-group-checkbox" style="margin-top: 0px;"></div>
				</div>

				<button type="submit" name="login-button" class="login-button"><i class="fa fa-chevron-right"></i></button>
			</div>

			<?php if(Yii::$app->params['loginConfig']['forgotPassword']) { ?>
				<div class="etc-login-form">
					<p><a href="<?=Url::toRoute('forgot-password')?>" class="password-link"><small><?= $password_text; ?></small></a></p>
				</div>
			<?php } ?>
		<?php ActiveForm::end(); ?>
	</div>
	<!-- end:Main Form -->
</div>

<script>
	(function($) {
		"use strict";

		var options = {
						'btn-loading': '<i class="fa fa-spinner fa-pulse"></i>',
						'btn-success': '<i class="fa fa-check"></i>',
						'btn-error': '<i class="fa fa-remove"></i>',
						'msg-success': 'All Good! Redirecting...',
						'msg-error': 'Wrong login credentials!',
						'useAJAX': true,
					};

		// Login Form
		//----------------------------------------------
		// Validation
		$("#login-form").validate({
			rules: {
				lg_username: "required",
				lg_password: "required",
			},
			errorClass: "form-invalid"
		});
	})(jQuery);
</script>
