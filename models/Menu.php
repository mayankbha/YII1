<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/25		Mayank Bhatnagar		33				Need to update chat link code in getFeaturesMenuArray() function to open chat in new window.
 **************************************************************************
 */

namespace app\models;

use app\models\workflow\models\FlowList;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class Menu extends BaseModel
{
    public static $dataLib = 'CodiacSDK.CommonArea';
    public static $dataAction = 'MenuList';

    /**
     * Getting menu (first level) from API server
     * @param array $fieldList
     * @param array $postData
     * @param array $additionallyParam
     * @return null|static
     */
    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        $model = parent::getData($fieldList, $postData);
        if (!empty($model->list)) {
            usort($model->list, function ($a, $b) {
                return ($cmp = strnatcmp($a["weight"], $b["weight"])) ? $cmp : strnatcmp($a["menu_text"], $b["menu_text"]);
            });
        }

        return $model;
    }

    /**
     * Getting array of menu, with url
     * @return array
     */
    public static function getMenuArray()
    {
        $menuName = Yii::$app->request->get('menu', null);
        $screenName = Yii::$app->request->get('screen', null);
        $groupArea = self::getGroupNames();

        $menuArray = [];
		$no_dropdown_menu = [];

        if (!Yii::$app->user->isGuest) {
            $menu = self::getData(['group_name' => $groupArea]);

            if (!empty($menu->list)) {
				//echo '<pre> $menu->list :: '; print_r($menu->list);

                $subMenu = null;
                $subMenu = GroupScreen::getData(['menu_name' => ArrayHelper::getColumn($menu->list, 'menu_name'), 'group_name' => $groupArea]);

				//echo '<pre> $subMenu->list :: '; print_r($subMenu->list);

                foreach ($menu->list as $item) {
                    $subItems = [];

                    if (!empty($subMenu->list)) {
                        foreach ($subMenu->list as $v) {
                            if ($v['menu_name'] === $item['menu_name'] && $v['weight'] != 99) {
                                $subItems[] = [
                                    'label' => Yii::t('app', $v['screen_text']),
                                    'url' => ['/screen/' . $v['menu_name'] . '/' . $v['screen_name']],
                                    'active' => $v['screen_name'] === $screenName
                                ];

								$no_dropdown_menu[] = [
                                    'label' => Yii::t('app', $v['screen_text']),
                                    'url' => ['/screen/' . $v['menu_name'] . '/' . $v['screen_name']],
                                    'active' => $v['screen_name'] === $screenName
                                ];
                            }
                        }
                    }

                    $label = Yii::t('app', $item['menu_text']);

                    if ((UserAccount::getMenuViewType() == UserAccount::MENU_VIEW_LEFT_BAR) && isset($item['menu_image'])) {
                        $label = Html::tag('span', null, ['class' => "left-position-navbar-menu-icon glyphicon {$item['menu_image']}"]) . $label;
                    }

                    $menuArray[] = [
                        'label' => $label,
                        'url' => ['/screen/' . $item['menu_name']],
                        'active' => $item['menu_name'] == $menuName,
                        'encode' => false,
                        'items' => $subItems,
                        'options' => [
                            'class' => ($item['menu_name'] == $menuName) ? 'open' : '',
                        ]
                    ];
                }
            }
        }

        if (UserAccount::getMenuViewType() == UserAccount::MENU_VIEW_DEFAULT) {
            return array_merge($no_dropdown_menu, Menu::getAdditionalMenuArray());
        }

		if (UserAccount::getMenuViewType() == UserAccount::MENU_VIEW_ONE_LEVEL || UserAccount::getMenuViewType() == UserAccount::MENU_VIEW_TWO_LEVEL || UserAccount::getMenuViewType() == UserAccount::MENU_VIEW_TWO_LEVEL_TAB) {
            return array_merge($menuArray, Menu::getAdditionalMenuArray());
        }

        return $menuArray;
    }

    public static function getAdditionalMenuArray() {
        return [
//            [
//                'label' => '<span class="glyphicon glyphicon-globe" aria-hidden="true"></span> ' . Yii::$app->language,
//                'items' => self::getLanguageMenu(),
//                'encode' => false,
//                'visible' => !Yii::$app->user->isGuest
//            ],
            [
                'label' => '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ' . Yii::t('app', 'Features'),
                'items' => self::getFeaturesMenuArray(),
                'encode' => false,
                'visible' => !Yii::$app->user->isGuest
            ],
            [
                'label' => Yii::t('app', 'Login'),
                'url' => ['/site/login'],
                'visible' => Yii::$app->user->isGuest
            ],
            [
                'label' => Yii::t('app', 'Sign-up'),
                'url' => ['/site/sign-up'],
                'visible' => Yii::$app->user->isGuest
            ]
        ];
    }

    public static function getFeaturesMenuArray() {
        $result = [];

		$settings = UserAccount::getSettings();
		//echo '<pre> $settings :: '; print_r($settings); die;

		$menuTooltip = UserAccount::getMenuTooltip();

		//echo '<pre> $menuTooltip :: '; print_r($menuTooltip);

        if ($securityFilter = UserAccount::getSecurityFilter()) {
            if (isset($securityFilter['allow_settings_change']) && $securityFilter['allow_settings_change'] == 'Y') {
                $result[] = [
                    'label' => '<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> ' . Yii::t('app', 'Settings'),
                    'icon' => '<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>',
                    'text' => Yii::t('app', 'Settings'),
                    'url' => ['/settings'],
					'linkOptions' => ['title' => $menuTooltip['settings']],
                    'encode' => false
                ];
            }
            if (isset($securityFilter['allow_password_change']) && $securityFilter['allow_password_change'] == 'Y') {
                $result[] = [
                    'label' => '<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> ' . Yii::t('app', 'Change password'),
                    'icon' => '<span class="glyphicon glyphicon-lock" aria-hidden="true"></span>',
                    'text' => Yii::t('app', 'Change password'),
                    'url' => ['/change-password'],
					'linkOptions' => ['title' => $menuTooltip['change_password']],
                    'encode' => false
                ];
            }
            if (isset($securityFilter['allow_chat']) && $securityFilter['allow_chat'] == 'Y') {
				$url = Url::to(['/chat?show=0'], true);

				$result[] = [
					'label' => '<span class="glyphicon glyphicon-comment" aria-hidden="true"></span> ' . Yii::t('app', 'Chat'),
					'icon' => '<span class="glyphicon glyphicon-comment" aria-hidden="true"></span><span class="badge all-chat-notifications">' . UserAccount::getNewNotifications() .'</span>',
					'text' => Yii::t('app', 'Chat'),
					'url' => 'javascript: void(0);',
					'linkOptions' => ['data-method' => 'post', 'title' => $menuTooltip['chat'], 'onclick' => 'openChat("'.$url.'");'],
					//'url' => ['/chat'],
					//'linkOptions' => ['data-method' => 'post', 'title' => 'Chat'],
					'encode' => false,
				];

                /*if (Yii::$app->controller->id == 'chat') {
                    $result[] = [
                        'label' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> ' . Yii::t('app', 'Chat'),
                        'icon' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span>',
                        'text' => Yii::t('app', 'Chat'),
                        'url' => '#',
                        'linkOptions' => ['class'=>'js-open-chat opened', 'title' => 'Chat'],
                        'encode' => false,
                    ];
                } else {
					$url = Url::to(['/chat?show=0'], true);

                    $result[] = [
                        'label' => '<span class="glyphicon glyphicon-comment" aria-hidden="true"></span> ' . Yii::t('app', 'Chat'),
                        'icon' => '<span class="glyphicon glyphicon-comment" aria-hidden="true"></span><span class="badge all-chat-notifications">' . UserAccount::getNewNotifications() .'</span>',
                        'text' => Yii::t('app', 'Chat'),
						'url' => 'javascript: void(0);',
                        'linkOptions' => ['data-method' => 'post', 'title' => 'Chat', 'onclick' => 'openChat("'.$url.'");'],
                        //'url' => ['/chat'],
                        //'linkOptions' => ['data-method' => 'post', 'title' => 'Chat'],
                        'encode' => false,
                    ];
                }*/
            }
        }
        $result[] = [
            'label' => '<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> ' . Yii::t('app', 'Logout'),
            'icon' => '<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>',
            'text' => Yii::t('app', 'Logout'),
            'url' => !empty(Yii::$app->session['access_token']) ? ['/saml/sls'] : ['/logout'],
            'linkOptions' => ['data-method' => 'post', 'title' => $menuTooltip['logout']],
            'encode' => false,
			'visible' => (isset(Yii::$app->params['guestUserMode']) && (Yii::$app->params['guestUserMode'] != 'auto' || (Yii::$app->params['guestUserMode'] == 'auto') && (!empty($settings) && $settings->user_name != Yii::$app->params['guestUserCredentials']['username'])))
        ];

        return $result;
    }

	public static function getForceChangePasswordMenuArray() {
        $menuTooltip = UserAccount::getMenuTooltip();
        $result[] = [
            'label' => '<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> ' . Yii::t('app', 'Logout'),
            'icon' => '<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>',
            'text' => Yii::t('app', 'Logout'),
            'url' => !empty(Yii::$app->session['access_token']) ? ['/saml/sls'] : ['/logout'],
            'linkOptions' => ['data-method' => 'post', 'title' => $menuTooltip['logout']],
            'encode' => false,
			'visible' => (isset(Yii::$app->params['guestUserMode']) && (Yii::$app->params['guestUserMode'] != 'auto' || (Yii::$app->params['guestUserMode'] == 'auto') && (!empty($settings) && $settings->user_name != Yii::$app->params['guestUserCredentials']['username'])))
        ];

        return $result;
    }

    public static function getLanguageMenu() {
        $result = [];
        if ($languages = Yii::$app->params['languages']) {
            $languages[Yii::$app->sourceLanguage] = 'English';

            foreach ($languages as $key => $item) {
                $class = ($key == Yii::$app->language) ? 'hidden' : '';
                $result[] = [
                    'label' => $item,
                    'url' => '#',
                    'linkOptions' => [
                        'data-set-language' => $key,
                        'class' => $class
                    ],
                    'encode' => false,
                ];
            }
        }

        return $result;
    }

    /**
     * Getting group names
     * @return array
     */
    public static function getGroupNames() {
        if (!empty(Yii::$app->session['screenData']['app\models\UserAccount']) && $groupArea = Yii::$app->session['screenData']['app\models\UserAccount']) {
            $groupArea = $groupArea->group_area;
            if ($explode = explode(';', $groupArea)) return $explode;
            return [$groupArea];
        }
        return [];
    }
}