<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
?>

<?php $hasActiveTab = false;?>
<?php foreach($screenData as $tab_item):?>
    <?php if(isset($tab_item['screen_name'])):?>
        <div class="tab-pane <?= $hasActiveTab ? '' : 'active';?>" id="<?=$tab_item['screen_name'];?>">
            <div style="min-height: 704px;">
                <?=$tab_item['screen_desc'];?>
            </div>
            <div class="btn-group nav-right-group" role="group">
                <button type="button" class="btn btn-default"><?=$tab_item['screen_button1_text'];?></button>
                <button type="button" class="btn btn-default"><?=$tab_item['screen_button2_text'];?></button>
                <button type="button" class="btn btn-default"><?=$tab_item['screen_button3_text'];?></button>
            </div>
        </div>
    <?php endif; ?>
    <?php $hasActiveTab = true;?>
<?php endforeach; ?>
