<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use Yii;

class ThemeHelper extends \yii\base\Component
{
    public static function printFlashes()
    {
        echo '<div class="alert-wrap">';
        foreach (Yii::$app->session->getAllFlashes() as $type => $message) {
            ?>
            <div class="alert alert-<?= $type ?> alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <span class="alert-icon">
                    <span class="icon"></span>
                </span>
                <?= $message ?>
            </div>
        <?php
        }
        echo '</div>';
    }
}