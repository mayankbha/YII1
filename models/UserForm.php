<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use app\models\forms\UserStyleTemplateForm;
use Yii;
use yii\base\Model;

/**
 * Class UserForm
 * @property UserStyleTemplateForm $style_template
 */
class UserForm extends Model
{
    public $id;
    public $account_name;
    public $account_status;
    public $account_type;
    public $background_color;
    public $border_color;
    public $border_size;
    public $email;
    public $force_change;
    public $group_area;
    public $info_color;
    public $language;
    public $link_color;
    public $tenant_code;
    public $text_color;
    public $user_name;
    public $currencyformat_code;
    public $datetimeformat_code;
    public $timezone_code;
    public $header_border_size;
    public $header_color;
    public $header_border_color;
    public $search_border_color;
    public $field_border_color;
    public $search_border_selected_color;
    public $field_border_selected_color;
    public $tab_selected_color;
    public $tab_unselected_color;
    public $section_background_color;
    public $highlight_color_selection;
    public $dateformat_code;
    public $timeformat_code;
    public $currencytype_code;
    public $menu_background;
    public $menu_text_color;
    public $message_line_color;
    public $message_line_background;
    public $section_header_color;
    public $section_header_background;
    public $chart_color_first;
    public $chart_color_second;
    public $chart_color_third;
    public $chart_color_fourth;
    public $document_group;

    public $style_template;

    const BOOL_API_TRUE = 'Y';
    const BOOL_API_FALSE = 'N';

    const ACTIVE_ACCOUNT = 'active';
    const INACTIVE_ACCOUNT = 'inactive';

    public static $boolProperty = [
        self::BOOL_API_TRUE => 'Yes',
        self::BOOL_API_FALSE => 'No',
    ];

    public static $statusProperty = [
        self::ACTIVE_ACCOUNT => 'Active',
        self::INACTIVE_ACCOUNT => 'Inactive',
    ];

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['user_name', 'text_color', 'link_color', 'language', 'info_color'], 'required'],
            [['group_area', 'force_change', 'email', 'border_size', 'border_color', 'background_color'], 'required'],
            [
                [
                    'account_type',
                    'account_status',
                    'account_name',
                    'currencyformat_code',
                    'timezone_code',
                    'dateformat_code',
                    'timeformat_code',
                    'currencytype_code'
                ],
                'required'
            ],
            [['id', 'tenant_code'], 'integer'],
            [
                [
                    'header_border_size',
                    'header_color',
                    'header_border_color',
                    'search_border_color',
                    'field_border_color',
                    'search_border_selected_color',
                    'field_border_selected_color',
                    'tab_selected_color',
                    'tab_unselected_color',
                    'section_background_color',
                    'highlight_color_selection',
                    'datetimeformat_code',
                    'menu_background',
                    'menu_text_color',
                    'message_line_color',
                    'message_line_background',
                    'section_header_color',
                    'section_header_background',
                    'chart_color_first',
                    'chart_color_second',
                    'chart_color_third',
                    'chart_color_fourth',
                    'document_group',
                    'style_template',
                ],
                'safe'
            ]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'user_name' => Yii::t('app', 'User name'),
            'text_color' => Yii::t('app', 'Text color'),
            'tenant_code' => Yii::t('app', 'Tenant code'),
            'link_color' => Yii::t('app', 'Link color'),
            'language' => Yii::t('app', 'Language'),
            'info_color' => Yii::t('app', 'Info color'),
            'group_area' => Yii::t('app', 'Group area'),
            'force_change' => Yii::t('app', 'Change'),
            'email' => Yii::t('app', 'Email'),
            'border_size' => Yii::t('app', 'Border size'),
            'border_color' => Yii::t('app', 'Border color'),
            'background_color' => Yii::t('app', 'Background color'),
            'account_type' => Yii::t('app', 'Account type'),
            'account_status' => Yii::t('app', 'Status'),
            'account_name' => Yii::t('app', 'Account name'),
            'id' => Yii::t('app', 'ID'),
            'header_border_size' => Yii::t('app', 'Header border size'),
            'header_color' => Yii::t('app', 'Header background color'),
            'header_border_color' => Yii::t('app', 'Header border color'),
            'search_border_color' => Yii::t('app', 'Search border color'),
            'search_border_selected_color' => Yii::t('app', 'Highlight of selected search'),
            'field_border_color' => Yii::t('app', 'Field borders color'),
            'field_border_selected_color' => Yii::t('app', 'Highlight of selected field'),
            'tab_selected_color' => Yii::t('app', 'Tab selected color'),
            'tab_unselected_color' => Yii::t('app', 'Tab unselected color'),
            'section_background_color' => Yii::t('app', 'Section background color'),
            'highlight_color_selection' => Yii::t('app', 'Menu selected color'),

            'currencyformat_code' => Yii::t('app', 'Currency format'),
            'datetimeformat_code' => Yii::t('app', 'Date/Time format'),
            'timezone_code' => Yii::t('app', 'Timezone'),
            'dateformat_code' => Yii::t('app', 'Date format'),
            'timeformat_code' => Yii::t('app', 'Time format'),
            'currencytype_code' => Yii::t('app', 'Currency type'),

            'menu_background' => Yii::t('app', 'Menu background'),
            'menu_text_color' => Yii::t('app', 'Menu text color'),
            'message_line_color' => Yii::t('app', 'Message line color'),
            'message_line_background' => Yii::t('app', 'Message line background'),
            'section_header_color' => Yii::t('app', 'Section header color'),
            'section_header_background' => Yii::t('app', 'Section header background'),

            'chart_color_first' => Yii::t('app', 'First charts color'),
            'chart_color_second' => Yii::t('app', 'Second charts color'),
            'chart_color_third' => Yii::t('app', 'Third charts color'),
            'chart_color_fourth' => Yii::t('app', 'Fourth charts color'),
        ];
    }

    public function init()
    {
        $this->style_template = new UserStyleTemplateForm();
        parent::init();
    }
}