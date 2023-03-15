<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this \yii\web\View
 * @var $disableBurger boolean
 */

use app\models\UserAccount;
use yii\helpers\Html;
use yii\helpers\Url;

$menuType = UserAccount::getMenuViewType();
$buttonsType = UserAccount::getButtonsType();

$message_text = 'Message';
$connect_to_the_the_server_text = 'Connected to the server';
$hide_api_result_text = 'Hide API result';
$show_api_result_text = 'Show API result';

$internationalization = UserAccount::getInternationalization();

if(isset($internationalization) && !empty($internationalization)) {
	if(isset($internationalization['rdr_inf_message']))
		$message_text = $internationalization['rdr_inf_message'];

	if(isset($internationalization['rdr_inf_connected_to_the_server']))
		$connect_to_the_the_server_text = $internationalization['rdr_inf_connected_to_the_server'];

	if(isset($internationalization['rdr_inf_hide_api_result_text']))
		$hide_api_result_text = $internationalization['rdr_inf_hide_api_result_text'];

	if(isset($internationalization['rdr_inf_show_api_result_text']))
		$show_api_result_text = $internationalization['rdr_inf_show_api_result_text'];
}

?>

<div class="navbar info-place" <?php if (isset($this->params['layout_without_params'])): ?>style="top: 0;"<?php endif ?>>
    <?php if (empty($this->params['layout_without_params'])): ?>
        <?php if($menuType == UserAccount::MENU_VIEW_LEFT_BAR && !in_array($buttonsType, [UserAccount::BUTTON_TYPE_PLACE_STYLE, UserAccount::BUTTON_TYPE_PLACE])): ?>
            <button type="button" class="btn btn-default left-navbar-icon glyphicon glyphicon-menu-hamburger"></button>
        <?php endif ?>
    <?php endif ?>

    <?= Yii::t('app', $message_text) ?>: <span><?= Yii::t('app', $connect_to_the_the_server_text) ?></span>

    <b class="message-pool-link is-hide" data-hide-text="<?= Yii::t('app', $hide_api_result_text) ?>" data-show-text="<?= Yii::t('app', $show_api_result_text) ?>">
        <?= Yii::t('app', $show_api_result_text) ?>
    </b>
</div>

<!--<div id="screen-auto-refresh-div" style="display: none; left: 800px;  position: absolute; min-height: 25px !important; top: 8px; z-index: 999;">&nbsp;&nbsp;<a href="javascript: void(0);" id="scree-auto-refresh-on-link" data-mode="disable_auto_refresh"><?= Html::img('@web/img/refresh_on.png', ['width' => '30px', 'alt' => 'Disable Refresh']); ?></a><a href="javascript: void(0);" style="display: none;" id="scree-auto-refresh-off-link" data-mode="enable_auto_refresh"><?= Html::img('@web/img/refresh_off.png', ['width' => '34px', 'alt' => 'Enable Refresh']); ?></a></div>-->