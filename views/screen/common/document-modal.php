<?php
    /**
     * @var $screenProperty array
     */

	use yii\helpers\Html;
	use yii\helpers\Url;
	use yii\web\View;
	use yii\helpers\ArrayHelper;
	use yii\widgets\ActiveForm;
	use app\models\UserAccount;

	$successful_update_text = 'Successfully update';
	$error_update_text = 'Error update';
	$add_document_text = 'Add document';
	$return_text = 'Return';

	$submit_text = 'Submitting...';
	$submit_finished_text = 'Submit';

	$internationalization = UserAccount::getInternationalization();

	if(isset($internationalization) && !empty($internationalization)) {
		if(isset($internationalization['rdr_inf_successful_update_text']))
			$successful_update_text = $internationalization['rdr_inf_successful_update_text'];

		if(isset($internationalization['rdr_inf_error_update_text']))
			$error_update_text = $internationalization['rdr_inf_error_update_text'];

		if(isset($internationalization['rdr_inf_add_document_text']))
			$add_document_text = $internationalization['rdr_inf_add_document_text'];

		if(isset($internationalization['rdr_inf_return_text']))
			$return_text = $internationalization['rdr_inf_return_text'];

		if(isset($internationalization['rdr_inf_submit_text']))
			$submit_text = $internationalization['rdr_inf_submit_text'];

		if(isset($internationalization['rdr_inf_submit_finished_text']))
			$submit_finished_text = $internationalization['rdr_inf_submit_finished_text'];
	}
?>

<div class="modal fade" id="document-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title"></h4>
            </div>

			<?php $form = ActiveForm::begin([
					'id' => 'edit-document-form-id',
					'options' => [
					   'enctype' => 'multipart/form-data'
					]
				]);
			?>
				<div class="modal-body" style="overflow: auto;">
					<table class="table table-hover table-bordered" id="view-document-list-tbl" style="display: none;">
						<thead></thead>

						<tbody></tbody>
					</table>

					<table class="table table-hover table-bordered view-deleted-document-list-tbl" style="display: none;">
						<thead></thead>

						<tbody></tbody>
					</table>

					<div class="edit-document-modal-div" style="display: none;">
						<div class="alert-wrap" id="edit-document-success-message-div" style="display: none;"><div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><span class="alert-icon"><span class="icon"></span></span><?php echo $successful_update_text; ?></div></div>

						<div class="alert-wrap" id="edit-document-failed-message-div" style="display: none;"><div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><span class="alert-icon"><span class="icon"></span></span><?php echo $error_update_text; ?></div></div>

						<div class="row">
							<div class="col-sm-2">
								<div class="form-group">
									<label>Category</label>
								</div>
							</div>

							<div class="col-sm-3">
								<div class="form-group">
									<label>Description</label>
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label>FileName</label>
								</div>
							</div>

							<!--<div class="col-sm-1">
								<div class="form-group">
									<label>FileSize</label>
								</div>
							</div>

							<div class="col-sm-2">
								<div class="form-group">
									<label>CreatedBy</label>
								</div>
							</div>

							<div class="col-sm-2">
								<div class="form-group">
									<label>CreatedDate</label>
								</div>
							</div>-->
						</div>

						<div class="document-post-form-div">
							
						</div>

						<!--<div class="row">
							<div class="col-sm-3">
								<?= Html::textInput("file_name[]", null, ["class" => "form-control"]); ?>
							</div>

							<div class="col-sm-2">
								<?= Html::textInput("description[]", null, ["class" => "form-control"]); ?>
							</div>

							<div class="col-sm-2">
								<?= Html::textInput("file_size[]", null, ["class" => "form-control"]); ?>
							</div>

							<div class="col-sm-2">
								<?= Html::textInput("created_by[]", null, ["class" => "form-control"]); ?>
							</div>

							<div class="col-sm-2">
								<?= Html::textInput("created_date[]", null, ["class" => "form-control"]); ?>
							</div>
						</div>-->

						<div class="row document-family-wrapper" style="display: none;">
							<div class="col-sm-3">
								<?= Html::textInput("description[]", null, ['class' => 'form-control']) ?>
							</div>

							<div class="col-sm-6">
								<?= Html::fileInput("document[]", null, ['class' => 'form-control']) ?>
							</div>

							<!--<div class="col-sm-1">
								<span>&nbsp;</span>
							</div>

							<div class="col-sm-2">
								<span>&nbsp;</span>
							</div>

							<div class="col-sm-2">
								<span>&nbsp;</span>
							</div>-->

							<div class="col-sm-1">
								<span class="glyphicon glyphicon-remove remove-family-icon"></span>
							</div>
						</div>

						<div class="form-group">
							<br>

							<?= Html::button(Yii::t('app', $add_document_text), ['class' => 'btn btn-default add-category-family']); ?>
						</div>

						<input type="hidden" id="document-cnt" value="0" />
						<input type="hidden" name="document_family[]" id="document_family" />
						<input type="hidden" name="document_kps[]" id="document_kps" />
					</div>

					<div class="form-group pull-right">
						<span class="glyphicon glyphicon-trash show-deleted-document-icon" alt="Show Deleted Documents"></span>
					</div>

					<br>
				</div>

				<div class="modal-footer">
					<div class="button-block view-deleted-document-list-tbl" style="display: none;">
						<button type="button" class="btn btn-info document-model-list-return-btn" style="margin-bottom: 10px; display: none;"><?php echo $return_text; ?></button>
					</div>

					<div class="button-block edit-document-modal-div" style="display: none;">
						<!--<button type="button" class="btn btn-primary" id="document-model-save-btn">Submit</button>-->

						<?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary document-model-save-btn progress-button', 'style' => 'width: 20%', 'data-loading' => $submit_text, 'data-finished' => $submit_finished_text]); ?>
					</div>

                    <?php if (isset($screenProperty['edit']) && $screenProperty['edit'] == "Y"): ?>
                        <div class="button-block text-center add-document-without-edit-mode hide">
                            <?= Html::button(Yii::t('app', 'Add'), ['class' => 'btn btn-default']); ?>
                        </div>
                    <?php endif; ?>
				</div>
			<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>