<?php 
	use yii\helpers\Html;
	use yii\helpers\Url;
	use yii\web\View;
	use yii\helpers\ArrayHelper;
	use yii\widgets\ActiveForm;
	use app\models\UserAccount;

	$image_preview_text = 'Image Preview';
	$close_text = 'Close';

	$internationalization = UserAccount::getInternationalization();

	if(isset($internationalization) && !empty($internationalization)) {
		if(isset($internationalization['rdr_inf_image_preview_text']))
			$image_preview_text = $internationalization['rdr_inf_image_preview_text'];

		if(isset($internationalization['rdr_inf_close_text']))
			$close_text = $internationalization['rdr_inf_close_text'];
	}
?>

<div class="modal fade" id="image-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title"><?php echo $image_preview_text; ?></h4>
            </div>

			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<img class="img-responsive show_full_width_image" src="" style="margin: 0 auto;" />
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<div class="button-block">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $close_text; ?></button>
				</div>
			</div>
        </div>
    </div>
</div>