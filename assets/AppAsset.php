<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\assets;

use Yii;
use yii\bootstrap\BootstrapPluginAsset;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\jui\JuiAsset;
use yii\web\AssetBundle;
use yii\web\View;
use yii\web\YiiAsset;
use app\models\UserAccount;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
		'css/codiacicon.css'
    ];
    public $js = [
        'js/jquery.ui.touch-punch.min.js',
        'js/jquery.cookie.js',
        'js/main.js',
    ];
    public $depends = [
        YiiAsset::class,
        BootstrapPluginAsset::class,
        JuiAsset::class
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

    public function init()
    {
        parent::init();

        $buttonsType = UserAccount::getButtonsType();
        if (($buttonsType == UserAccount::BUTTON_TYPE_PLACE_STYLE) || ($buttonsType == UserAccount::BUTTON_TYPE_STYLE)) {
            $this->css[] = 'css/change-buttons-style.css';
        }
        if (($buttonsType == UserAccount::BUTTON_TYPE_PLACE_STYLE) || ($buttonsType == UserAccount::BUTTON_TYPE_PLACE)) {
            $this->css[] = 'css/change-buttons-place.css';
        }

        $menuType = UserAccount::getMenuViewType();

        /*if (($menuType == UserAccount::MENU_VIEW_ONE_LEVEL) || ($menuType == UserAccount::MENU_VIEW_TWO_LEVEL)) {
            $this->css[] = 'css/one-level-navbar.css';
        }*/

        if (in_array($menuType, [UserAccount::MENU_VIEW_DEFAULT || UserAccount::MENU_VIEW_ONE_LEVEL || UserAccount::MENU_VIEW_TWO_LEVEL || UserAccount::MENU_VIEW_TWO_LEVEL_TAB || UserAccount::MENU_VIEW_LEFT_BAR])) {
            $this->css[] = 'css/two-level-navbar.css';
        }
        if ($menuType == UserAccount::MENU_VIEW_LEFT_BAR) {
            $this->css[] = 'css/left-position-navbar.css';
        }
    }

    /**
     * Registers the CSS and JS files with the given view.
     *
     * @param \yii\web\View $view the view that the asset files are to be registered with.
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function registerAssetFiles($view)
    {

        if (!Yii::$app->user->isGuest)  {
            $user = Yii::$app->getUser()->getIdentity();
            $securityFilter = UserAccount::getSecurityFilter();
//ALEX G CHAT CHANGES 08/11/2020
            if ($user->ChatSettings['enabledNotifications'] && $securityFilter['allow_chat'] == 'Y') {
                $interval = !empty($user->ChatSettings['refreshInterval']) ? (int)$user->ChatSettings['refreshInterval'] * 1000 : 5000;
                $chatConfig = Json::encode([
                    'interval' => $interval,
                    'url' =>  Url::to(['/chat/get-notifications']),
                ]);

                $view->registerJs("var chatConfig = $chatConfig", View::POS_HEAD);
            }

            $view->registerCss(UserAccount::getStyles());

            if (($user = Yii::$app->getUser()->getIdentity()) && !empty($user->main_logo)) {
                $imageBody = $user->main_logo['logo_image_body'];
                $view->registerCss("body.bear_bg{background: url('data:image/jpg;base64,$imageBody') no-repeat center !important}");
            }
        }

        return parent::registerAssetFiles($view);
    }
}
