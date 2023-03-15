<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use app\components\_TemplateHelper;
use app\components\RenderTabHelper;
use Yii;

class Screen extends BaseModel
{
    public static $dataLib = 'CodiacSDK.AdminScreens';
    public static $dataAction = 'GetScreenList';

    public $tplData = [];
    public $fieldsData = [];
    public $fieldsAccess = [];

    const DEVICE_DESKTOP = 'D';
    const DEVICE_MOBILE = 'M';
    const DEVICE_WEB = 'W';

    public static $devices = [
        self::DEVICE_DESKTOP => 'Desktop',
        self::DEVICE_MOBILE => 'Mobile',
        self::DEVICE_WEB => 'Web',
    ];

    public static $typeLabels = [
        1 => 'Header 2x2',
        2 => 'Header 2x3',
        3 => '2x2',
        4 => '2x3',
        5 => '1x1',
        6 => 'Header 1x1',
        7 => 'Header 1x2',
        8 => '1x2',
        9 => 'Header 2x1',
        10 => '2x1',
    ];

    public static $types = [
        1 => [
            'header' => true,
            'row_count' => 2,
            'col_count' => 2
        ],
        2 => [
            'header' => true,
            'row_count' => 3,
            'col_count' => 2
        ],
        3 => [
            'header' => false,
            'row_count' => 2,
            'col_count' => 2
        ],
        4 => [
            'header' => false,
            'row_count' => 3,
            'col_count' => 2
        ],
        5 => [
            'header' => false,
            'row_count' => 1,
            'col_count' => 1
        ],
        6 => [
            'header' => true,
            'row_count' => 1,
            'col_count' => 1
        ],
        7 => [
            'header' => true,
            'row_count' => 1,
            'col_count' => 2
        ],
        8 => [
            'header' => false,
            'row_count' => 1,
            'col_count' => 2
        ],
        9 => [
            'header' => true,
            'row_count' => 2,
            'col_count' => 1
        ],
        10 => [
            'header' => false,
            'row_count' => 2,
            'col_count' => 1
        ],
    ];

    /**
     * Getting data from API server
     * @param array $fieldList
     * @param array $postData
     * @param array $additionallyParam
     * @return static
     */
    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        /* @var $model static */
        $model = parent::getData($fieldList, $postData, $additionallyParam);
        if (!empty($model->list)) {
            usort($model->list, function ($a, $b) {
                return ($cmp = strnatcmp($a["screen_tab_weight"], $b["screen_tab_weight"])) ? $cmp : strnatcmp($a["screen_tab_text"], $b["screen_tab_text"]);
            });
        }

        $fieldsData = [];

		//'tpl' => empty($listItem['screen_tab_template']) ? null : json_decode($listItem['screen_tab_template'], true)
		//'tpl' => empty($listItem['screen_tab_template']) ? null : Screen::decodeTemplate($listItem['screen_tab_template'], true)

		if (isset($model->list)) {
            foreach ($model->list as $listItem) {
                if (isset($listItem['screen_tab_template'])) { //echo '<pre>'; print_r(Screen::decodeTemplate($listItem['screen_tab_template'], true)); die;
                    $model->tplData[$listItem['id']] = [
                        'lib' => $listItem['screen_lib'],
                        'tpl' => empty($listItem['screen_tab_template']) ? null : Screen::decodeTemplate($listItem['screen_tab_template'], true)
                    ];
                    if (!isset($fieldsData[$listItem['id']])) {
                        $fieldsData[$listItem['id']] = [];
                    }

                    self::getFieldsArrayFromTpl($model->tplData[$listItem['id']]['tpl'], $fieldsData[$listItem['id']]);
                }
            }
        }

        if (!empty($model) && property_exists($model, 'fieldsData')) {
            $model->fieldsData = $fieldsData;
            $model->fieldsAccess = self::getFieldsAccess($fieldsData, !empty($listItem['screen_lib']) ? $listItem['screen_lib'] : null);
        }

        return $model;
    }

    /**
     * Getting fields for render from template
     * @param _TemplateHelper $tpl
     * @param array $fieldsList
     */
    private static function getFieldsArrayFromTpl($tpl, &$fieldsList)
    {
        if (!empty($tpl) && property_exists($tpl, 'template_layout')) {
            foreach ($tpl->template_layout as $layout) {
                $funcName = $layout->data_source_get;
                if (!isset($fieldsList[$funcName])) $fieldsList[$funcName] = [];
                if ($layout->layout_type == RenderTabHelper::SECTION_TYPE_LIST && !empty($layout->layout_fields)) {
                    foreach ($layout->layout_fields as $fld) {
                        $fieldName = null;
                        foreach ($fld as $fldAttr) {
                            if ($fldAttr['name'] == 'data_field') {
                                $fieldName = CommandData::fixedApiResult($fldAttr['value'], $tpl->alias_framework->enable);
                                if ($fieldName != '' && !in_array($fieldName, $fieldsList[$funcName])) {
                                    $fieldsList[$funcName][] = $fieldName;
                                }
                            }
                        }
                    }
                } elseif (in_array($layout->layout_type, [RenderTabHelper::SECTION_TYPE_GRID, RenderTabHelper::SECTION_TYPE_CHART_PIE, RenderTabHelper::SECTION_TYPE_CHART_DOUGHNUT])) {
                    foreach ($layout->layout_configuration->params as $fld) {
                        $fieldName = CommandData::fixedApiResult($fld, $tpl->alias_framework->enable);
                        if ($fieldName != '' && !in_array($fieldName, $fieldsList[$funcName])) {
                            $fieldsList[$funcName][] = $fieldName;
                        }
                    }
                } elseif (in_array($layout->layout_type, [RenderTabHelper::SECTION_TYPE_CHART_LINE, RenderTabHelper::SECTION_TYPE_CHART_BAR_HORIZONTAL, RenderTabHelper::SECTION_TYPE_CHART_BAR_VERTICAL])) {
                    foreach ($layout->layout_configuration->params as $fldGroup) {
                        foreach ($fldGroup as $fld) {
                            $fieldName = CommandData::fixedApiResult($fld, $tpl->alias_framework->enable);
                            if ($fieldName != '' && !in_array($fieldName, $fieldsList[$funcName])) {
                                $fieldsList[$funcName][] = $fieldName;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Getting rights for fields
     * @param array $fieldsList
     * @param string $lib
     * @return array
     */
    private static function getFieldsAccess($fieldsList, $lib) {
        $access = [];
        if (!empty($fieldsList)) {
            foreach($fieldsList as $functions) {
                $access[$lib] = [];
                foreach($functions as $funcName => $fields) {
                    $access[$lib][$funcName] = AliasList::getAlias($lib,$funcName, $fields);
                }
            }
        }
        return $access;
    }

    /**
     * Decode template of tab
     * @param string $template
     * @param bool $getObject
     * @return _TemplateHelper|array
     */
    public static function decodeTemplate($template, $getObject = false)
    {
        if (!isset($template)) {
            return null;
        }

        $templateJson = base64_decode($template);
        $templateArray = json_decode($templateJson, true);

        return ($getObject) ? _TemplateHelper::run($templateArray) : $templateArray;
    }

    /**
     * Getting settings for search
     * @return array
     */
    public function getSearchConfig()
    {
        $list = [];
        foreach ($this->tplData as $id => $item) {
            if (empty($item['lib']) || empty($item['tpl'])) {
                continue;
            }

            $library = $item['lib'];
            $tpl = $item['tpl'];

            if (empty($list[$library])) {
                if (empty($tpl->search_custom_query) && empty($tpl->search_configuration) ) {
                    continue;
                }

                if (!empty($tpl->search_custom_query)) {
                    $list[$library]['custom'] = $tpl->search_custom_query;
                }

                if (!empty($tpl->search_configuration)) {
                    $list[$library]['default'] = $tpl->search_configuration;
                }

                $list[$library]['id'] = $id;
            }
        }
        \Yii::$app->session[CommandData::SEARCH_CONFIG_CACHE_NAME] = $list;
        return $list;
    }

    /**
     * Getting screen settings by screen name
     * @param string $screenName
     * @return null
     */
    public function getSelectScreen($screenName)
    {
        if (!empty($this->list)) {
            foreach ($this->list as $list) {
                if ($list['id'] == $screenName) return $list;
            }
        }

        return null;
    }

    public function getSelectTpl() {
        $tid = Yii::$app->request->post('activeTab', false);
        if (!empty($this->tplData[$tid])) {
            return $this->tplData[$tid];
        }

        return null;
    }

    public function getTabId() {
        return Yii::$app->request->post('activeTab', null);
    }

    public function getTplList()
    {
        $tabListId = Yii::$app->request->post('tabList', false);
        $result = [];

        if (is_array($tabListId)) {
            foreach($tabListId as $id) {
                if (!empty($this->tplData[$id])) {
                    $result[$id] = $this->tplData[$id];
                }
            }
        }

        return $result;
    }

    public function getListInfo($list_name, $entry_name) {
        $processData = [
            "func_name" => "GetListList",
            "func_param" => [
                "field_name_list" => ["list_name", "entry_name"],
                "field_value_list" => ["list_name" => ["TransRequest"], "entry_name" => ["F"]]
            ],
            "lib_name" => "CodiacSDK.CommonArea"
        ];

        //echo json_encode($processData);

        $result = (new static())->processData($processData);
        return (!empty($result['record_list'])) ? (object)$result['record_list'] : [];
    }

    public static function execute(array $tabData, $dataId, $field_val, $field_list_json)
    {
        if (empty($tabData['screen_tab_template'])) {
            return null;
        }

        $success = false;
        $addedExtension = [];

        if (($screenTemplate = self::decodeTemplate($tabData['screen_tab_template'], true)) && !empty($screenTemplate->screen_extensions)) {
            $replace_execute_function = '';

            if(!empty($screenTemplate->template_layout)) {
                foreach ($screenTemplate->template_layout as $key => $val) {
                    if($val->layout_type == 'LIST') {
                        if(!empty($val->layout_fields)) {
                            foreach ($val->layout_fields as $key1 => $val1) {
                                if(!empty($val1))
                                    foreach($val1 as $key2 => $val2)
                                        if(!empty($val2))
                                            foreach($val2 as $key3 => $val3)
                                                if(!is_array($val3))
                                                    if($val2['name'] == 'field_type' && $val2['value'] == 'List With Extension Function' && $field_val != '') {
                                                        $field_list = json_decode($field_list_json);

                                                        if(!empty($field_list) && array_key_exists($field_val, $field_list)) {
                                                            $list_info = Screen::getListInfo($field_list->$field_val, $field_list);

                                                            if(!empty($list_info))
                                                                foreach($list_info as $list)
                                                                    $replace_execute_function = $list['note'];
                                                        }
                                                    }
                            }
                        }
                    }
                }
            }

			if(!empty($screenTemplate->screen_extensions['execute']['pre'])) {
				$pre_arr = array();

                foreach($screenTemplate->screen_extensions['execute']['pre'] as $pre_key => $pre) {
                    $pre_arr[$pre_key]['id'] = $pre['extension_id'];
                    $pre_arr[$pre_key]['signature'] = $pre['extension_lib'].';'.$pre['extension'].';';

					$funcName = $pre['extension'];
				}

				$libName = $tabData['screen_lib'];

				$subDataPK = [];

				$postData = [
					'lib_name' => $libName,
					'func_name' => 'ExecuteAliasFramework'
				];
				if ($relatedField = CustomLibs::getRelated($libName, $funcName)) {
					$subDataPK = CustomLibs::getPK($libName, $functionName);
				}
				$fieldParams = CommandData::getFieldListForQuery($dataId, $relatedField);
				$additionalParam = CommandData::getFieldOutListForQuery($libName, $funcName, $subDataPK);

				$additionalParam['func_extensions_pre'] = $pre_arr;

				$val = CommandData::getData($fieldParams, $postData, $additionalParam);

				$success = true;
			}

			if(!empty($screenTemplate->screen_extensions['execute']['post'])) {
				$post_arr = array();

                foreach($screenTemplate->screen_extensions['execute']['post'] as $post_key => $post) {
                    $post_arr[$post_key]['id'] = $post['extension_id'];
                    $post_arr[$post_key]['signature'] = $post['extension_lib'].';'.$post['extension'].';';

					$funcName = $post['extension'];
				}

				$libName = $tabData['screen_lib'];

				$subDataPK = [];

				$postData = [
					'lib_name' => $libName,
					'func_name' => 'ExecuteAliasFramework'
				];
				if ($relatedField = CustomLibs::getRelated($libName, $funcName)) {
					$subDataPK = CustomLibs::getPK($libName, $functionName);
				}
				$fieldParams = CommandData::getFieldListForQuery($dataId, $relatedField);
				$additionalParam = CommandData::getFieldOutListForQuery($libName, $funcName, $subDataPK);

				$additionalParam['func_extensions_post'] = $post_arr;

				$val = CommandData::getData($fieldParams, $postData, $additionalParam);

				$success = true;
			}

			if(!empty($screenTemplate->screen_extensions['executeFunction']['execute'])) {
				$execute_arr = array();

                foreach($screenTemplate->screen_extensions['executeFunction']['execute'] as $execute_key => $execute) {
                    $execute_arr[$execute_key]['id'] = $execute['extension_id'];
                    $execute_arr[$execute_key]['signature'] = $execute['extension_lib'].';'.$execute['extension'].';';

					if($replace_execute_function != '' && $execute_key == 0)
						$funcName = $replace_execute_function;
					else
						$funcName = $execute['extension'];
				}

				$libName = $tabData['screen_lib'];

				$subDataPK = [];

				$postData = [
					'lib_name' => $libName,
					'func_name' => 'ExecuteAliasFramework'
				];
				if ($relatedField = CustomLibs::getRelated($libName, $funcName)) {
					$subDataPK = CustomLibs::getPK($libName, $functionName);
				}
				$fieldParams = CommandData::getFieldListForQuery($dataId, $relatedField);
				$additionalParam = CommandData::getFieldOutListForQuery($libName, $funcName, $subDataPK);

				$additionalParam['func_extensions_execute'] = $execute_arr;

				$val = CommandData::getData($fieldParams, $postData, $additionalParam);

				$success = true;
			}

            /*foreach ($screenTemplate->screen_extensions['execute'] as $functionName => $extension) {
                //if (!empty($extension) && is_array($extension)) {
                    $libName = $tabData['screen_lib'];
                    $subDataPK = [];

                    if($replace_execute_function == '')
                        $funcName = $screenTemplate->screen_extensions['executeFunction']['function'];
                    else
                        $funcName = $replace_execute_function;

                    $postData = [
                        'lib_name' => $libName,
                        'func_name' => $funcName
                    ];
                    if ($relatedField = CustomLibs::getRelated($libName, $funcName)) {
                        $subDataPK = CustomLibs::getPK($libName, $functionName);
                    }
                    $fieldParams = CommandData::getFieldListForQuery($dataId, $relatedField);
                    $additionalParam = CommandData::getFieldOutListForQuery($libName, $funcName, $subDataPK);

                    $val = CommandData::getData2($fieldParams, $postData, $additionalParam);

                    die;

                    $additionalParam = [];

                    $custom = explode(';', $screenTemplate->screen_extensions['executeFunction']['custom']);

                    if (!empty($custom[0]) && !empty($custom[1])) {
                        foreach ($screenTemplate->screen_extensions['execute'][$functionName] as $func => $ext) {

                            $postData = [
                                'lib_name' => $custom[0],
                                'func_name' => $custom[1]
                            ];

                            $additionalParam['PK'] = $dataId;
                            $additionalParam['patch_json'] = $val->list[0];
                            if ($functionName == 'pre') {
                                $additionalParam['func_extensions_pre'] = [$ext];
                                if (!empty($screenTemplate->screen_extensions['execute']['post'][$func])){
                                    $postResult = $screenTemplate->screen_extensions['execute']['post'][$func];
                                    $additionalParam['func_extensions_post'] = [$postResult];
                                    array_push($addedExtension, $func);
                                }
                            } else if ($functionName == 'post') {
                                if (!in_array($func, $addedExtension)) {
                                    $additionalParam['func_extensions_post'] = [$ext];
                                    if (!empty($screenTemplate->screen_extensions['execute']['pre'][$func])){
                                        $preResult = $screenTemplate->screen_extensions['execute']['pre'][$func];
                                        $additionalParam['func_extensions_pre'] = [$preResult];
                                    }
                                }
                            }
                            CommandData::getData([], $postData, $additionalParam);
                        }
                    } else {
                        return null;
                    }
                //}
                $success = true;
           }*/
        }

        if ($success) {
            return $success;
        } else {
            return null;
        }
    }

    public static function executeCustom($dataId, array $tabData, $getFunction, array $customData, array $pre = [], array $post = [])
    {
        if (empty($tabData['screen_tab_template']) || empty($getFunction) || empty($customData) || count($customData) < 2) {
            return null;
        }

        $libName = $tabData['screen_lib'];
        $subDataPK = [];

        $postData = [
            'lib_name' => $tabData['screen_lib'],
            'func_name' => $getFunction
        ];
        if ($relatedField = CustomLibs::getRelated($libName, $getFunction)) {
            $subDataPK = CustomLibs::getPK($libName, $getFunction);
        }
        $fieldParams = CommandData::getFieldListForQuery($dataId, $relatedField);
        $additionalParam = CommandData::getFieldOutListForQuery($libName, $getFunction, $subDataPK);
        $val = CommandData::getData($fieldParams, $postData, $additionalParam);

        $additionalParam = [];
        if (!empty($customData[0]) && !empty($customData[1])) {
            $postData = [
                'lib_name' => $customData[0],
                'func_name' => $customData[1]
            ];

            $additionalParam['PK'] = implode(';', $dataId);
            $additionalParam['patch_json'] = $val->list[0];
            if (!empty($pre[$customData[1]])) {
                $additionalParam['func_extensions_pre'] = $pre[$customData[1]];
            }
            if (!empty($post[$customData[1]])) {
                $additionalParam['func_extensions_post'] = $post[$customData[1]];
            }

            return CommandData::getData([], $postData, $additionalParam);
        }

        return null;
    }

    public function getList()
    {
        $list = parent::getList();
        return array_filter($list, function ($item) {
            return in_array('W', explode(';', $item['screen_tab_devices']));
        });
    }
}