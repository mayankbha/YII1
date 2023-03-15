<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $screen array
 * @var $tenantCode string
 * @var $accountType string
 * @var $userType string
 * @var $secretQuestions array
 */

use app\components\RenderTabHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Sign-up second step');
$renderHelper = new RenderTabHelper();

?>

<div class="cf sub-content-wrapper">
	<div class="account-wall">
		<?= Html::beginForm('', 'post', ['class' => 'registration-main-form form-signin']) ?>
			<div class="alert-wrap" style="display: none;">
				<div class="alert alert-danger alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					<span class="alert-icon"><span class="icon"></span></span>
					<span class="alert-message"></span>
				</div>
			</div>

			<?php if(!empty($secretQuestions)): ?>
				<div class="panel panel-default panel-window" style="width: 100%;">
					<div class="panel-heading">
						<h3 class="panel-title"><?= Yii::t('app', 'Secret questions answer') ?></h3>
					</div>
					<div class="panel-body">
						<?php foreach($secretQuestions as $item): ?>
							<div class="form-group">
								<?= Html::label($item['description']) ?>
								<?= Html::textInput("_secretQuestions[{$item['list_name']}.{$item['entry_name']}]", null, ['class' => 'form-control', 'required' => true]) ?>
							</div>
						<?php endforeach ?>
					</div>
				</div>
			<?php endif ?>

			<?= $renderHelper->render($screen, 'insert') ?>

			<?= Html::hiddenInput('tenant_code', $tenantCode) ?>
			<?= Html::hiddenInput('account_type', $accountType) ?>
			<?= Html::hiddenInput('account_security_type', $userType) ?>

			<div class="wrapper">
				<span class="group-btn">
					<?= Html::submitInput(Yii::t('app', 'Submit'), ['name' => '_registration_data', 'class' => 'btn btn-block btn-primary']) ?>
				</span>
			</div>
		<?= Html::endForm() ?>
	</div>
</div>

<style>
	.account-wall { background-color: <?php echo Yii::$app->params['loginConfig']['header_background_color']; ?>; }

	.form-signin
	{
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
		padding: 4px;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	.form-signin .form-control:focus
	{
		z-index: 2;
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

	.panel-default > .panel-heading {
		background-color : <?php echo $sign_up_style['section_header_background']; ?> !important;
		color : <?php echo $sign_up_style['section_header_color']; ?> !important;
	}

	.stats-section .panel-body { background-color : <?php echo $sign_up_style['section_background_color']; ?> !important; }
	.alert-warning { background-color : <?php echo $sign_up_style['background_color']; ?> !important; }

	.form-control { border-color : <?php echo $sign_up_style['field_border_color']; ?> !important; }

	.form-control:focus { border-color : <?php echo $sign_up_style['field_border_selected_color']; ?> !important; }
</style>
