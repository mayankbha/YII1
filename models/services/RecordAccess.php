<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;

use app\models\AccountModel;
use app\models\CustomLibs;

class RecordAccess extends AccountModel
{
    public static function lock(RecordManager $recordManager, $getFunction)
    {
        $model = new self();
        $table = CustomLibs::getTableName($recordManager->getLibrary(), $getFunction);
        $request = [
            'func_name' => 'LockRecord',
            'func_param' => [
                'pk' => $recordManager->getPK(),
                'table' => $table
            ]
        ];

        $result = $model->processData($request);
        if ($result['requestresult'] != 'successfully') {
            return null;
        }

        return true;
    }

    public static function unlock(RecordManager $recordManager, $getFunction)
    {
        $model = new self();
        $table = CustomLibs::getTableName($recordManager->getLibrary(), $getFunction);
        $request = [
            'func_name' => 'UnlockRecord',
            'func_param' => [
                'pk' => $recordManager->getPK(),
                'table' => $table
            ]
        ];

        $result = $model->processData($request);
        if ($result['requestresult'] != 'successfully') {
            return null;
        }

        return true;
    }
}