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
	.btn {
		background-color: <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>;
		border-color: <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>;
		color: <?php echo Yii::$app->params['loginConfig']['body_font_color']; ?>;
		font-size: <?php echo Yii::$app->params['loginConfig']['body_font_size']; ?>;
	}

	.form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_border_color']; ?>; }
	.has-success .form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_success_border_color']; ?>; }
	.has-error .form-control { border-color: <?php echo Yii::$app->params['loginConfig']['field_error_border_color']; ?>; }
	.login-form { background: url("<?php echo Yii::$app->params['loginConfig']['login1_background_image']; ?>") no-repeat center top; }
</style>

<div class="col-sm-12 col-md-6 col-md-offset-3">
    <div class="login-form">
        <div class="form-horizontal">
            <?= \app\components\ThemeHelper::printFlashes(); ?>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
				<div class="form-group" style="margin-bottom: 4px;">
					<div class="col-sm-12">
						<?= $form->field($model, 'username', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->input('username', ['placeholder' => Yii::t('app', 'User name')])->label(false); ?>
					</div>
				</div>

				<div class="form-group" style="margin-bottom: 6px;">
					<div class="col-sm-12">
						<?= $form->field($model, 'password', ['options'=> [], 'wrapperOptions' => ['class' => 'form-control']])->passwordInput(['class' => 'form-control allow_to_submit_by_enter', 'placeholder' => Yii::t('app', 'Password')])->label(false); ?>
					</div>
				</div>

				<div class="form-submit text-center">
					<?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary', 'name' => 'login-button']); ?>
				</div>
            <?php ActiveForm::end(); ?>

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
			<?php } ?>
        </div>
    </div>
</div>
