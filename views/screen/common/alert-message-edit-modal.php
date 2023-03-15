<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

/* @var $this yii\web\View */

use yii\helpers\Html;
use app\models\UserAccount;

$alert_text = 'Alert';
$alert_type_text = 'Alert Type';
$alert_message_text = 'Alert message';
$close_text = 'Close';
$save_text = 'Save';

$internationalization = UserAccount::getInternationalization();

if(isset($internationalization) && !empty($internationalization)) {
	
	if(isset($internationalization['rdr_inf_alert_text']))
		$alert_text = $internationalization['rdr_inf_alert_text'];

	if(isset($internationalization['rdr_inf_alert_type_text']))
		$alert_type_text = $internationalization['rdr_inf_alert_type_text'];

	if(isset($internationalization['rdr_inf_alert_message_text']))
		$alert_message_text = $internationalization['rdr_inf_alert_message_text'];

	if(isset($internationalization['rdr_inf_close_text']))
		$close_text = $internationalization['rdr_inf_close_text'];

	if(isset($internationalization['rdr_inf_save_text']))
		$save_text = $internationalization['rdr_inf_save_text'];
}

?>

<div class="modal fade" id="alert-message-edit-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= Yii::t('app', $alert_text) ?></h4>
            </div>

            <div class="modal-body">
                <?= \yii\helpers\Html::label(Yii::t('app', $alert_type_text), 'alert_type', []) ?>
                <?= \yii\helpers\Html::dropDownList('alert_type', null, [''=>'--Select--', 'Success'=>'Success', 'Warning'=>'Warning', 'Error'=>'Error'], [
                    'class' => 'form-control',
                    'id' => 'alert_type'
                ]) ?>
                <br />
                <?= \yii\helpers\Html::label(Yii::t('app', $alert_message_text), 'alert_message', []) ?>
                <?= Html::input('text', 'alert_message', '', [
                    'class' => 'form-control',
                    'id' => 'alert_message'
                ]); ?>

                <?= Html::hiddenInput('alert-sub-id', '', ['id'=>'alert-sub-id']); ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline alert-close" data-dismiss="modal"><?= Yii::t('app', $close_text) ?></button>
                <button type="button" class="btn btn-primary alert-save"><?= Yii::t('app', $save_text) ?></button>
            </div>
        </div>
    </div>
</div>