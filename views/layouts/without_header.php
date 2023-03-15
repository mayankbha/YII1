<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this \yii\web\View
 * @var $content string
 */

use app\assets\AppAsset;
use app\models\UserAccount;
use yii\bootstrap\Html;

AppAsset::register($this);
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
</head>
<body class="<?=(isset($this->params['showBear']) && $this->params['showBear'] == true) ? 'bear_bg' : '' ?> <?=(isset($this->params['isLoginForm']) && $this->params['isLoginForm'] == true) ? 'login-page' : '' ?>" style="background-color: <?php echo Yii::$app->params['loginConfig']['body_background_color']; ?>;">
<?php $this->beginBody() ?>

<style>
	body {
		font-family: <?php echo Yii::$app->params['loginConfig']['body_font_style']; ?>;
		font-size: <?php echo Yii::$app->params['loginConfig']['body_font_size']; ?>;
		color: <?php echo Yii::$app->params['loginConfig']['body_font_color']; ?>;
	}

	.navbar { border-bottom: 3px solid <?php echo Yii::$app->params['loginConfig']['header_menu_bottom_border_color']; ?>; }
	.navbar-default .navbar-nav > li { background-color: <?php echo Yii::$app->params['loginConfig']['header_menu_color']; ?>; }
	.navbar-default .navbar-nav > li > a:hover, .navbar-default .navbar-nav > li > a:focus { background-color: <?php echo Yii::$app->params['loginConfig']['header_menu_hover_color']; ?>; }
	.navbar-default .navbar-nav > .active > a { background-color: <?php echo Yii::$app->params['loginConfig']['header_menu_highlight_color']; ?>; }
	.footer { background-color: <?php echo Yii::$app->params['loginConfig']['footer_background_color']; ?>; }
    
    .nav-left-group {
        top: 0px;
    }
</style>

<div class="wrap" style="margin-left: 0;">
    <?php if (!Yii::$app->user->isGuest && (UserAccount::getButtonsType() != UserAccount::BUTTON_TYPE_PLACE_STYLE) && (UserAccount::getButtonsType() != UserAccount::BUTTON_TYPE_PLACE)): ?>
        <?= $this->render('@app/views/screen/common/message-area'); ?>
    <?php endif ?>

    <div class="container">
        <?= $content ?>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>