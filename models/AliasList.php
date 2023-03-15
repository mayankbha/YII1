<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use yii\helpers\ArrayHelper;

class AliasList extends BaseModel
{
    public static $dataLib = 'CodiacSDK.CommonArea';
    public static $dataAction = 'GetTemplateList';

    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        //$additionallyParam = ['field_out_list' => ['alias_field', 'alias_table']];
        return parent::getData($fieldList, $postData, $additionallyParam);
    }

    /**
     * Getting rights for fields
     * @param {string} $libName
     * @param {string} $functionName
     * @param {array} $fieldList - Array of fields
     * @return array
     */
    public static function getAlias($libName, $functionName, $fieldList)
    {
        self::$dataAction = 'GetTemplateList';
        $aliasTable = self::getData(['lib_name' => [$libName], 'data_source' => [$functionName]], [], ['field_out_list' => ['alias_table'], 'limitnum' => 1]);
        if (!empty($aliasTable->list)) {
            self::$dataAction = 'GetAliasList';
            $alias = self::getData(['alias_table' => [$aliasTable->list[0]['alias_table']], 'alias_field' => $fieldList], [], ['field_out_list' => ['alias_field', 'alias_rights']]);

            if (!empty($alias->list)) return ArrayHelper::map($alias->list, 'alias_field', 'alias_rights');
        }
        return [];
    }
}