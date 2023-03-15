<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;

use app\models\Screen;
use Yii;
use app\models\AccountModel;

class RecordAccessAliasFramework extends AccountModel
{
    protected static function getSourceLink()
    {
        if (!empty(Yii::$app->session['apiEndpointCustom'])) {
            return Yii::$app->session['apiEndpointCustom'];
        }
        return (YII_ENV_DEV) ? Yii::$app->params['apiEndpointCustomDev'] : Yii::$app->params['apiEndpointCustom'];
    }

    public static function lock(RecordManager $recordManager)
    {
        $model = new self();
        $request = [
            'lib_name' => $recordManager->getLibrary(),
            'func_name' => 'LockRecordAF',
            'func_param' => [
                'pk' => $recordManager->getAliasFrameworkPK(),
                'fields' => self::getFieldList($recordManager->getLibrary())
            ]
        ];

        $result = $model->processData($request);
        if ($result['requestresult'] != 'successfully') {
            return null;
        }

        return true;
    }

    public static function unlock(RecordManager $recordManager)
    {
        //echo '<pre> in unlock :: '; print_r($recordManager->getAliasFrameworkPK());

        $model = new self();
        $request = [
            'lib_name' => $recordManager->getLibrary(),
            'func_name' => 'UnlockRecordAF',
            'func_param' => [
                'pk' => $recordManager->getAliasFrameworkPK(),
                'fields' => self::getFieldList($recordManager->getLibrary())
            ]
        ];

        //json_encode($recordManager->getAliasFrameworkPK());

        Yii::info('Testing', __METHOD__);

        $result = $model->processData($request);

        if ($result['requestresult'] != 'successfully') {
            return null;
        }

        return true;
    }

    public static function getFieldList($libName)
    {
        $fields = [];
        /**
         * @var $tabData Screen
         */
        if (($tabData = Yii::$app->session['tabData']) && ($tabId = $tabData->getTabId()) && Yii::$app->session['tabData']->fieldsData[$tabId]) {
            foreach (Yii::$app->session['tabData']->fieldsData[$tabId] as $functionFields) {
                $fields = array_merge($fields, $functionFields);
            }
        }

        return $fields;
    }
}