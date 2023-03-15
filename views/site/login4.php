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
	.account-wall { background-color: <?php echo Yii::$app->params['loginConfig']['header_background_color']; ?>; }

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
		background-color: <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>;
		border-color: <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>;
		color: <?php echo Yii::$app->params['loginConfig']['body_font_color']; ?>;
		font-size: <?php echo Yii::$app->params['loginConfig']['body_font_size']; ?>;
	}

	.form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_border_color']; ?>; }
	.has-success .form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_success_border_color']; ?>; }
	.has-error .form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_error_border_color']; ?>; }
</style>

<?= \app\components\ThemeHelper::printFlashes() ?>

<div class="container" style="margin-top: 100px;">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4 login-ml">
            <div class="account-wall">
                <img class="profile-img" src="<?php echo Url::toRoute(Yii::$app->params['loginConfig']['login4_background_image'], null); ?>" />

                <?php $form = ActiveForm::begin(['id' => 'login-form', 'class' => 'form-signin']); ?>
					<?= $form->field($model, 'username', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->input('username', ['placeholder' => Yii::t('app', 'User name')])->label(false) ?>

					<?= $form->field($model, 'password', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->passwordInput(['class' => 'form-control allow_to_submit_by_enter', 'placeholder' => Yii::t('app', 'Password')])->label(false) ?>

					<?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-lg btn-block', 'name' => 'login-button']) ?>

                    <?php if(Yii::$app->params['loginConfig']['forgotPassword']) { ?>
                        <?php if(!empty(Yii::$app->params['guestUserMode'])): ?>
                            <div class="pull-left" style="margin-top: 10px;">
                                <a href="<?=Url::toRoute('guest-login')?>">
                                    <small><?= Yii::t('app', 'Guest login') ?></small>
                                </a>
                            </div>
                        <?php endif ?>
                        <div class="pull-right" style="margin-top: 10px;">
                            <a href="<?=Url::toRoute('forgot-password')?>" class="password-link">
                                <small><?= $password_text; ?></small>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    <?php } ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
