<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 */

	use app\models\UserAccount;

	$screen_text = 'Screen';
	$edit_text = 'Edit';
	$approve_text = 'Approve';

	$internationalization = UserAccount::getInternationalization();

	if(isset($internationalization) && !empty($internationalization)) {
		if(isset($internationalization['rdr_inf_screen_text']))
			$screen_text = $internationalization['rdr_inf_screen_text'];

		if(isset($internationalization['rdr_inf_edit_text']))
			$edit_text = $internationalization['rdr_inf_edit_text'];

		if(isset($internationalization['rdr_inf_approve_text']))
			$approve_text = $internationalization['rdr_inf_approve_text'];
	}
?>

<input type="hidden" name="internationalization_list" id="internationalization_list" value='<?php echo json_encode($internationalization, JSON_HEX_APOS); ?>' />

<div class="modal fade" id="screen-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title"><?= Yii::t('app', $screen_text) ?></h4>
            </div>

            <div class="modal-body">
                <iframe style="width: 100%; min-height: 600px" src=""></iframe>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="plugin-warning-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title"></h4>
            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline alert-close" data-dismiss="modal"><?= Yii::t('app', $edit_text) ?></button>
                <button type="button" class="btn btn-info warning-reaction-approve"><?= Yii::t('app', $approve_text) ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="message-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= Yii::t('app', 'API Response') ?></h4>
            </div>

            <div class="modal-body">
               <div class="message-pool"></div>
            </div>
        </div>
    </div>
</div>