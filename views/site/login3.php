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

<style>
	@import url(http://fonts.googleapis.com/css?family=Roboto:400);

	.custom_container {
		padding: 25px;
		position: fixed;
		margin-top: 100px;
	}

	.form-login {
		background-color: <?php echo Yii::$app->params['loginConfig']['header_background_color']; ?>;
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
		background-color: <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>;
		border-color: <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>;
		color: <?php echo Yii::$app->params['loginConfig']['body_font_color']; ?>;
		font-size: <?php echo Yii::$app->params['loginConfig']['body_font_size']; ?>;
	}

	.form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_border_color']; ?>; }
	.has-success .form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_success_border_color']; ?>; }
	.has-error .form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_error_border_color']; ?>; }

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

<?= \app\components\ThemeHelper::printFlashes() ?>

<div class="container custom_container">
    <div class="row">
        <div class="col-md-12 col-md-12">
			<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
				<div class="form-login">
					<h4>Welcome back.</h4>

					<?= $form->field($model, 'username', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->input('username', ['placeholder' => Yii::t('app', 'User name')])->label(false) ?></br>

					<?= $form->field($model, 'password', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->passwordInput(['class' => 'form-control allow_to_submit_by_enter', 'placeholder' => Yii::t('app', 'Password')])->label(false) ?></br>

					<div class="wrapper">
						<span class="group-btn">
							<?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn', 'name' => 'login-button']) ?>
						</span>
					</div>

					<?php if(Yii::$app->params['loginConfig']['forgotPassword']) { ?>
						<div class="etc-login-form">
							<p><a href="<?=Url::toRoute('forgot-password')?>" class="password-link"><small><?= $password_text; ?></small></a></p>
						</div>
					<?php } ?>
				</div>
			<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
