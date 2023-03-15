<?php
	use app\models\UserAccount;
	use yii\helpers\Html;
	use yii\helpers\Url;

	$generate_report_text = 'Generate report';

	$internationalization = UserAccount::getInternationalization();

	if(isset($internationalization) && !empty($internationalization)) {
		if(isset($internationalization['rdr_inf_generate_report_text']))
			$generate_report_text = $internationalization['rdr_inf_generate_report_text'];
	}
?>

<div class="modal fade" id="message-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= Yii::t('app', $generate_report_text) ?></h4>
            </div>
            <div class="modal-body">
                <div class="message-pool"></div>
            </div>
        </div>
    </div>
</div>