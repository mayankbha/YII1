<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */
namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

class BaseModel extends AccountModel
{
    public $list;
    public $pkList;
    public $subData;
    public static $dataLib = '';
    public static $dataAction = '';

    //Change controller of API server
    protected static function getSourceLink()
    {
        if (!empty(Yii::$app->session['apiEndpointCustom'])) {
            return Yii::$app->session['apiEndpointCustom'];
        }
        return (YII_ENV == 'dev') ? Yii::$app->params['apiEndpointCustomDev'] : Yii::$app->params['apiEndpointCustom'];
    }

    /**
     * Getting data from API server
     * @param array $fieldList
     * @param array $postData
     * @param array $additionallyParam
     * @return mixed
     */
    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        if (empty($postData)) $postData = [
            'lib_name' => static::$dataLib,
            'func_name' => static::$dataAction
        ];

        if (empty($fieldList)) {
            $funcParam = [
                'field_name_list' => [],
                'field_value_list' => []
            ];
        } else if (!ArrayHelper::isAssociative($fieldList))  {
            $funcParam = [
                'field_name_list' => $fieldList
            ];
        } else {
            $funcParam = [
                'field_name_list' => array_keys($fieldList),
                'field_value_list' => $fieldList
            ];
        }
        $funcParam += $additionallyParam;
        $postData += ['func_param' => $funcParam];

        $model = new static();
        $attributes = $model->processData($postData);

        if (!empty($attributes['sub_array'])) {
            $model->subData = $attributes['sub_array'];
        }

        if (!empty($attributes['record_list'])) {
            $model->list = $attributes['record_list'];

            if (!empty($attributes['record_list_pk'])) {
                $model->pkList = $attributes['record_list_pk'];
            }

            return $model;
        }

        return null;
    }

	public static function getData2($fieldList = [], $postData = [], $additionallyParam = [])
    {
		//echo 'in BaseModel getData2 :: ';
		//echo ':: $dataLib :: '.static::$dataLib.' ::';
		//echo ':: $dataAction :: '.static::$dataAction.' ::';

        if (empty($postData)) $postData = [
            'lib_name' => static::$dataLib,
            'func_name' => static::$dataAction
        ];

        if (empty($fieldList)) {
            $funcParam = [
                'field_name_list' => [],
                'field_value_list' => []
            ];
        } else if (!ArrayHelper::isAssociative($fieldList))  {
            $funcParam = [
                'field_name_list' => $fieldList
            ];
        } else {
            $funcParam = [
                'field_name_list' => array_keys($fieldList),
                'field_value_list' => $fieldList
            ];
        }
        $funcParam += $additionallyParam;
        $postData += ['func_param' => $funcParam];

        $model = new static();
        $attributes = $model->processData2($postData);

        if (!empty($attributes['record_list'])) {
            $model->list = $attributes['record_list'];

            if (!empty($attributes['record_list_pk'])) {
                $model->pkList = $attributes['record_list_pk'];
            }

            return $model;
        }

        return null;
    }

    /**
     * @param $pk string
     * @param $postData array
     * @return array|null
     */
    public static function getModel($pk, $postData = [])
    {
        if (empty($postData)) {
            $postData = [
                'lib_name' => static::$dataLib,
                'func_name' => static::$dataAction
            ];
        }

        $fieldList = [];
        $pk = explode(';', $pk);
        $pkList = CustomLibs::getPK($postData['lib_name'], $postData['func_name']);
        if (!empty($pkList)) {
            foreach ($pkList as $key => $item) {
                if (empty($pk[$key])) {
                    continue;
                }
                
                $fieldList[$item] = [$pk[$key]];
            }

            if ($model = static::getData($fieldList, $postData)) return $model->list[0];
        }

        return null;
    }

    public static function updateModel($id, $attributes)
    {
        $functionName = CustomLibs::getFunctionName(static::$dataLib, static::$dataAction, 'Update');

        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => $functionName,
            'func_param' => [
                'PK' => (string) $id,
                'patch_json' => $attributes
            ]
        ]);
    }

    public static function setModel($attributes)
    {
        $functionName = CustomLibs::getFunctionName(static::$dataLib, static::$dataAction, 'Create');

        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => $functionName,
            'func_param' => [
                'patch_json' => $attributes
            ]
        ]);
    }

	public static function setModel2($attributes)
    {
        $functionName = CustomLibs::getFunctionName(static::$dataLib, static::$dataAction, 'Create');

        return (new static())->processData2([
            'lib_name' => static::$dataLib,
            'func_name' => $functionName,
            'func_param' => [
                'patch_json' => $attributes
            ]
        ]);
    }

    public function isSuccess()
    {
        return isset($this->list);
    }

    public function getList()
    {
        return ($this->isSuccess()) ? $this->list : [];
    }

    public function getOne()
    {
        return ($list = $this->getList()) ? $list[0] : [];
    }
}