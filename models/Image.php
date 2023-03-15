<?php
namespace app\models;

use app\models\forms\ImageForm;
use yii\helpers\ArrayHelper;

class Image extends BaseModel
{
    const INSERT_FUNC_TYPE = 'Create';
    const DELETE_FUNC_TYPE = 'Delete';

    public static $dataLib = 'CodiacSDK.AdminUsers';
    public static $dataAction = 'GetLogoList';
    public static $formClass = ImageForm::class;

    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        if (empty($fieldList)) {
            $fieldList = ['type' => [ImageForm::TYPE_LOGO_HEADER, ImageForm::TYPE_LOGO_MAIN]];
        }

        return parent::getData($fieldList, $postData, $additionallyParam);
    }

    protected static function prepareData($attributes, $method = null) {
        if (!empty($attributes['logo_image_body'])) {
            $attributes['logo_image_body'] = base64_encode(file_get_contents($attributes['logo_image_body']->tempName));
        } else {
            ArrayHelper::remove($attributes, 'logo_image_body');
        }
        
        unset($attributes['id']);
        unset($attributes['pk']);

        return $attributes;
    }

    public static function getImageBody($attribute)
    {
        return !empty($attribute['logo_image_body']) ? $attribute['logo_image_body'] : null;
    }

    public static function getModel($pk, $postData = array())
    {
        //echo $pk;

        $fieldList = [];
        $cleanPK = explode(';', $pk);
        $pkList = CustomLibs::getPK(static::$dataLib, static::$dataAction);

        //echo '<pre>'; print_r($pkList);

        if (!empty($pkList)) {
            foreach ($pkList as $key => $item) {
                if (!empty($cleanPK[$key])) {
                    $fieldList[$item] = [$cleanPK[$key]];
                }
            }

            //echo '<pre>'; print_r($fieldList);

            if ($selfModel = static::getData($fieldList)) {
                $model = new static::$formClass();
                if ($model->load($selfModel->list[0], '')) {
                    $model->pk = $pk;
                    return $model;
                }
            }
        }

        return null;
    }


    public static function deleteModel($id)
    {
        $functionName = CustomLibs::getFunctionName(static::$dataLib, static::$dataAction, self::DELETE_FUNC_TYPE);
        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => $functionName,
            'func_param' => [
                'PK' => (string)$id
            ]
        ]);
    }

    public static function setModel($model)
    {
        $attributes = static::prepareData($model->attributes, __FUNCTION__);
        $functionName = CustomLibs::getFunctionName(static::$dataLib, static::$dataAction, self::INSERT_FUNC_TYPE);

        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => $functionName,
            'func_param' => [
                'patch_json' => $attributes
            ]
        ]);
    }
}