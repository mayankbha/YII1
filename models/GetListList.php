<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

class GetListList extends BaseModel
{
    const BASE_NAME_DATE = 'Date';
    const BASE_NAME_TIME = 'Time';
    const BASE_NAME_TIMEZONE = 'TimeZone';
    const BASE_NAME_CURRENCY = 'Currency';
    const BASE_NAME_CURRENCY_TYPE = 'CurrencyType';
    const BASE_NAME_MENU_TYPE = 'MenuType';
    const BASE_NAME_USER_TYPE = 'UserType';
    const BASE_NAME_BUTTON_TYPE = 'ButtonType';
    const BASE_NAME_LANGUAGE = 'LanguageCode';
    const BASE_NAME_COUNTRY = 'CountryList';
    const BASE_NAME_STATE = 'StatesList';
    const BASE_NAME_CITY = 'CityList';
    const BASE_NAME_SECURITY_QUESTIONS = 'SQuestions';
    const BASE_NAME_AUTHORIZATION_TYPE = 'AuthType';
    const BASE_NAME_SQL_SECURITY = 'SQLSecurity';

    const BASE_NAME_EXTENSION = 'Extensions';

    public static $dataLib = 'CodiacSDK.CommonArea';
    public static $dataAction = 'GetListList';

    /**
     * Getting list from physical table of API server
     * @param string $entryName
     * @param string $listName
     * @return null
     */
    public static function getByListName($entryName, $listName)
    {
        if(!empty($entryName) && !empty($listName)){
            if ($model = static::getData(['entry_name' => [$entryName], 'list_name' => [$listName]])) return $model->list[0];
        }

        return null;
    }

    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        $model = parent::getData($fieldList, $postData, $additionallyParam);
        
        if(isset($model->list) && is_array($model->list)) {
            usort($model->list, function($a, $b) {
                $a = $a['weight'];
                $b = $b['weight'];

                if ($a == $b) return 0;
                return ($a < $b) ? -1 : 1;
            });
        }

        return $model;
    }

	public static function getDocumentCategory($document_family)
    {
		if($document_family != '') {
			$processData = [
				"func_name" => "GetDocumentFamilyList",
				"func_param" => [
					"field_name_list" => ["family_name"],
					"field_value_list" => ["family_name" => ["$document_family"]]
				],
				"lib_name" => "CodiacSDK.AdminGroups"
			];

			//echo json_encode($processData);

			$result = (new static())->processData($processData);
			return (!empty($result['record_list'])) ? (object)$result['record_list'] : null;
		}

		return null;
    }
}