<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use Yii;

class CustomLibs extends AccountModel
{
    const DIRECTION_SETTER = 'SETTER';
    const DIRECTION_GETTER = 'GETTER';
    const LAYOUT_TYPE_MULTI_SEARCH = 'MULTI-SEARCH';

    const CASH_NAME_FUNCTION_LIST = 'libFunctionList';
    const RELATED_FUNCTION_DELIMITER = ';';

    public static $disabled_layout_types = [
        self::LAYOUT_TYPE_MULTI_SEARCH
    ];

    public static $dataAction = 'GetSDKList';

    /**
     * Getting library functions list from API server
     * @return array
     */
    public static function getSDKList()
    {
        if (empty(Yii::$app->session[self::CASH_NAME_FUNCTION_LIST])) {
            if ($list = self::getModelInstance()) {
                return Yii::$app->session[self::CASH_NAME_FUNCTION_LIST] = $list->lib_list;
            }

            return [];
        }

        return Yii::$app->session[self::CASH_NAME_FUNCTION_LIST];
    }

    public static function getTableName($libName, $functionGetName)
    {
        $libFunctionList = self::getLibFuncList($libName);

        foreach ($libFunctionList as $key => $libInfo) {
            if ($libInfo['func_name'] == $functionGetName) {
                return $libInfo['func_table'];
            }
        }

        return null;
    }

	 public static function getTableName2($libName, $functionGetName)
    {
        $libFunctionList = self::getLibFuncList2($libName);

        foreach ($libFunctionList as $key => $libInfo) {
            if ($libInfo['func_name'] == $functionGetName) {
                return $libInfo['func_table'];
            }
        }

        return null;
    }

    public static function getFunctionName($libName, $functionGetName, $type)
    {
        $tableName = self::getTableName($libName, $functionGetName);

        $libFunctionList = self::getLibFuncList($libName, CustomLibs::DIRECTION_SETTER);

        foreach ($libFunctionList as $libInfo) {
            if ($libInfo['func_type'] == $type && $libInfo['func_table'] == $tableName) {
                return $libInfo['func_name'];
            }
        }

        return null;
    }

    /**
     * Getting library functions list from API server
     * @param string $libName
     * @param bool $direction - Returned function type
     * @return array
     */
    public static function getLibFuncList($libName, $direction = false)
    {
        if ($libData = self::getSDKList()) {
            foreach ($libData as $val) {
                if (strtolower($val['lib_name']) == strtolower($libName)) {
                    if ($direction === self::DIRECTION_SETTER || $direction === self::DIRECTION_GETTER) {
                        foreach ($val['lib_func_list'] as $key => $item) {
                            if ($item['func_direction_type'] != $direction) unset($val['lib_func_list'][$key]);
                            else if (in_array($item['func_layout_type'], self::$disabled_layout_types)) unset($val['lib_func_list'][$key]);
                        }
                    } else if ($direction === self::LAYOUT_TYPE_MULTI_SEARCH) {
                        foreach ($val['lib_func_list'] as $key => $item) {
                            if ($item['func_layout_type'] != $direction) unset($val['lib_func_list'][$key]);
                        }
                    }

                    return $val['lib_func_list'];
                }
            }
        }

        return [];
    }

	public static function getLibFuncList2($libName, $direction = false)
    {
        if ($libData = self::getSDKList()) {
            foreach ($libData as $val) {
                if (strtolower($val['lib_name']) == strtolower($libName)) {
                    if ($direction === self::DIRECTION_SETTER || $direction === self::DIRECTION_GETTER) {
                        foreach ($val['lib_func_list'] as $key => $item) {
                            if ($item['func_direction_type'] != $direction) unset($val['lib_func_list'][$key]);
                            else if (in_array($item['func_layout_type'], self::$disabled_layout_types)) unset($val['lib_func_list'][$key]);
                        }
                    } else if ($direction === self::LAYOUT_TYPE_MULTI_SEARCH) {
                        foreach ($val['lib_func_list'] as $key => $item) {
                            if ($item['func_layout_type'] != $direction) unset($val['lib_func_list'][$key]);
                        }
                    } else if(!$direction) {
						/*$direction = self::DIRECTION_GETTER;

						foreach ($val['lib_func_list'] as $key => $item) {
                            //if ($item['func_direction_type'] != $direction) unset($val['lib_func_list'][$key]);
                            //else if (in_array($item['func_layout_type'], self::$disabled_layout_types)) unset($val['lib_func_list'][$key]);
                        }*/
					}

                    return $val['lib_func_list'];
                }
            }
        }

        return [];
    }

    /**
     * Getting function params from library
     * @param string $libName
     * @param string $funcName
     * @return bool|mixed
     */
    public static function getLibFuncParamList($libName, $funcName)
    {
        $libList = self::getLibFuncList($libName);
        foreach ($libList as $val) {
            if ($val['func_name'] == $funcName) {
                return $val;
            }
        }

        return false;
    }

    public static function getRelated($libName, $functionName)
    {
        $libFunctionList = CustomLibs::getLibFuncList($libName);

        foreach ($libFunctionList as $libInfo) {
            if ($libInfo['related_func']) {
                $functionList = explode(self::RELATED_FUNCTION_DELIMITER, $libInfo['related_func']);
                $index = array_search($functionName, $functionList);
                if ($index !== false) {
                    $relatedFieldList = explode(self::RELATED_FUNCTION_DELIMITER, $libInfo['related_field']);
                    return $relatedFieldList[$index];
                }
            }
        }

        return null;
    }

    public static function getPK($libName, $functionName) {
        $tableName = self::getTableName($libName, $functionName);
        $postData = [
            "func_name" => "GetPKS",
            "func_param" => [
                "table_name" => $tableName
            ]
        ];

        $result = (new static())->processData($postData);

        if (!empty($result['record_list']['PK'])) return explode(';', $result['record_list']['PK']);
        return [];
    }
	
	public static function getPK2($libName, $functionName) {
        $tableName = self::getTableName2($libName, $functionName);
		
        $postData = [
            "func_name" => "GetPKS",
            "func_param" => [
                "table_name" => $tableName
            ]
        ];

        $result = (new static())->processData2($postData);

        if (!empty($result['record_list']['PK'])) {
			if(is_array($result['record_list']['PK'])) return explode(';', $result['record_list']['PK']);
			else return array($result['record_list']['PK']);
		}
        return [];
    }
}