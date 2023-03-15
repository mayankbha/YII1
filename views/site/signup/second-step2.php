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
	<div class="form-login">
		<?= Html::beginForm('', 'post', ['class' => 'registration-main-form']) ?>
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
					<?= Html::submitInput(Yii::t('app', 'Submit'), ['name' => '_registration_data', 'class' => 'btn btn-primary']) ?>
				</span>
			</div>
		<?= Html::endForm() ?>
	</div>
</div>

<style>
	@import url(http://fonts.googleapis.com/css?family=Roboto:400);

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

	.panel-default > .panel-heading {
		background-color : <?php echo $sign_up_style['section_header_background']; ?> !important;
		color : <?php echo $sign_up_style['section_header_color']; ?> !important;
	}

	.stats-section .panel-body { background-color : <?php echo $sign_up_style['section_background_color']; ?> !important; }
	.alert-warning { background-color : <?php echo $sign_up_style['background_color']; ?> !important; }

	.form-control { border-color : <?php echo $sign_up_style['field_border_color']; ?> !important; }
	.form-control:focus { border-color : <?php echo $sign_up_style['field_border_selected_color']; ?> !important; }
</style>
