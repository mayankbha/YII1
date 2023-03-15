<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use stdClass;
use yii\helpers\ArrayHelper;

class Report extends BaseModel
{
    public static $dataLib = 'CodiacSDK.Report';
    public static $dataAction = 'GetReports';

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
        $pkList = ['id'];
        if (!empty($pkList)) {
            foreach ($pkList as $key => $item) {
                if (empty($pk[$key])) {
                    continue;
                }

                $fieldList[$item] = [(int) $pk[$key]];
            }

            if ($model = static::getData($fieldList, $postData)) return $model->list[0];
        }

        return null;
    }

    public static function generate($id, $primaryTable, $search_function_info, $isBatch = false, $batch = [])
    {
        $postData = [
            'lib_name' => static::$dataLib,
            'func_name' => 'GenerateReportAF',
            'func_param' => [
                'report_id' => $id,
                'request_primary_table' => $primaryTable,
                'batch_generation' => "false",
                'search_function_info' => $search_function_info
            ]
        ];

        if ($isBatch) {
            $userSettings = UserAccount::getSettings();

            $postData['func_param']['batch_generation'] = "true";
            $postData['func_param']['custom_sql_security_level'] = $userSettings->custom_sql_security_level;
            $postData['func_param']['sql_params'] = $batch ? $batch : new stdClass();
        }

        $model = new static();
        $result = $model->processData($postData);

        return isset($result['record_list']) ? ArrayHelper::getColumn($result['record_list'], 'pk') : null;
    }
}