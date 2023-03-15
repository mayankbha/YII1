<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this View
 * @var $content string
 */

use app\components\LeftBarNavigationWidget;
use app\assets\AppAsset;
use app\models\Menu;
use app\models\UserAccount;
use yii\bootstrap\Dropdown;
use yii\bootstrap\Html;
use yii\bootstrap\Nav;
use yii\web\View;
use yii\helpers\Url;

AppAsset::register($this);
$this->beginPage()
?>

<?php
	$current_url = Url::current();
	$url_explode = explode("?", $current_url);

	//echo '<pre>'; print_r($url_explode); die;

	if((isset($url_explode[1]) && $url_explode[1] == 'show=0'))
		$show = false;
	else
		$show = true;

	$userAccountSettings = UserAccount::getSettings();
	if (isset($userAccountSettings) && !empty($userAccountSettings->ChatSettings)) {
        $chatSettings = $userAccountSettings->ChatSettings;
    }
    if (isset($chatSettings) && isset($chatSettings['styleChat'])) {
        $chatStyle = $chatSettings['styleChat'];
    }
?>

<!DOCTYPE html>

<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="shortcut icon" href="<?= Url::toRoute('/favicon.ico', true);?>" type="image/x-icon" />
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>

	<?php if (!YII_DEBUG) { ?>
		<script>
			console.log = function () { };
		</script>
	<?php } ?>
</head>

<body class="<?=(isset($this->params['showBear']) && $this->params['showBear'] == true) ? 'bear_bg' : '' ?> <?=(isset($this->params['isLoginForm']) && $this->params['isLoginForm'] == true) ? 'login-page' : '' ?>" style="background-color: <?php echo Yii::$app->params['loginConfig']['body_background_color']; ?>;">
<?php $this->beginBody() ?>

<?php $setting = UserAccount::getSettings(); ?>
<?php $internationalization_list = UserAccount::getInternationalization(); ?>

<?php if(UserAccount::getMenuViewType() == UserAccount::MENU_VIEW_LEFT_BAR): ?>
	<?php if($setting->force_change == 'Y') {  ?>
		<?= LeftBarNavigationWidget::widget(['feature' => Menu::getForceChangePasswordMenuArray()]) ?>
	<?php } else { ?>
		<?= LeftBarNavigationWidget::widget(['items' => Menu::getMenuArray(), 'feature' => Menu::getFeaturesMenuArray(), /*'language' => Menu::getLanguageMenu()*/]) ?>
	<?php } ?>
<?php endif ?>

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

	tfoot {
		display: table-header-group;
		background-color: #f5f5f5;
	}

	tfoot select {
		padding: 6px 12px;
		font-size: 14px;
		line-height: 1.42857143;
		color: #555;
		background-color: #fff;
		background-image: none;
		border: 1px solid #ccc;
		border-radius: 4px;
		box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
		transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
		width: 100%;
	}

	.toggle .toggle-group {
		margin-bottom: 0px;
	}
	.toggle.ios, .toggle-on.ios, .toggle-off.ios {
		border-radius: 20px;
	}
	.toggle.ios .toggle-handle {
		border-radius: 20px;
	}
</style>

<div class="wrap">
	<input type="hidden" name="internationalization_list" id="internationalization_list" value='<?php echo json_encode($internationalization_list, JSON_HEX_APOS); ?>' />

	<?php if($show) { ?>
		<?php if(UserAccount::getMenuViewType() !== UserAccount::MENU_VIEW_LEFT_BAR): ?>
			<div class="navbar navbar-default navbar-fixed-top" style="background-color: <?php echo Yii::$app->params['loginConfig']['header_background_color']; ?>;">
				<div class="container">
					<div class="navbar-header pull-right">
						<a class="navbar-brand" href="<?= Yii::$app->homeUrl ?>">
							<img class="hdr-logo" src="<?= UserAccount::getHeaderLogo() ?>" alt="<?= Yii::t('app', 'Logo image') ?>">
						</a>
					</div>

					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-main" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<?php /*if (in_array(UserAccount::getMenuViewType(), [UserAccount::MENU_VIEW_ONE_LEVEL, UserAccount::MENU_VIEW_TWO_LEVEL])): ?>
						<div class="dropdown nav-features">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle">
								<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
								<?= Yii::t('app', 'Features') ?>
									<span class="badge all-chat-notifications"><?=UserAccount::getNewNotifications()?></span>
								<b class="caret"></b>
							</a>
							<?= Dropdown::widget(['items' => Menu::getFeaturesMenuArray()]) ?>
						</div>
					<?php endif*/ ?>

					<div class="collapse navbar-collapse" id="navbar-main" style="width:calc(100% - 36px);">
						<?= Nav::widget(['options' => ['class' => 'nav navbar-nav'], 'items' => Menu::getMenuArray()]); ?>
					</div>
				</div>
			</div>
		<?php endif ?>

		<?php if (!Yii::$app->user->isGuest && (UserAccount::getButtonsType() != UserAccount::BUTTON_TYPE_PLACE_STYLE) && (UserAccount::getButtonsType() != UserAccount::BUTTON_TYPE_PLACE)): ?>
			<?= $this->render('@app/views/screen/common/message-area'); ?>
		<?php endif ?>
	<?php } ?>

    <div class="container">
		<?php if($show) { ?>
			<?php if (!Yii::$app->user->isGuest && UserAccount::MENU_VIEW_LEFT_BAR == UserAccount::getMenuViewType() && Yii::$app->controller->id !== 'screen'): ?>
				<?= $this->render('@app/views/screen/common/left-navigation-bar', [
					'screen' => null, 
					'screenList' => null,
					'isWorkflow' => isset($this->params['isWorkflow']),
					'taskCount' => isset($this->params['taskCount']) ? $this->params['taskCount'] : 0
				]); ?>
			<?php endif ?>
		<?php } ?>

        <?= $content ?>
    </div>
</div>

<?php if(isset($url_explode[1]) && ($url_explode[1] != 0 || $url_explode[1] != 'isFrame=0') && $show) { ?>
	<footer class="footer">
		<div class="container">
			<p>&copy; <?= date('Y') ?> Champion Computer Consulting Inc.</p>
		</div>
	</footer>
<?php } ?>

<?php $this->endBody() ?>

</body>
</html>

<?php if(isset($url_explode[1]) && ($url_explode[1] != 0 || $url_explode[1] != 'isFrame=0')) { ?>
	<script type="text/javascript">
		//$('.left-navbar-icon').click();

		//$(".left-navbar-icon").toggleClass("wrap nav-left-group left-position-navbar");

		$('.wrap').css('margin-left', '0px');
		$('.wrap').css('padding', '0px');
		$('.nav-left-group').css('left', '0px');
		$('.left-position-navbar').css('left', '-250px');
		$('.left-navbar-icon').attr('data-toggle', 'true');
		$('.left-navbar-icon').hide();

		$('#sidepanel').removeClass('hidden');
	</script>
<?php } ?>

<script>
	function openChat(url) {
        <?php if (isset($chatStyle) && $chatStyle == 1): ?>
            var $chat = $('#chat-frame');
            if ($chat.length == 0) {
                var chatFrame = '<div id="chat-frame" style="overflow: auto;">' +
                    '<div class="close-button">' +
                    '<a href="javascript:void(0);" onclick="closeChat()" title="Close"><span class="glyphicon glyphicon-remove"></span></a>' +
                    '</div>' +
                    '<iframe src="' + url + '" title="chat"></iframe>' +
                    '</div>';
                $('.wrap').append(chatFrame);

                $chat =  $('#chat-frame');
                var interval = 20;
                var chatWidth = $chat.first().width();
                var step = parseInt(chatWidth) / interval;

                var slideOpen = setInterval(function() {
                    var positionRight = parseInt($chat.css('right')) + step;
                    positionRight = positionRight > 0 ? 0 : positionRight;
                    $chat.css('right', positionRight + 'px');
                    if (positionRight >= 0) clearInterval(slideOpen);
                }, interval);
            } else {
                var $iframe = $chat.find("iframe")[0];
                $iframe.src = $iframe.src;
            }
        <?php else: ?>
            let chatWindow = window.open(url, "chat_window", "left=250,top=110,width=1000,height=670,resizable=true,scrollbars=yes");
            chatWindow.focus();
        <?php endif; ?>
	}

    <?php if (isset($chatStyle) && $chatStyle == 1): ?>
    function closeChat() {
        var $chat = $('#chat-frame');
        var interval = 20;
        var chatWidth = $chat.first().width();
        var step = parseInt($chat.first().width()) / interval;

        var slideClose = setInterval(function() {
            var positionRight = parseInt($chat.first().css('right')) - step;
            $chat.css('right', positionRight + 'px');
            if (positionRight <= -chatWidth) {
                $chat.remove();
                clearInterval(slideClose);
            }
        }, interval);
    }
    <?php endif; ?>

    function marginTopForContent() {
        var nav_bar_height = $('.navbar-default').outerHeight();

        if(nav_bar_height != undefined && nav_bar_height != 'undefined' && nav_bar_height > 0) {
            if ($('.navbar-default').css('display') != 'none') {
                var nav_left_group = 0;

                <?php $buttonsType = UserAccount::getButtonsType(); ?>
                <?php if($buttonsType == UserAccount::BUTTON_TYPE_DEFAULT  || $buttonsType == UserAccount::BUTTON_TYPE_STYLE) { ?>
                    $('.info-place').css('top', (nav_bar_height + 2));
                    nav_left_group = $('.info-place').outerHeight() || 0;
                <?php } else if($buttonsType == UserAccount::BUTTON_TYPE_PLACE_STYLE || $buttonsType == UserAccount::BUTTON_TYPE_PLACE) { ?>
                    $('.nav-left-group').css('top', nav_bar_height + 2);
                    nav_left_group = $('.nav-left-group').outerHeight() || 0;
                <?php } ?>

                $('.sub-content-wrapper').css('margin-top', (nav_bar_height + nav_left_group + 10));

                if ($('.content-wrapper').length) {
                    $('.content-wrapper').css('margin-top', (nav_bar_height + nav_left_group + 10));
                }
            }
        }
    };

    $(document).ready(function() {
        marginTopForContent();
    });

    $(window).resize(function() {
        marginTopForContent();
    });
</script>

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

<?php $this->endPage() ?>