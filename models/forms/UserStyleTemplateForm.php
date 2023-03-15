<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\forms;

use app\models\Image;
use app\models\User;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

/**
 * Class UserStyleTemplateForm
 * @property ImageForm $avatar
 * @property ImageForm $background_image
 * @property ImageForm $menu_background_image;
 */
class UserStyleTemplateForm extends Model
{
    public $background_color = '#ffffff';
    public $background_image;
    public $background_image_body;
    public $border_color = '#ddd';
    public $border_size  = '1px';
    public $info_color = '#8a6d3b';
    public $link_color = '#0000ff';
    public $text_color = '#000';
    public $header_border_size = '1px';
    public $header_color = '#fcf8e3';
    public $header_border_color = '#faebcc';
    public $search_border_color = '#ccc';
    public $tab_selected_color = '#ddd';
    public $tab_unselected_color = '#ffffff';
    public $section_background_color = '#ffffff';
    public $highlight_color_selection = '#dddddd';
	public $section_border_size = '1px';
    public $menu_background;
    public $menu_background_image;
    public $menu_background_image_body;
    public $message_line_color;
    public $section_header_color;
    public $field_border_color;
    public $search_border_selected_color;
    public $field_border_selected_color;
    public $menu_text_color;
    public $message_line_background;
    public $section_header_background;
    public $chart_color_first;
    public $chart_color_second;
    public $chart_color_third;
    public $chart_color_fourth;
    public $avatar;
    public $avatar_body;
    public $avatar_array = [];
    public $background_image_array = [];
    public $menu_background_image_array = [];
    public $use_body_images = true;
    public $use_menu_images = true;

    public function __construct(array $config = [])
    {
        $config = array_merge(User::getDefaultSettings(), $config);
        $config = $this->removeUnusingProperties($config);
        parent::__construct($config);

        $arrayList = [&$this->avatar_array, &$this->background_image_array, &$this->menu_background_image_array];

        //echo '<pre>'; print_r($config);

        foreach($arrayList as $key => $pkList) {
            foreach ($pkList as $number => $pk) {
                $arrayList[$key][$number] = Image::getModel($pk);
            }
        }

        //echo '<pre>'; print_r($arrayList);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [[
            [
                'background_color',
                'background_image',
                'border_color',
                'border_size',
                'info_color',
                'link_color',
                'text_color',
                'header_border_size',
                'header_color',
                'header_border_color',
                'search_border_color',
                'tab_selected_color',
                'tab_unselected_color',
                'section_background_color',
                'highlight_color_selection',
                'menu_background',
                'menu_background_image',
                'message_line_color',
                'section_header_color',
                'field_border_color',
                'search_border_selected_color',
                'field_border_selected_color',
                'menu_text_color',
                'message_line_background',
                'section_header_background',
                'chart_color_first',
                'chart_color_second',
                'chart_color_third',
                'chart_color_fourth',
                'avatar',
				'section_border_size'
            ],
            'string'
        ],
        [['use_body_images', 'use_menu_images'], 'boolean']];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'text_color' => Yii::t('app', 'Text color'),
            'link_color' => Yii::t('app', 'Link color'),
            'info_color' => Yii::t('app', 'Info color'),
            'border_size' => Yii::t('app', 'Border size'),
            'border_color' => Yii::t('app', 'Border color'),
            'background_color' => Yii::t('app', 'Background color'),
            'background_image' => Yii::t('app', 'Background image'),
            'header_border_size' => Yii::t('app', 'Header border size'),
            'header_color' => Yii::t('app', 'Header color'),
            'header_border_color' => Yii::t('app', 'Header border color'),
            'search_border_color' => Yii::t('app', 'Search border color'),
            'tab_selected_color' => Yii::t('app', 'Tab selected color'),
            'tab_unselected_color' => Yii::t('app', 'Tab unselected color'),
            'section_background_color' => Yii::t('app', 'Section background color'),
            'highlight_color_selection' => Yii::t('app', 'Highlight Color selection'),
            'menu_background' => Yii::t('app', 'Menu background color'),
            'menu_background_image' => Yii::t('app', 'Menu background image'),
            'message_line_color' => Yii::t('app', 'Message line color'),
            'section_header_color' => Yii::t('app', 'Section header color'),
            'search_border_selected_color' => Yii::t('app', 'Highlight of selected search'),
            'field_border_color' => Yii::t('app', 'Field borders color'),
            'field_border_selected_color' => Yii::t('app', 'Highlight of selected field'),
            'menu_text_color' => Yii::t('app', 'Menu text color'),
            'message_line_background' => Yii::t('app', 'Message line background'),
            'section_header_background' => Yii::t('app', 'Section header background'),
            'chart_color_first' => Yii::t('app', 'First charts color'),
            'chart_color_second' => Yii::t('app', 'Second charts color'),
            'chart_color_third' => Yii::t('app', 'Third charts color'),
            'chart_color_fourth' => Yii::t('app', 'Fourth charts color'),
            'avatar' => Yii::t('app', 'Avatar'),
			'section_border_size' => Yii::t('app', 'Section Border Size'),
        ];
    }

    public function prepareImageAttributes()
    {
        $attrList = [
            'avatar_array',
            'background_image_array',
            'menu_background_image_array',
        ];

        foreach($attrList as $attr) {
            $this->$attr = ArrayHelper::getColumn($this->$attr, 'pk');
        }
    }

    /**
     * @param array $properties
     * @return array
     */
    public function removeUnusingProperties(array $properties)
    {
        foreach (array_keys($properties) as $property) {
            if (!property_exists(self::class, $property)) {
                unset($properties[$property]);
            }
        }
        return $properties;
    }
}