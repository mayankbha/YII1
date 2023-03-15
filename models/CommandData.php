<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use app\models\services\RecordAccess;
use app\models\services\RecordAccessAliasFramework;
use app\models\services\RecordData;
use app\models\services\RecordSubData;
use app\models\services\RecordManager;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class CommandData extends BaseModel
{
    const SEARCH_LIMIT = 15;
    const SEARCH_TYPE_DEFAULT = 1;
    const SEARCH_TYPE_CUSTOM_QUERY = 2;

    const SEARCH_CONFIG_CACHE_NAME = 'searchConfig';
    const AF_PK_INFO_CACHE_NAME = 'PKAliasFrameworkInfo';

    const SEARCH_FUNC_TYPE = 'Search';
    const INSERT_FUNC_TYPE = 'Create';
    const UPDATE_FUNC_TYPE = 'Update';
    const DELETE_FUNC_TYPE = 'Delete';

    public static $insertSubFuncName = 'CreateSub';
    public static $updateSubFuncName = 'UpdateSub';
    public static $deleteSubFuncName = 'DeleteSub';

    public static function search($library, $queries, array $aliasFrameworkInfo)
    {
        $config = Yii::$app->session[self::SEARCH_CONFIG_CACHE_NAME];
        //echo '<pre>'; print_r($config); die;
        if (!empty($config[$library]['custom'])) {
            return self::searchCustom($queries, $config[$library]['custom']);
        } elseif (!empty($config[$library]['default'])) {
            return self::searchDefault($library, $queries, $config[$library]['default'], [], $aliasFrameworkInfo);
        }

        return [];
    }

    public static function searchDefault($library, array $queries, $config, array $additionalParams = [], array $aliasFrameworkInfo = ['enable' => false])
    {
        if (empty($config->func_inparam_configuration) || empty($config->data_source_get)) {
            return [];
        }

		$outParams = [];
		$searchMaskList = [];
		$aliasFrameworkInfo['enable'] = filter_var($aliasFrameworkInfo['enable'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

		foreach ($queries as $key => $mask) {
			if (!is_array($mask)) {
				$mask = [$mask];
			}

			$outParams[] = $key;
			$searchMaskList[$key] = $mask;
		}

		if ($aliasFrameworkInfo['enable'] && !empty($config->pk_configuration) && is_array($config->pk_configuration)) {
			$primaryKeys = $config->pk_configuration;
        } else if(!empty($config->pk_configuration) && is_array($config->pk_configuration)) {
            $primaryKeys = $config->pk_configuration;
		} else {
			$primaryKeys = CustomLibs::getPK($library, $config->data_source_get);
		}

		foreach ($config->func_inparam_configuration as $key => $value) {
			$config->func_inparam_configuration[$key] = self::fixedApiResult($value, $aliasFrameworkInfo['enable']);
		}

		if (!empty($primaryKeys)) { //echo '<pre>'; print_r($primaryKeys);
           // $primaryKeys = explode(';', $primaryKeys[0]);

			$outParams = array_merge($outParams, $primaryKeys);
		}

		$outParams = array_values(array_unique($outParams));

        //echo '<pre> outParams after array_values'; print_r($outParams);

		$additionalParams = array_merge([
			'limitnum' => self::SEARCH_LIMIT,
			'search_mask_list' => $searchMaskList,
			'field_out_list' => $outParams
		], $additionalParams);

		$postData = [
			'lib_name' => $library,
			'func_name' => $config->data_source_get
		];

		if ($aliasFrameworkInfo['enable']) {
			$postData['alias_framework_info'] = $aliasFrameworkInfo;
		}

		$response = self::getData(array_keys($queries), $postData, $additionalParams);

		if (!empty($response->list) && !empty($primaryKeys)) {
			foreach ($response->list as $listKey => $item) {
				$response->list[$listKey]['id'] = [];
				foreach ($primaryKeys as $pk) {
					$response->list[$listKey]['id'][$pk] = $item[$pk];
				}
			}

			return $response->list;
		}

        return [];
    }
//ALEX G CHANGES FOR CHARTS  08/11/2020
    public static function searchCustom(array $queries, $config, $returnAllRecords = false, $query_param = null)
    {
        if (empty($config->query_pk)) {
            return [];
        }

        //echo '<pre>'; print_r($queries);

        $library = 'CodiacSDK.CommonArea';
        $getQueriesAction = 'GetCustomQueryList';
        $executeAction = 'ExecuteCustomQuery';

        $model = self::getModel($config->query_pk, [
            'lib_name' => $library,
            'func_name' => $getQueriesAction
        ]);

        if (!empty($model['query_params'])) {
            $queryParams = explode(';', $model['query_params']);
            foreach ($queryParams as $param) {
                $param = trim($param);
                if (empty($queries[$param])) {
                    $queries[$param] = '';
                }
            }
        }

        $sqlParams = [];

        foreach ($queries as $key => $item) {
            if (strpos($key, '.')) {
                $key = substr($key, strrpos($key, '.') + 1, strlen($key));
            }

            if (!isset($sqlParams[$key]) || !empty($item)) {
                $sqlParams[$key] = $item;
            }
        }

        if (empty($sqlParams)) {
            $sqlParams = null;
        }

        $userSettings = UserAccount::getSettings();

        $response = (new static())->processData([
            "lib_name" => $library,
            'func_name' => $executeAction,
            "func_param" => [
                "PK" => $config->query_pk,
                "sql_params" => $sqlParams,
                "custom_sql_security_level" => $userSettings->custom_sql_security_level
            ]
        ]);

        $result = [];

        //echo '<pre>'; print_r($query_param);

        if (!empty($response['record_list'])) {
            if (!$returnAllRecords) {
                foreach ($response['record_list'] as $key => $item) {
                    if (!empty($config->alias_query_pk) && !empty($item['id'])) {
                        $result[$key]['id'] = ['id' => $item['id']];
                    } else {
                        $pkList = [];
                        foreach ($config->alias_query_pk as $pkName) {
                            if ($dotPosition = strrpos($pkName, '.')) {
                                $pkName = substr($pkName, $dotPosition + 1, strlen($pkName));
                            }
                            $pkList[$pkName] = (isset($item[$pkName])) ? $item[$pkName] : null;
                        }
                        $result[$key]['id'] = $pkList;
                    }

                    foreach ($queries as $param => $value) {
                        $pkName = $param;
                        if ($dotPosition = strrpos($param, '.')) {
                            $pkName = substr($param, $dotPosition + 1, strlen($param));
                        }

                        $pkName = trim($pkName, ':');
                        $result[$key][$param] = isset($item[$pkName]) ? $item[$pkName] : null;
                    }
                }
            } else {
                foreach ($response['record_list'] as $key => $item) {
                    $result[$key] = $item;

// ALEX G CHART CHANGES 08/11/2020
//                    if(isset($query_param[0]['query_param']) && $query_param[0]['query_param'] != '') {
                    if(!empty($query_param[0]['query_param'])) {
                        //$query_param_explode = explode('[', $query_param[0]['query_param']);
                        //end($query_param_explode);
                        //prev($query_param_explode);

                        $end = strrpos($query_param[0]['query_param'], '[');
                        $final_query_param = substr($query_param[0]['query_param'], 0, $end);

                        $result[$key]['query_param'] = $final_query_param;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Update data on API server
     *
     * @param RecordData $data
     * @param RecordSubData $updateSubData
     * @param RecordSubData $deleteSubData
     * @param RecordSubData $insertSubData
     * @param array $approvedMessagesCode
     *
     * @return mixed
     */
    public static function update(RecordData $data, RecordSubData $updateSubData, RecordSubData $insertSubData, RecordSubData $deleteSubData, array $approvedMessagesCode = [])
    {
        $model = new static();
        $tpl = Yii::$app->session['tabData']->getSelectTpl();

        $mainData = $data->getData();

        //echo '<pre>$data ::'; print_r($data);
        //echo '<pre>$mainData ::'; print_r($mainData);

        if ($data->recordManager->isUseAliasFramework()) {
            $updateSubData->setMainData($mainData);
            $insertSubData->setMainData($mainData);
            $deleteSubData->setMainData($mainData);

            $mainData = array_merge_recursive(
                $mainData,
                $updateSubData->getUpdateDataAF(RecordManager::ITEM_ATTR_UPDATE_FUNC),
                $insertSubData->getUpdateDataAF(RecordManager::ITEM_ATTR_UPDATE_FUNC),
                $deleteSubData->getUpdateDataAF(RecordManager::ITEM_ATTR_UPDATE_FUNC)
            );
        }

        //echo '<pre>After :: '; print_r($mainData);

        foreach ($mainData as $function => $items) {
            $request = [
                "lib_name" => $data->recordManager->getLibrary(),
                "func_param" => [
                    "patch_json" => (object) $items
                ]
            ];

            $request['func_param']['screen_tabs'] = array_keys(Yii::$app->session['tabData']->tplData);

            if ($data->recordManager->isUseAliasFramework()) {
                $request['alias_framework_info'] = $data->recordManager->getAliasFrameworkInfo();
                $request['func_name'] = $function;
                $request['func_param']['PK'] = $data->recordManager->getAliasFrameworkPK();
            } else {
                $request['func_name'] = CustomLibs::getFunctionName($data->recordManager->getLibrary(), $function, self::UPDATE_FUNC_TYPE);
                $request['func_param']['PK'] = $data->recordManager->getPK();
            }

            if (!empty($tpl['tpl']->screen_extensions['edit']['pre'])) {
                $request['func_param']['func_extensions_pre'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['edit']['pre']);
            }

            if (!empty($approvedMessagesCode)) {
                $request['func_param']['confirmed_messages'] = $approvedMessagesCode;
            }

            if (!empty($tpl['tpl']->screen_extensions['edit']['post'])) {
                $request['func_param']['func_extensions_post'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['edit']['post']);
            }

            if ($notifications = $data->getNotifications()) {
                $request['func_param']['func_extensions_action'][0]['CodiacSDK.NotifyProcessor;SendNotifycations_Async'] = [];
                $request['func_param']['func_extensions_action_params']['CodiacSDK.NotifyProcessor;SendNotifycations_Async'] = $notifications;
            }

            $result = $model->processData($request);

            if ($data->recordManager->isUseAliasFramework()) {
                //echo '<pre> in commandData update :: '; print_r($data->recordManager->getAliasFrameworkPK());

                RecordAccessAliasFramework::unlock($data->recordManager);
            } else {
                self::updateSub($updateSubData);
                self::insertSub($insertSubData);
                self::deleteSub($deleteSubData);
                RecordAccess::unlock($data->recordManager, $function);
            }

            if (($result['requestresult'] ?? '') != 'successfully') {
                return $result ?? null;
            }
        }

        return true;
    }

    /**
     * Insert data to library on API server
     *
     * @param RecordData $data
     * @param RecordSubData $subData
     * @param array $approvedMessagesCode
     *
     * @return array|null
     */
    public static function insert(RecordData $data, RecordSubData $subData, array $approvedMessagesCode)
    {
        $model = new static();
        $tpl = Yii::$app->session['tabData']->getSelectTpl();

        $mainData = $data->getData();
        if ($data->recordManager->isUseAliasFramework()) {
            $subData->setMainData($mainData);
            $mainData = array_merge_recursive($mainData, $subData->getUpdateDataAF(RecordManager::ITEM_ATTR_CREATE_FUNC));
        }

        foreach ($mainData as $function => $items) {
            $request = [
                "lib_name" => $data->recordManager->getLibrary(),
                "func_param" => [
                    "patch_json" => $items
                ]
            ];

            $request['func_param']['screen_tabs'] = array_keys(Yii::$app->session['tabData']->tplData);

            if ($data->recordManager->isUseAliasFramework()) {
                $request['alias_framework_info'] = $data->recordManager->getAliasFrameworkInfo();
                $request['func_name'] = $function;

                if (!empty($tpl['tpl']->search_custom_query)) {
                    $request['search_function_info']['config'] = $tpl['tpl']->search_custom_query;
                } elseif (!empty($tpl['tpl']->search_configuration)) {
                    $request['search_function_info']['config']  = $tpl['tpl']->search_configuration;
                }
            } else {
                $request['func_name'] = CustomLibs::getFunctionName($data->recordManager->getLibrary(), $function, self::INSERT_FUNC_TYPE);
            }

            if (!empty($tpl['tpl']->screen_extensions['add']['pre'])) {
                $request['func_param']['func_extensions_pre'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['pre']);
            }

            if (!empty($approvedMessagesCode)) {
                $request['func_param']['confirmed_messages'] = $approvedMessagesCode;
            }

            if (!empty($tpl['tpl']->screen_extensions['add']['post'])) {
                $request['func_param']['func_extensions_post'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['post']);
            }

            if ($notifications = $data->getNotifications()) {
                $request['func_param']['func_extensions_action'][]['CodiacSDK.NotifyProcessor;SendNotifycations_Async'] = [];
                $request['func_param']['func_extensions_action_params']['CodiacSDK.NotifyProcessor;SendNotifycations_Async'] = $notifications;
            }

            $result = $model->processData($request);

            if (($result['requestresult'] ?? '') != 'successfully') {
                return $result ?? null;
            }
        }

        if (!empty($result['record_list']['PK'])) {
            $insertedPK = explode(RecordManager::PK_DELIMITER, $result['record_list']['PK']);
            if (!$data->recordManager->isUseAliasFramework()) {
                $subData->recordManager->setPK($insertedPK);
                self::insertSub($subData);
                $pkFields = CustomLibs::getPK($data->recordManager->getLibrary(), $function);
            } else {
                $pkFields = [];
                if (!empty($tpl['tpl']->search_configuration->pk_configuration)) {
                    $pkFields = $tpl['tpl']->search_configuration->pk_configuration;
                } else if (!empty($tpl['tpl']->search_custom_query->alias_query_pk)) {
                    $pkFields = $tpl['tpl']->search_custom_query->alias_query_pk;
                }
            }

            if (isset($function) && !empty($pkFields)) {
                $pk = [];
                foreach ($pkFields as $i => $fieldName) {
                    if ($dotPosition = strpos($fieldName, '.')) {
                        $fieldName = substr($fieldName, $dotPosition + 1, strlen($fieldName));
                    }
                    $pk[$fieldName] = $insertedPK[$i];
                }

                return $pk;
            }
        }

        return null;
    }

    /**
     * Delete data from api server
     *
     * @param RecordManager $recordManager
     * @param array $functionInfo
     * @param array $approvedMessagesCode
     *
     * @return mixed
     */
    public static function delete(RecordManager $recordManager, array $functionInfo, array $approvedMessagesCode)
    {
        if (empty($functionInfo['get']) || ($recordManager->isUseAliasFramework() && empty($functionInfo['delete']))) {
            return false;
        }

        $model = new self();
        $tpl = Yii::$app->session['tabData']->getSelectTpl();
        $request = [
            "lib_name" => $recordManager->getLibrary()
        ];

        $request['func_param']['screen_tabs'] = array_keys(Yii::$app->session['tabData']->tplData);

        if ($recordManager->isUseAliasFramework()) {
            $request['func_name'] = $functionInfo['delete'];
            $request['func_param']['PK'] = $recordManager->getAliasFrameworkPK();
            $request['alias_framework_info'] = $recordManager->getAliasFrameworkInfo();
        } else {
            $request['func_name'] = CustomLibs::getFunctionName($recordManager->getLibrary(), $functionInfo['get'], self::DELETE_FUNC_TYPE);
            $request['func_param']['PK'] = $recordManager->getPK();
        }

        if (!empty($tpl['tpl']->screen_extensions['delete']['pre'])) {
            $request['func_param']['func_extensions_pre'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['delete']['pre']);
        }

        if (!empty($approvedMessagesCode)) {
            $request['func_param']['confirmed_messages'] = $approvedMessagesCode;
        }

        if (!empty($tpl['tpl']->screen_extensions['delete']['post'])) {
            $request['func_param']['func_extensions_post'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['delete']['post']);
        }

        $result = $model->processData($request);

        if (($result['requestresult'] ?? '') != 'successfully') {
            return $result ?? null;
        }

        return true;
    }

    /**
     * Execute data on API server
     *
     * @param RecordData $data
     * @param RecordSubData $updateSubData
     * @param RecordSubData $deleteSubData
     * @param RecordSubData $insertSubData
     *
     * @return mixed
     */
    public static function execute(RecordData $data, RecordSubData $updateSubData, RecordSubData $insertSubData, RecordSubData $deleteSubData, array $approvedMessagesCode)
    {
        $model = new static();
        $tpl = Yii::$app->session['tabData']->getSelectTpl();

        //echo '<pre>'; print_r($tpl);

        $mainData = $data->getData();

        if ($data->recordManager->isUseAliasFramework()) {
            $updateSubData->setMainData($mainData);
            $insertSubData->setMainData($mainData);
            $deleteSubData->setMainData($mainData);

            $mainData = array_merge_recursive(
                $mainData,
                $updateSubData->getExecuteDataAF(RecordManager::ITEM_ATTR_UPDATE_FUNC),
                $insertSubData->getExecuteDataAF(RecordManager::ITEM_ATTR_UPDATE_FUNC),
                $deleteSubData->getExecuteDataAF(RecordManager::ITEM_ATTR_UPDATE_FUNC)
            );
        }

        foreach ($mainData as $function => $items) {
            $request = [
                "lib_name" => $data->recordManager->getLibrary(),
                "func_param" => [
                    "patch_json" => $items
                ]
            ];

            if ($data->recordManager->isUseAliasFramework()) {
                $request['alias_framework_info'] = $data->recordManager->getAliasFrameworkInfo();
                $request['func_name'] = 'ExecuteAliasFramework';
                $request['func_param']['PK'] = $data->recordManager->getAliasFrameworkPK();
            } else {
                $request['func_name'] = CustomLibs::getFunctionName($data->recordManager->getLibrary(), $function, self::UPDATE_FUNC_TYPE);
                $request['func_param']['PK'] = $data->recordManager->getPK();
            }

            if (!empty($tpl['tpl']->screen_extensions['execute']['pre'])) {
                $request['func_param']['func_extensions_pre'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['execute']['pre']);
            }

            if (!empty($approvedMessagesCode)) {
                $request['func_param']['confirmed_messages'] = $approvedMessagesCode;
            }

            if (!empty($tpl['tpl']->screen_extensions['execute']['post'])) {
                $request['func_param']['func_extensions_post'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['execute']['post']);
            }

            if(!empty($tpl['tpl']->screen_extensions['executeFunction']['execute'])) {
                $request['func_param']['func_extensions_execute'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['executeFunction']['execute']);
            }

            $result = $model->processData($request);

            /*if ($data->recordManager->isUseAliasFramework()) {
                RecordAccessAliasFramework::unlock($data->recordManager);
            } else {
                self::updateSub($updateSubData);
                self::insertSub($insertSubData);
                self::deleteSub($deleteSubData);
                RecordAccess::unlock($data->recordManager, $function);
            }*/

            if ($result['requestresult'] != 'successfully') {
                return null;
            }
        }

        return true;
    }

    /**
     * Update children elements of data on API server
     *
     * @param RecordSubData $data
     *
     * @return mixed
     */
    public static function updateSub(RecordSubData $data)
    {
        $subData = $data->getData();
        if ($data->recordManager->isUseAliasFramework() || empty($subData) || empty($data->recordManager->getPK())) {
            return false;
        }

        $model = new self();
        $tpl = Yii::$app->session['tabData']->getSelectTpl();

        foreach ($subData as $getFunction => $row) {
            foreach ($row as $subPK => $items) {
                $updateFunction = CustomLibs::getFunctionName($data->recordManager->getLibrary(), $getFunction, self::UPDATE_FUNC_TYPE);
                if ($relatedField = CustomLibs::getRelated($data->recordManager->getLibrary(), $getFunction)) {
                    $items[$relatedField] = $data->recordManager->getPK();
                }

                $request = [
                    "lib_name" => $data->recordManager->getLibrary(),
                    'func_name' => $updateFunction,
                    "func_param" => [
                        "PK" => (string)$subPK,
                        "patch_json" => $items
                    ]
                ];

                $request['func_param']['screen_tabs'] = array_keys(Yii::$app->session['tabData']->tplData);

                if (!empty($tpl['tpl']->screen_extensions['add']['pre'])) {
                    $request['func_param']['func_extensions_pre'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['pre']);
                }

                if (!empty($tpl['tpl']->screen_extensions['add']['post'])) {
                    $request['func_param']['func_extensions_post'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['post']);
                }

                $result = $model->processData($request);
                if ($result['requestresult'] != 'successfully') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Insert children elements of data on API server
     *
     * @param RecordSubData $data
     *
     * @return boolean
     */
    public static function insertSub(RecordSubData $data)
    {
        $subData = $data->getData();
        if ($data->recordManager->isUseAliasFramework() || empty($subData) || empty($data->recordManager->getPK())) {
            return false;
        }

        $model = new self();
        $tpl = Yii::$app->session['tabData']->getSelectTpl();

        foreach ($subData as $getFunction => $row) {
            foreach ($row as $items) {
                $createFunction = CustomLibs::getFunctionName($data->recordManager->getLibrary(), $getFunction, self::INSERT_FUNC_TYPE);
                if ($relatedField = CustomLibs::getRelated($data->recordManager->getLibrary(), $getFunction)) {
                    $items[$relatedField] = $data->recordManager->getPK();
                }

                $request = [
                    "lib_name" => $data->recordManager->getLibrary(),
                    'func_name' => $createFunction,
                    "func_param" => [
                        "patch_json" => $items
                    ]
                ];

                $request['func_param']['screen_tabs'] = array_keys(Yii::$app->session['tabData']->tplData);

                if (!empty($tpl['tpl']->screen_extensions['add']['pre'])) {
                    $request['func_param']['func_extensions_pre'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['pre']);
                }

                if (!empty($tpl['tpl']->screen_extensions['add']['post'])) {
                    $request['func_param']['func_extensions_post'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['post']);
                }

                $result = $model->processData($request);
                if ($result['requestresult'] != 'successfully') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Delete children elements of data on API server
     *
     * @param RecordSubData $data
     *
     * @return mixed
     */
    public static function deleteSub(RecordSubData $data)
    {
        $subData = $data->getDeleteData();
        if ($data->recordManager->isUseAliasFramework() || empty($subData)) {
            return false;
        }

        $model = new self();
        $tpl = Yii::$app->session['tabData']->getSelectTpl();

        foreach ($subData as $getFunction => $items) {
            foreach ($items as $pk) {
                $deleteFunction = CustomLibs::getFunctionName($data->recordManager->getLibrary(), $getFunction, self::DELETE_FUNC_TYPE);
                $request = [
                    "lib_name" => $data->recordManager->getLibrary(),
                    'func_name' => $deleteFunction,
                    "func_param" => [
                        "PK" => (string)$pk
                    ]
                ];

                $request['func_param']['screen_tabs'] = array_keys(Yii::$app->session['tabData']->tplData);

                if (!empty($tpl['tpl']->screen_extensions['add']['pre'])) {
                    $request['func_param']['func_extensions_pre'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['pre']);
                }

                if (!empty($tpl['tpl']->screen_extensions['add']['post'])) {
                    $request['func_param']['func_extensions_post'] = self::fillFuncExtensions($tpl['tpl']->screen_extensions['add']['post']);
                }

                $result = $model->processData($request);
                if ($result['requestresult'] != 'successfully') {
                    return false;
                }
            }
        }

        return true;
    }

    public static function fixedApiResult($str, $isAliasFramework = false)
    {
        $position = strrpos($str, '.');
        if (substr_count($str, '.') > 1 && $isAliasFramework) {
            $position = false;
        }

        if ($position !== false) {
            return substr($str, $position + 1, strlen($str));
        }

        return str_replace(['search_mask_list:', 'field_name_list:', 'record_list:'], '', $str);
    }

    public static function getFieldListForQuery($id, $relatedField = null)
    {
        $fieldList = [];
        $pk = (is_array($id)) ? $id : Json::decode($id, true);

        if (!empty($id)) {
            if ($relatedField) {
                $pk = implode(RecordManager::PK_DELIMITER, $pk);
                $fieldList[$relatedField] = [$pk];
            } elseif (is_array($pk)) {
                foreach ($pk as $key => $item) {
                    $fieldList[$key] = [$item];
                }
            }
        }

        return $fieldList;
    }

    public static function getFieldOutListForQuery($libName, $functionName, array $subDataPK = [])
    {
        //echo 'in getFieldOutListForQuery';

        $additionalParam = [];

        /**
         * @var $tabData Screen;
         */

        if (isset(Yii::$app->session['tabData']) && $tabData = Yii::$app->session['tabData']) {
            $tabId = $tabData->getTabId();
            $additionalParam['field_out_list'] = [];

            if (isset(Yii::$app->session['tabData']->fieldsData[$tabId][$functionName])) {
                $additionalParam['field_out_list'] = Yii::$app->session['tabData']->fieldsData[$tabId][$functionName];
            }

            foreach ($subDataPK as $item) {
                if (!in_array($item, $additionalParam['field_out_list'])) {
                    $additionalParam['field_out_list'][] = $item;
                }
            }
        }

        //Fix for button type column
        if (!empty($additionalParam['field_out_list'])) {
            foreach ($additionalParam['field_out_list'] as $key => $field) {
                if(is_array($field) && isset($field[0]) && (preg_match('/__[\w]+/', $field[0])))
                    unset($additionalParam['field_out_list'][$key]);
                else if (!is_array($field) && preg_match('/__[\w]+/', $field)) {
                    unset($additionalParam['field_out_list'][$key]);
                }
            }

            $additionalParam['field_out_list'] = array_values($additionalParam['field_out_list']);
        }

        return $additionalParam;
    }

    public static function getGridFieldOutListForQuery(\stdClass $configuration, array $subDataPK = [])
    {
        //echo 'in getGridFieldOutListForQuery';

        //echo '<pre> configuration :: '; print_r($configuration);

        $additionalParam = [];

        if (!empty($configuration->layout_configuration->params)) {
            $additionalParam['field_out_list'] = $configuration->layout_configuration->params;
        }

        if (!empty($configuration->sub_tables_template)) {
            foreach ($configuration->sub_tables_template as $subTableTemplate) {
                array_push($additionalParam['field_out_list'], ...$subTableTemplate['layout_configuration']['params']);
            }
        }

        foreach ($subDataPK as $item) {
            if (!in_array($item, $additionalParam['field_out_list'])) {
                $additionalParam['field_out_list'][] = $item;
            }
        }

        $additionalParam['sort_field'] = [];

        if (!empty($configuration->layout_table->column_configuration)) {
            foreach ($configuration->layout_table->column_configuration as $name => $columnConfig) {
                $columnConfig = ArrayHelper::map($columnConfig, 'name', 'value');
                if (!empty($columnConfig['sort_type'])) {
                    $additionalParam['sort_field'][] = $name;
                    $additionalParam['sort_type'] = $columnConfig['sort_type'];
                }
            }
        }

        //Fix for button type column
        if (!empty($additionalParam['field_out_list'])) {
            foreach ($additionalParam['field_out_list'] as $key => $field) {
                if(is_array($field) && isset($field[0]) && (preg_match('/__[\w]+/', $field[0])))
                    unset($additionalParam['field_out_list'][$key]);
                else if (!is_array($field) && preg_match('/__[\w]+/', $field)) {
                    unset($additionalParam['field_out_list'][$key]);
                }
            }

            $additionalParam['field_out_list'] = array_values($additionalParam['field_out_list']);
        }

        //echo '<pre> additionalParam :: '; print_r($additionalParam);

        return $additionalParam;
    }

    public static function getPKAliasFramework() {
        if (empty(Yii::$app->session[self::AF_PK_INFO_CACHE_NAME])) {
            $postData = [
                "func_name" => "GetPKSInfo",
                "lib_name" => "CodiacSDK.CommonArea",
                "func_param" => ['empty' => 'true'],
            ];

            $result = (new static())->processData($postData);
            Yii::$app->session[self::AF_PK_INFO_CACHE_NAME] = isset($result['record_list']) ? $result['record_list'] : null;
        }

        return Yii::$app->session[self::AF_PK_INFO_CACHE_NAME];
    }

    public static function getWorkflowData($postData, $additionalParam) {
        $model = new static();
        $attributes = $model->processData($postData);

        if (!empty($attributes['record_list'])) {
            $model->list = $attributes['record_list'];

            if (!empty($attributes['record_list_pk'])) {
                $model->pkList = $attributes['record_list_pk'];
            }

            return $model;
        } else {
            return null;
        }
    }

    public static function searchLinkedListCustomQuery($custom_query, $custom_query_param, array $queries)
    {
        $library = 'CodiacSDK.CommonArea';
        $getQueriesAction = 'GetCustomQueryList';
        $executeAction = 'ExecuteCustomQuery';

        $model = self::getModel($custom_query, [
            'lib_name' => $library,
            'func_name' => $getQueriesAction
        ]);

        if (!empty($model['query_params'])) {
            $queryParams = explode(';', $model['query_params']);
            foreach ($queryParams as $param) {
                $param = trim($param);
                if (empty($queries[$param])) {
                    $queries[$param] = '';
                }
            }
        }

        $sqlParams = [];

        foreach ($queries as $key => $item) {
            if (strpos($key, '.')) {
                $key = substr($key, strrpos($key, '.') + 1, strlen($key));
            }

            if (!isset($sqlParams[$key]) || !empty($item)) {
                $sqlParams[$key] = $item;
            }
        }

        if (empty($sqlParams)) {
            $sqlParams = null;
        } else {
            $array_key = array_keys($sqlParams);

            $sqlParams[$array_key[0]] = $custom_query_param;
        }

        //echo '<pre>'; print_r($sqlParams);

        $userSettings = UserAccount::getSettings();

        $response = (new static())->processData([
            "lib_name" => $library,
            'func_name' => $executeAction,
            "func_param" => [
                "PK" => $custom_query,
                "sql_params" => $sqlParams,
                "custom_sql_security_level" => $userSettings->custom_sql_security_level
            ]
        ]);

        //echo '<pre> sqlParams :: '; print_r($sqlParams);

        $result = array();

        if (!empty($response['record_list'])) {
            foreach ($response['record_list'] as $key => $item) {
                foreach ($sqlParams as $param => $value) {
                    $pkName = $param;

                    if ($dotPosition = strrpos($param, '.')) {
                        $pkName = substr($param, $dotPosition + 1, strlen($param));
                    }

                    $pkName = trim($pkName, ':');
                    $result[$key] = isset($item[$pkName]) ? $item[$pkName] : null;
                }
            }

            return $result;
        } else {
            return [];
        }
    }

    public static function searchScreenData(array $alias_input, array $alias_output)
    {
        $response = (new static())->processData([
            "lib_name" => "CodiacSDK.Universal",
            "func_name" => "AliasFrameworkSearchEntryPoint",
            "func_param" => [
                "alias_input" => $alias_input,
                "alias_output" => $alias_output
            ]
        ]);

       if (!empty($response['record_list'])) {
            return $response['record_list'];
        }

        return null;
    }

    /**
    * @param array $extensions
    * @return array
    */
    private static function fillFuncExtensions(array $extensions)
    {
        $funcExtensions = [];

        foreach($extensions as $key => $value) {
            $funcExtensions[$key]['id'] = $value['extension_id'];
            $funcExtensions[$key]['signature'] = $value['extension_lib'].';'.$value['extension'].';';
        }

        return $funcExtensions; 
    }
}