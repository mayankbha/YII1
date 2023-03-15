<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;
use Yii;


class User extends BaseModel
{
    public static $findAttribute = 'PK';
    public static $dataLib = 'CodiacSDK.AdminUsers';
    public static $dataAction = 'UpdateUser';

    /**
     * Prepare data for update user interface settings
     * @param array $attributes
     * @param null|string $method
     * @return mixed
     */
    protected static function prepareData($attributes, $method = null) {
        if (!empty($attributes['account_password'])) unset($attributes['account_password']);
        if (!empty($attributes['group_area'])) unset($attributes['group_area']);
        if (!empty($attributes['document_group'])) unset($attributes['document_group']);
        if (!empty($attributes['last_login'])) unset($attributes['last_login']);

        foreach($attributes as $key => $item) {
            if ($item === null) unset($attributes[$key]);
        }

        return $attributes;
    }

    public static function updateModel($userSettings, $model)
    {
        $pk = CustomLibs::getPK(static::$dataLib, static::$dataAction);
        $pkValues = [];
        foreach ($pk as $item) {
            if (!empty($userSettings[$item])) $pkValues[] = $userSettings[$item];
        }
        $pkValues = implode(';', $pkValues);

        $attributes = static::prepareData($model->attributes, __FUNCTION__);
        $attributes = static::cleanAttributes($attributes);

        $userModel = Yii::$app->session['screenData'][UserAccount::class];
        $userModel->style_template = $attributes;

        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => static::$dataAction,
            'func_param' => [
                static::$findAttribute => $pkValues,
                'patch_json' => array(
                    'style_template' => base64_encode(json_encode($attributes))
                )
            ]
        ]);
    }

    public static function updateModelPassword($password)
    {
        $pk = CustomLibs::getPK(static::$dataLib, static::$dataAction);
        $pkValues = [];
        $userSettings = (array)Yii::$app->session['screenData'][UserAccount::class];
        foreach ($pk as $item) {
            if (!empty($userSettings[$item])) $pkValues[] = $userSettings[$item];
        }

        $pkValues = implode(';', $pkValues);

		$attributes = ['account_password'=>$password,'force_change'=>'N'];

        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => static::$dataAction,
            'func_param' => [
                static::$findAttribute => $pkValues,
                'patch_json' => $attributes
            ]
        ]);
    }

    public static function getDefaultSettings() {
        $model = new parent();
        $attributes = $model->processData([
            "func_name" => "GetDefaultColors",
            "func_param" => null,
            "lib_name" => "CodiacSDK.CommonArea"
        ]);

        $default = [
            'border_size' => '1px',
            'background_color' => '#f8f8f8',
            'border_color' => '#cccccc',
            'header_border_size' => '0px',
            'section_border_size' => '1px',

            'header_border_color' => '#f4f2f2',
            'tab_selected_color'=> '#cccccc',
            'section_background_color' => '#f8f8f8',

            'text_color' => '#000000',
            'link_color' => '#000000',
            'info_color' => '#000000',
            'header_color' => '#f8f8f8',

            'search_border_color' => '#f4f2f2',
            'tab_unselected_color' => '#f4f2f2',
            'highlight_color_selection' => '#dddddd',

            'menu_background' => '#f8f8f8',
            'message_line_color' => '#000000',
            'section_header_color' => '#000000',

            'message_line_background' => '#f8f8f8',
            'field_border_color' => '#dddddd',
            'field_border_selected_color' => '#4a86e8',
            'search_border_selected_color' => '#4a86e8',
            'section_header_background' => '#f5f5f5',
            'menu_text_color' => '#000000',
            'chart_color_first' => '#5ce25c',
            'chart_color_second' => '#76a5af',
            'chart_color_third' => '#ea9999',
            'chart_color_fourth' => '#ffd966',

        ];

        if (!empty($attributes['defcolors']['style_template'])) {
            $default = array_merge($default, Screen::decodeTemplate($attributes['defcolors']['style_template']));
        }

        return $default;
    }

    protected static function cleanAttributes($attributes)
    {
        $temporaryAttributes = ['background_image_body', 'menu_background_image_body', 'avatar_body'];
        return array_diff_key($attributes, array_flip($temporaryAttributes));
    }
}