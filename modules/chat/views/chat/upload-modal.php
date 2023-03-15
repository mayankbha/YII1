<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

/* @var $this yii\web\View */
use yii\helpers\Html;
use kato\DropZone;
use yii\helpers\Url;

?>

<div class="modal fade" id="upload-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title"><?= Yii::t('app', 'Upload file') ?></h4>
            </div>

            <div class="modal-body upload-modal-body">
                <div class="upload-setting-block">
					<?= DropZone::widget([
						'dropzoneContainer' => 'file-uploader',
						'uploadUrl' => Url::to(['/file/upload'], true),
						'options' => [
							'paramName' => "file",
							'maxFilesize' => '20',
							'uploadMultiple' => false,
							'maxFiles' => 1,
							'acceptedFiles' => '.xlsx,.xls,.doc, .docx,.ppt, .pptx, text/plain, application/pdf, image/*',
							'previewTemplate' => '
								<div class="dz-preview dz-file-preview">
									<div class="dz-details">
										<div class="dz-remove" data-dz-remove><span class="glyphicon glyphicon-remove"></span></div>
										<div class="dz-size"><span data-dz-size></span></div>
										<div class="dz-filename"><span data-dz-name></span></div>
									</div>
									<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
									<div class="dz-success-mark"><span class="glyphicon glyphicon-ok"></span></div>
									<div class="dz-error-mark"><span class="glyphicon glyphicon-exclamation-sign"></span></div>
									<div class="dz-error-message"><span data-dz-errormessage></span></div>
								</div>
							'
						],
						'clientEvents' => [
							'addedfile' => "function(file, xhr) {
								$(file.previewElement).parents('.dropzone').find('.dz-message').hide();
							}",
							'success' => "function(file, xhr) {
								var _this = this;

								var me = $(file.previewElement),
								message = me.parents('.dropzone').find('.dz-message'),
								uploadArrowButton = $(file.previewElement).parents('.dropzone').next('.upload-arrow-button');
                        
								me.find('.dz-remove').click(function () {
									message.show();
									uploadArrowButton.hide();
									uploadArrowButton.prop('disabled', true).removeClass('is-active').removeClass('is-completed');
									uploadArrowButton.attr('data-file-name', '');
									uploadArrowButton.attr('data-original-file-name', '');
									
									uploadArrowButton.parent().find('input[type=\"hidden\"]').val('');
								});

								uploadArrowButton.prop('disabled', false);
								uploadArrowButton.attr('data-file-name', xhr.file_name);
								uploadArrowButton.attr('data-original-file-name', xhr.original_file_name);

								//var t = uploadArrowButton;
								var family = uploadArrowButton.attr('data-family');
								var category = uploadArrowButton.attr('data-category');
								var fileName = xhr.file_name;
								var originalFileName = xhr.original_file_name;

								$.ajax({
									type: 'POST',
									cache: false,
									url: '".Url::toRoute(['/file/init-upload'])."',
									data: {
										family: family,
										category: category,
										file_name: fileName,
										original_file_name: originalFileName
									},
									success: function (data) {
										if (data.status == 'success') {
											$('.info-place span').removeClass('danger').html('Upload file to server');

											uploadArrowButton.addClass('is-active');

											uploadFileFragment(uploadArrowButton, fileName, data.response, 0, 1, _this, file);
										} else if (data.status == 'error') {
											uploadArrowButton.removeClass('is-active');
											$('.info-place span').addClass('danger').html(data.message);
										}
									},
									error: function (data) {
										$('.info-place span').addClass('danger').html(data.responseJSON.message);
										uploadArrowButton.removeClass('is-active');
									}
								});

								//uploadArrowButton.trigger('click');
							}",
							'removedfile' => "function (file) {
								var response = JSON.parse(file.xhr.response);

								$.post('" . Url::to(['/file/delete'], true) . "', {file_name: response.file_name}).done(function(data) {
									console.log('File \"' + response.file_name + '\" has been deleted');
								});
							}"
						],
					]);
                ?>

                <button type="button" class="btn btn-default upload-arrow-button" disabled="" data-family="policy" data-category="taxdoc" style="display: none;"><span class="glyphicon glyphicon-arrow-up"></span></button>

                <?php
					$script = "Dropzone.autoDiscover = false;";

					$this->registerJs($script, \yii\web\View::POS_END, 'dropzone-options');
                ?>
                </div>
            </div>
        </div>
    </div>
</div>