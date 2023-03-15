<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use app\modules\chat\models\Notification;
use Yii;
use yii\web\Cookie;
use yii\web\IdentityInterface;
use yii\helpers\Url;

/**
 *
 * @property string $username;
 * @property string $account_name;
 * @property string $account_status;
 * @property string $force_change;
 * @property string $email;
 * @property string $language;
 * @property string $account_type;
 * @property string $group_area;
 * @property string $account_password;
 * @property string $background_color;
 * @property string $border_size;
 * @property string $border_color;
 * @property string $text_color;
 * @property string $link_color;
 * @property string $info_color;
 * @property string $tenant_code;
 * @property string $last_login;
 * @property string $document_group;
 * @property string account_security_type;
 */
class UserAccount extends AccountModel implements IdentityInterface
{
    const MENU_VIEW_DEFAULT = 'MenuType.M1';
    const MENU_VIEW_ONE_LEVEL = 'MenuType.M2';
    const MENU_VIEW_TWO_LEVEL = 'MenuType.M3';
    const MENU_VIEW_TWO_LEVEL_TAB = 'MenuType.M4';
    const MENU_VIEW_LEFT_BAR = 'MenuType.M5';

    const BUTTON_TYPE_DEFAULT = 'ButtonType.B1';
    const BUTTON_TYPE_PLACE_STYLE = 'ButtonType.B2';
    const BUTTON_TYPE_PLACE = 'ButtonType.B3';
    const BUTTON_TYPE_STYLE = 'ButtonType.B4';

    static public $availableButtonStyles =[
      self::BUTTON_TYPE_DEFAULT,
      self::BUTTON_TYPE_PLACE_STYLE,
      self::BUTTON_TYPE_PLACE,
      self::BUTTON_TYPE_STYLE,
    ];

    public $id;
    public $account_name;
    public $account_status;
    public $account_type;
    public $background_color;
    public $button_style_code = self::BUTTON_TYPE_DEFAULT;
    public $border_color;
    public $border_size;
    public $email;
    public $force_change;
    public $group_area;
    public $info_color;
    public $user_language;
    public $last_login;
    public $link_color;
    public $tenant_code;
    public $text_color;
    public $user_name;
    public $currencyformat_code;
    public $datetimeformat_code;
    public $timezone_code;
    public $account_password;
    public $style_template;
    public $ChatSettings;
    public $dateformat_code;
    public $timeformat_code;
    public $currencytype_code;
    public $menutype_code = self::MENU_VIEW_DEFAULT;
    public $document_group;
    public $account_security_type;
    public $primary_group;
    public $custom_sql_security_level;

    public $authKey;
    public $accessToken;

    public $main_logo;
    public $header_logo;
    public $tenant_list;

    public static $dataAction = 'login';

    public static $patchAction = 'menu';

    public static $getDefaultAction = 'menu/DefaultColors';

    public static function getBorderSizeAllowed()
    {
        return array(
            '0px' => '0px',
            '1px' => '1px',
            '2px' => '2px',
            '3px' => '3px',
            '4px' => '4px',
			'small_shadow' => 'Small Shadow',
            'large_shadow' => 'Large Shadow',
        );
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $session = Yii::$app->session;
        $existModel = (isset($session['screenData']) && isset($session['screenData']['app\models\UserAccount'])) ? $session['screenData']['app\models\UserAccount'] : null;
        return (!empty($existModel) && $existModel->id == $id) ? $existModel : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function encodePassword($password)
    {
        return function_exists('openssl_digest') ? openssl_digest($password, 'sha512') : hash('sha512', $password);
    }

    public function validatePassword($password)
    {
        return !empty($this->user_name);
    }

    public function isNeedToChangePassword()
    {
        return $this->force_change==='Y';
    }

    public function getStartScreen()
    {
        if ($this->isNeedToChangePassword()) {
            return Url::toRoute(['change-password']);
        }

        if (isset($this->primary_group) && ($group = Group::getData(['group_name' => [$this->primary_group]], []))) {
            if (!empty($group->list[0]['start_screen']) && $screen = Screen::getData(['id' => [$group->list[0]['start_screen']]])) {
                if (isset($screen->list[0])) {
                    if (($screenGroup = GroupScreen::getData(['screen_name' => [$screen->list[0]['screen_name']]])) && isset($screenGroup->list[0])) {
                        return Url::toRoute([
                            '/screen',
                            'menu' => $screenGroup->list[0]['menu_name'],
                            'screen' => $screen->list[0]['screen_name'],
                            '#' => "tab={$screen->list[0]['id']}"
                        ]);
                    }
                }
            }
        }

        return Url::toRoute(['index']);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @param string $password
     *
     * @return static|null
     */
    public static function findByUsername($username, $password)
    {
        self::checkSourceLink();
        if (!empty(Yii::$app->params['enableSendHashPassword'])) {
            $password = self::encodePassword($password);
        }

		if($password != '')
			return self::getModelInstance(null, ["func_param" => ['ulogin' => $username, 'upassword' => $password]]);
		else
			return self::getModelInstance(null, ["func_param" => ['ulogin' => $username]]);
    }

    public static function initSettings() {
		//echo 'in UserAccount initSettings'; die;

        $session = Yii::$app->session;

        if (!empty($session['screenData'][__CLASS__])) {
            $settings = $session['screenData'][__CLASS__];

			//echo '<pre> $settings :: '; print_r($settings); die;

            $settings->timezone_code = ($model = GetListList::getByListName(str_replace('TimeZone.', '', $settings->timezone_code), 'TimeZone')) ? $model['description'] : null;
            $settings->dateformat_code = ($model = GetListList::getByListName(str_replace('Date.', '', $settings->dateformat_code), 'Date')) ? $model['description'] : null;
            $settings->timeformat_code = ($model = GetListList::getByListName(str_replace('Time.', '', $settings->timeformat_code), 'Time')) ? $model['description'] : null;
            $settings->currencyformat = $settings->currencyformat_code;
            $settings->currencyformat_code = ($model = GetListList::getByListName(str_replace('Currency.', '', $settings->currencyformat_code), 'Currency')) ? $model['description'] : null;
            $settings->currencytype_code = ($model = GetListList::getByListName(str_replace('CurrencyType.', '', $settings->currencytype_code), 'CurrencyType')) ? $model['description'] : null;
            $settings->user_language = ($model = GetListList::getByListName(str_replace(GetListList::BASE_NAME_LANGUAGE . '.', '', $settings->user_language), GetListList::BASE_NAME_LANGUAGE)) ? $model['entry_name'] : null;

            //Default values for saving
            $settings->dateformat_code_default = ($model = GetListList::getByListName('DEFAULT_VALUE', 'Date')) ? $model['description'] : null;
            $settings->timeformat_code_default = ($model = GetListList::getByListName('DEFAULT_VALUE', 'Time')) ? $model['description'] : null;

            $securityFilter = SecurityFilter::getData([
                'tenant' => [$settings->tenant_code],
                'account_type' => [$settings->account_type],
                'user_type' => [$settings->account_security_type],
            ]);

			if(is_array($settings->style_template))
				$style_template = base64_encode(json_encode($settings->style_template));
			else
				$style_template = $settings->style_template;

			$settings->style_template = json_decode(base64_decode($style_template), true);

			//$settings->style_template = json_decode(base64_decode($settings->style_template), true);
            $settings->ChatSettings = json_decode(base64_decode($settings->tenant_list[0]['ChatSettings']), true);

            if (!empty($settings->tenant_list[0])) {
                $logoPKs = explode(";", $settings->tenant_list[0]['Logos']);
                $settings->custom_sql_security_level = $settings->tenant_list[0]['custom_sql_security_level'];
                foreach ($logoPKs as $pk) {
                    $logo = LogoModel::getModel($pk);
                    if (isset($logo['type'])) {
                        if ($logo['type'] === 'LOGO_MAIN') {
                            $settings->main_logo = $logo;
                        }
                        if ($logo['type'] === 'LOGO_HEADER') {
                            $settings->header_logo = $logo;
                        }
                    }
                }
            }
            static::generateTemplateImages($settings);

            if (!empty($securityFilter) && !empty($securityFilter->list[0])) {
                $session['securityFilter'] = $securityFilter->list[0];
            }

			$menuTooltip = Screen::getMenuTooltip();

			//echo '<pre> $menuTooltip :: '; print_r($menuTooltip);

			if(!empty($menuTooltip))
				$session['menuTooltip'] = $menuTooltip;

			$internationalization = Screen::getInternationalization();

			//echo '<pre> $internationalization :: '; print_r($internationalization);

			if(!empty($internationalization))
				$session['internationalization'] = $internationalization;
        }
    }

    public static function generateTemplateImages($settings)
    {
        if (!empty($settings->style_template['avatar']) && $model = LogoModel::getModel($settings->style_template['avatar'], ';')) {
            $settings->style_template['avatar_body'] = $model['logo_image_body'];
        }
        if (!empty($settings->style_template['background_image']) && !empty($settings->style_template['use_body_images']) && $model = LogoModel::getModel($settings->style_template['background_image'], ';')) {
            $settings->style_template['background_image_body'] = $model['logo_image_body'];
        }
        if (!empty($settings->style_template['menu_background_image']) && !empty($settings->style_template['use_menu_images']) && $model = LogoModel::getModel($settings->style_template['menu_background_image'], ';') ) {
            $settings->style_template['menu_background_image_body'] = $model['logo_image_body'];
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    protected static function preparePostData($additionalPostData = array(), $funcName = null)
    {
        return $additionalPostData;
    }

    protected static function getData($postData = array())
    {
        $model = new static();

        $postData = array_merge(array('func_name' => $model::$dataAction), $postData);

        $attributes = $model->processData($model::preparePostData($postData));

        if (!empty($attributes) && isset($attributes['user'])) {
            $attributes['user']['id'] = $attributes['user']['user_id'];
            unset($attributes['user']['user_id']);
            $attributes['user']['username'] = $attributes['user']['user_name'];
            unset($attributes['user']['username']);
            foreach ($attributes['user'] as $attribute => $value) {
                $model->$attribute = $value;
            }
            $model->account_password = isset($postData['func_param']['upassword']) ? $postData['func_param']['upassword'] : null;
        } else {
            $model = null;
        }

        return $model;
    }

    public static function getStyles()
    {
        $userSettings = self::getSettings();

        $cssFromProfile = '';
        if (!Yii::$app->user->isGuest && !empty($userSettings->style_template)) {
            $settings = $userSettings->style_template;

			if(isset($settings['border_size'])) {
				if($settings['border_size'] == 'small_shadow') {
					$body_border_setting = ".tab-content, .screen-stepper .screen-stepper-step a, .screen-stepper .screen-stepper-step:after {border-width: 0px !important; border-color: {$settings['border_color']} !important; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important; border-radius: .25rem !important;}";
				} else if($settings['border_size'] == 'large_shadow') {
					$body_border_setting = ".tab-content, .screen-stepper .screen-stepper-step a, .screen-stepper .screen-stepper-step:after {border-width: 0px !important; border-color: {$settings['border_color']} !important; box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important; border-radius: .25rem !important;}";
				} else {
					$body_border_setting = ".tab-content, .screen-stepper .screen-stepper-step a, .screen-stepper .screen-stepper-step:after {border-width: {$settings['border_size']} !important; border-color: {$settings['border_color']} !important;}";
				}
			} else {
				$body_border_setting = '';
			}

			if(isset($settings['header_border_size'])) {
				if($settings['header_border_size'] == 'small_shadow') {
					$header_border_setting = ".header-section {border-width: 0px !important; border-color: {$settings['header_border_color']}; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important; border-radius: .25rem !important; background-color: {$settings['header_color']} !important}";
				} else if($settings['header_border_size'] == 'large_shadow') {
					$header_border_setting = ".header-section {border-width: 0px !important; border-color: {$settings['header_border_color']}; box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important; border-radius: .25rem !important; background-color: {$settings['header_color']} !important}";
				} else {
					$header_border_setting = ".header-section {border: {$settings['header_border_size']} solid {$settings['header_border_color']} !important; background-color: {$settings['header_color']} !important}";
				}
			} else {
				$header_border_setting = '';
			}

			if(isset($settings['section_border_size']) && $settings['section_border_size'] == 'small_shadow') {
				$section_border_setting = ".panel {border-width: 0px !important; border-color: {$settings['border_color']} !important; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important; border-radius: .25rem !important;}";
			} else if(isset($settings['section_border_size']) && $settings['section_border_size'] == 'large_shadow') {
				$section_border_setting = ".panel {border-width: 0px !important; border-color: {$settings['border_color']} !important; box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important; border-radius: .25rem !important;}";
			} else if(isset($settings['border_size'])) {
				$section_border_setting = ".panel {border-width: {$settings['border_size']} !important; border-color: {$settings['border_color']} !important;}";
			} else {
				$section_border_setting = '';
			}

			$cssFromProfile = "
                html body:not(bear_bg) {
                    background-color: {$settings['background_color']} !important; 
                    color: {$settings['text_color']} !important
                }
                .alert, 
                .second-menu-level > li:not(.active) > a {
                    border-width: {$settings['border_size']} !important;
                }
				{$body_border_setting}
				{$section_border_setting}
                .second-menu-level li:before, 
                .nav-right-group button:before {
                    border-color: transparent transparent {$settings['tab_unselected_color']} transparent;
                }
                .second-menu-level li:after, 
                .nav-right-group button:after {
                    border-color: transparent transparent transparent {$settings['tab_unselected_color']} !important;
                }
                
                .screen-tab:active:before,
                .screen-tab:focus:before,
                .nav-right-group button:active:before,
                .nav-right-group button:focus:before {
                    border-color: transparent transparent {$settings['tab_selected_color']}  transparent;
                }
                
                .screen-tab:active:after ,
                .screen-tab:focus:after ,
                .nav-right-group button:active:after,
                .nav-right-group button:focus:after {
                    border-color: transparent transparent transparent {$settings['tab_selected_color']} !important;
                }
                
                .second-menu-level li.active:before, 
                .nav-right-group button.active:before {
                    border-color: transparent transparent {$settings['tab_selected_color']}  transparent;
                    z-index:1;
                }
                .second-menu-level li.active:after, 
                .nav-right-group button.active:after {
                    border-color: transparent transparent transparent {$settings['tab_selected_color']} !important;
                    z-index:1;
                }
                .navbar,
                .navbar-default .navbar-collapse .navbar-nav,
                .left-position-navbar,
                .left-position-navbar-logo, 
                .left-position-navbar-user-info,
                .left-position-navbar .system-rooms-container,
                .left-position-navbar #create-room-form,
                .left-position-navbar .create-room-button {
                    border-color: {$settings['border_color']} !important 
                }
                a, a:hover {
                    color: {$settings['link_color']}
                }
                h3, label {
                    color: {$settings['info_color']}  !important
                }
                span.info-list-value {
                    color: {$settings['text_color']} !important
                }
                .query-search-field {
                    border-width: 1px !important;
                }
                .search-input-wrapper,
                div:not(.search-input-wrapper) > .search-input-inner-wrapper input {
                    border-color: {$settings['search_border_color']} !important;
                }
                .navbar-default .navbar-nav > .active > a,
                .navbar-default .navbar-nav > .active > a:hover,
                .navbar-default .navbar-nav > .active > a:focus,
                .navbar-default .navbar-nav > li,
                .navbar-default .navbar-nav > li > a:hover, 
                .dropdown.open > a,
                .dropdown.open a:hover,
                .left-position-navbar-menu li a:hover, 
                .left-position-navbar-menu li a:focus,
                .left-position-navbar-menu li a:active,
                .left-position-navbar-menu .dropdown.active,
                .left-position-navbar-menu .dropdown.active .dropdown-menu .active a {
                    background-color: {$settings['highlight_color_selection']} !important;
                }
				{$header_border_setting}
                .screen-tab, 
                .screen-tab:hover, 
                .pagination > li > a,
                .pagination > li > span,
                .second-menu-level > li:not(.active) > a,
                .screen-stepper .screen-stepper-step a {
                    background: {$settings['tab_unselected_color']} !important;
                }
                .screen-tab.active,
                .screen-tab:active,
                .screen-tab:focus,
                .second-menu-level > li.active > a,
                .dropdown-menu > li:hover, .pagination > li:hover ,
                .pagination > li.active > a,
                .screen-stepper .screen-stepper-step a.active {
                    background: {$settings['tab_selected_color']} !important;
                    z-index:1;
                }
                .stats-section .panel {
                    background-color: {$settings['section_background_color']} !important;
                }
                .nav-right-group > button {
                    color: {$settings['link_color']} !important
                }
                .btn-group.nav-right-group {
                    margin-bottom: -8px;
                    margin-left: {$settings['border_size']} !important;
                }
            ";
            $cssFromProfile .= (!empty($settings['message_line_background'])) ? "
                div:not(.nav-left-group) > .info-place,
                div:not(.sub-content-wrapper) > div > .nav-left-group {
                    background-color: {$settings['message_line_background']} !important
                }
            " : '';
            $cssFromProfile .= (!empty($settings['menu_background'])) ? "
                .navbar:not(.info-place):not(.error-message), 
                .navbar:not(.info-place):not(.error-message) .dropdown-menu,
				.left-position-navbar-menu .dropdown .dropdown-menu,
				.navbar-default .navbar-nav > li > a,
                .left-position-navbar {
                    background-color: {$settings['menu_background']} !important
                }
            " : '';
            $cssFromProfile .= (!empty($settings['message_line_color'])) ? "
                .info-place, .info-place span, .btn-group.nav-left-group .info-place span {
                    color: {$settings['message_line_color']} !important
                }
            " : '';
            $cssFromProfile .= (!empty($settings['section_header_color'])) ? "
                h3.panel-title {
                    color: {$settings['section_header_color']}  !important
                }
            " : '';
            $cssFromProfile .= (!empty($settings['section_header_background']) && !empty($settings['section_header_color'])) ? "
                .stats-section .panel-heading {
                    background-color: {$settings['section_header_background']}  !important;
                    color: {$settings['section_header_color']}  !important;
                }
                .table thead th {
                    background-color: {$settings['section_header_background']} !important;
                }
            " : '';
            $cssFromProfile .= (!empty($settings['menu_text_color'])) ? "
                .navbar-default .navbar-nav > li a, 
                .dropdown.nav-features a,
                .left-position-navbar-menu a,
                .left-position-navbar .feature-block a,
                .left-position-navbar .system-rooms-container li,
				.dropdown .dropdown-menu li a,
                .left-position-navbar .user-rooms-container li {
                    color: {$settings['menu_text_color']} !important
                }
            " : '';
            $cssFromProfile .= (!empty($settings['search_border_selected_color'])) ? "
                .search-input-wrapper:focus,
                div:not(.search-input-wrapper) > .search-input-inner-wrapper input:focus,
                .left-navbar-icon:focus {
                    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px " . self::hex2rgba($settings['search_border_selected_color'], 0.6) . ";
                    outline: 0;
                }
            " : '';
            $cssFromProfile .= (!empty($settings['field_border_color'])) ? "
                select, input, .input-group-addon, textarea,
                select.form-control, input.form-control, textarea.form-control {
                    border-color: {$settings['field_border_color']};
                }
            " : '';
            $cssFromProfile .= (!empty($settings['field_border_selected_color'])) ? "
                select:focus, input:focus, textarea:focus,
                select.form-control:focus, input.form-control:focus, textarea.form-control:focus {
                    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px " . self::hex2rgba($settings['field_border_selected_color'], 0.6) . ";
		    border-color: {$settings['field_border_selected_color']} !important;
                }
            " : '';

            $cssFromProfile .= (!empty($settings['background_image_body'])) ? "
                html, body:not(bear_bg) {
                    background-image: url(data:image/jpg;base64,{$settings['background_image_body']});
                }
            " : '';

            $cssFromProfile .= (!empty($settings['menu_background_image_body'])) ? "
                .left-position-navbar {
                    background-image: url(data:image/jpg;base64,{$settings['menu_background_image_body']});
                }
            " : '';
        }

        return $cssFromProfile;
    }

    /**
     * @return null|self
     */
    public static function getSettings()
    {
        $session = Yii::$app->session;
        if (!empty($session['screenData'][__CLASS__])) {
            return $session['screenData'][__CLASS__];
        }

        return null;
    }

    public static function getHeaderLogo()
    {
        if (!Yii::$app->user->isGuest && ($settings = self::getSettings()) && !empty($settings->header_logo['logo_image_body'])) {
            return "data:image/jpg;base64,{$settings->header_logo['logo_image_body']}";
        }

        return Url::toRoute('/img/logo.png', null);
    }

    public static function getMenuViewType()
    {
        if (!Yii::$app->user->isGuest && ($settings = self::getSettings()) && !empty($settings->menutype_code)) {
            return $settings->menutype_code;
        }

        return self::MENU_VIEW_DEFAULT;
    }

    public static function getButtonsType()
    {
        if (!Yii::$app->user->isGuest && ($settings = self::getSettings()) && !empty($settings->button_style_code)) {
            return $settings->button_style_code;
        }

        return self::BUTTON_TYPE_DEFAULT;
    }

    public static function getSecurityFilter()
    {
        $session = Yii::$app->session;
        if (!empty($session['securityFilter'])) {
            return $session['securityFilter'];
        }

        return null;
    }

	public static function getMenuTooltip()
    {
		//echo 'in UserAccount getMenuTooltip';

        $session = Yii::$app->session;

        if (!empty($session['menuTooltip'])) {
            return $session['menuTooltip'];
        }

        return null;
    }

	public static function getInternationalization()
    {
		//echo 'in UserAccount getInternationalization';

        $session = Yii::$app->session;

        if (!empty($session['internationalization'])) {
            return $session['internationalization'];
        }

        return null;
    }

    public static function hex2rgba($color, $opacity = false)
    {
        $default = 'rgb(0,0,0)';

        if (empty($color)) {
            return $default;
        }

        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            $hex = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
        } elseif (strlen($color) == 3) {
            $hex = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        } else {
            return $default;
        }

        $rgb = array_map('hexdec', $hex);

        if ($opacity) {
            if (abs($opacity) > 1) {
                $opacity = 1.0;
            }
            $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(",", $rgb) . ')';
        }

        return $output;
    }

    /**
     * Get count of new notifications in chat
     */
    public static function getNewNotifications()
    {
        return Notification::getNewNotifications();
    }
}