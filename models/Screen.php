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
                            if (isset($fldAttr['name']) && $fldAttr['name'] == 'data_field') {
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
                } elseif (in_array($layout->layout_type, [RenderTabHelper::SECTION_TYPE_CHART_LINE, RenderTabHelper::SECTION_TYPE_CHART_SCATTER_WITH_LINEAR_REGRESSION, RenderTabHelper::SECTION_TYPE_CHART_TIME_SERIES, RenderTabHelper::SECTION_TYPE_CHART_BAR_HORIZONTAL, RenderTabHelper::SECTION_TYPE_CHART_BAR_VERTICAL])) {
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

    public function getListInfo($list_name, $entry_name='TransRequest') {
        $processData = [
            "func_name" => "GetListList",
            "func_param" => [
				"field_name_list" => ["list_name"],
                "field_value_list" => ["list_name" => ["$entry_name"]]
                //"field_name_list" => ["list_name", "entry_name"],
                //"field_value_list" => ["list_name" => ["$entry_name"], "entry_name" => ["F"]]
            ],
            "lib_name" => "CodiacSDK.CommonArea"
        ];

        //echo json_encode($processData);

        $result = (new static())->processData($processData);
        return (!empty($result['record_list'])) ? (object)$result['record_list'] : [];
    }

	public function getExtensionInfo($extension_id) {
		$processData = [
            "func_name" => "GetExtFuncRecordList",
            "func_param" => [
				"field_name_list" => ["id"],
                "field_value_list" => ["id" => [$extension_id]]
            ],
            "lib_name" => "CodiacSDK.CommonArea"
        ];

        //echo json_encode($processData);

        $result = (new static())->processData($processData);
        return (!empty($result['record_list'])) ? (object)$result['record_list'] : [];
    }

	public static function getMenuTooltip() {
		//echo 'in Screen getMenuTooltip';

		$user_settings = UserAccount::getSettings();

		//echo '<pre> $user_settings :: '; print_r($user_settings);

		$settings = 'Settings';
		$change_password = 'Change Password';
		$chat = 'Chat';
		$logout = 'Logout';
		$tasks = 'Tasks';
		$insert = 'Insert';
		$load = 'Load';
		$previous = 'Previous';
		$next = 'Next';
		$edit = 'Edit';
		$copy = 'Copy';
		$delete = 'Delete';
		$execute = 'Execute';
		$save = 'Save';
		$cancel = 'Cancel';

		$user_default_language = $user_settings->user_language;

		//echo 'user_default_language :: ' . $user_default_language;

		//echo '<pre> $user_settings :: '; print_r($user_settings);

		$processData = [
            "func_name" => "GetMenuIconsTooltips",
            "lib_name" => "CodiacSDK.CommonArea"
        ];

        //echo json_encode($processData);

        $result = (new static())->processData($processData);
        
		$menu_tooltip_list = (!empty($result['record_list'])) ? (object)$result['record_list'] : [];

		//echo '<pre> $menu_tooltip_list :: '; print_r($menu_tooltip_list);

		if(!empty($menu_tooltip_list)) {
			foreach($menu_tooltip_list as $menu_tooltip) {
				//echo '<pre> $menu_tooltip :: '; print_r($menu_tooltip);

				if($menu_tooltip['menu_icon'] == 'settings' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$settings = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'change_password' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$change_password = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'chat' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$chat = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'logout' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$logout = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'tasks' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$tasks = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'insert' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$insert = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'load' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$load = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'previous' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$previous = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'next' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$next = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'edit' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$edit = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'copy' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$copy = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'delete' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$delete = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'execute' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$execute = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'save' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$save = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];

				if($menu_tooltip['menu_icon'] == 'cancel' && isset($menu_tooltip['icon_tooltip']['internationalization']) && array_key_exists($user_default_language, $menu_tooltip['icon_tooltip']['internationalization']))
					$cancel = $menu_tooltip['icon_tooltip']['internationalization'][$user_default_language];
			}
		}

		$menuTooltip = array(
			'settings' => $settings,
			'change_password' => $change_password,
			'chat' => $chat,
			'logout' => $logout,
			'tasks' => $tasks,
			'insert' => $insert,
			'load' => $load,
			'previous' => $previous,
			'next' => $next,
			'edit' => $edit,
			'copy' => $copy,
			'delete' => $delete,
			'execute' => $execute,
			'save' => $save,
			'cancel' => $cancel
		);

		//echo '<pre> $menuTooltip :: '; print_r($menuTooltip); die;

		return $menuTooltip;
	}

	public static function getInternationalization() {
		$user_settings = UserAccount::getSettings();

		//echo '<pre> $user_settings :: '; print_r($user_settings);

		$user_default_language = $user_settings->user_language;
		//echo 'user_default_language :: ' . $user_default_language;

		$processData = [
            "func_name" => "GetInternationalization",
			"func_param" => [
				"field_name_list" => ["application"],
				"field_out_list" => ["code", "body"],
				"field_value_list" => ["application" => ["render"]]
			],
            "lib_name" => "CodiacSDK.CommonArea"
        ];

        //echo json_encode($processData);

        $result = (new static())->processData($processData);

		//echo '<pre> $result :: '; print_r($result);

		$internationalization_list = (!empty($result['record_list'])) ? $result['record_list'] : [];

		//echo '<pre> $internationalization_list :: '; print_r($internationalization_list);

		$internationalization_arr = array();

		if(!empty($internationalization_list)) {
			foreach($internationalization_list as $key => $internationalization_val) {
				//echo '<pre> $internationalization :: '; print_r($internationalization);

				if(isset($internationalization_val['body']['internationalization']) && array_key_exists($user_default_language, $internationalization_val['body']['internationalization']))
					$internationalization_arr[$internationalization_val['code']] = $internationalization_val['body']['internationalization'][$user_default_language];
			}
		}

		//echo '<pre> $internationalization :: '; print_r($internationalization); die;

		return $internationalization_arr;
	}

    public static function execute(array $tabData, $dataId, $field_val, $field_list_json, $lastFoundData, $approvedMessagesCode = [], $screenData = [])
    {
        if (empty($tabData['screen_tab_template'])) {
            return null;
        }

        $screenTemplate = self::decodeTemplate($tabData['screen_tab_template'], true);

        $lib_name = 'CodiacSDK.Universal';

        $field_out_list = array();

        $func_param = array();
        $patch_list_arr = array();
        $patch_table_arr = array();
        $pk_list_arr = array();
        $patch_json_arr = array();
        $func_param_json = array();

        $check = 1;

        foreach ($screenTemplate->template_layout as $item_key => $item) {
            $func_name = $item->data_source_get;
            $layout_type = $item->layout_type;

            if($layout_type == 'TABLE' || $layout_type == 'CHART')
                $isGetParent = true;
            else
                $isGetParent = false;

            $subDataPK = [];
            $relatedField = CustomLibs::getRelated($lib_name, $func_name);

            if ($isGetParent && !empty($dataId)) {
                if (CustomLibs::getTableName($lib_name, $func_name)) {
                    $subDataPK = CustomLibs::getPK($lib_name, $func_name);
                } else if (!empty($screenTemplate->search_configuration->pk_configuration)) {
                    $fieldParams = CommandData::getFieldListForQuery($dataId);
                }
            }

            if (empty($fieldParams)) {
                $fieldParams = CommandData::getFieldListForQuery($dataId, $relatedField);
            }

            $postData = [
                'lib_name' => $lib_name,
                'func_name' => $func_name,
                'alias_framework_info' => $screenTemplate->alias_framework,
                'search_function_info' => [
                    'config' => $screenTemplate->search_configuration,
                    'data' => $lastFoundData
                ]
            ];

            if ($isGetParent) {
                $additionalParam = CommandData::getGridFieldOutListForQuery($item, $subDataPK);
            } else {
                $additionalParam = CommandData::getFieldOutListForQuery($lib_name, $func_name);
            }

            if(!empty($field_out_list) && !empty($additionalParam['field_out_list']))
                if(array_diff($field_out_list, $additionalParam['field_out_list'])) {
                    $check = 1;
                } else {
                    $check = 0;
                }

            $field_out_list = $additionalParam['field_out_list'];

            //echo $check;

            if($check == 1) {
                $data = CommandData::getData($fieldParams, $postData, $additionalParam);

                if(!empty($data->list)) {
                    //echo '<pre>'; print_r($data->list);
                    //echo '<pre>'; print_r($data->pkList);

                    $temp_arr = array();
                    $temp_arr2 = array();

                    foreach($data->list as $key1 => $val1) {
                        foreach($val1 as $key2 => $val2) {
                            $temp_arr[$key2] = $val2;
                        }

                        if($isGetParent) {
                            $temp_arr['update'] = true;

                            array_push($patch_table_arr, $temp_arr);
                        } else {
                            array_push($patch_list_arr, $temp_arr);
                        }
                    }

                    //echo '<pre> patch_list_arr :: '; print_r($patch_list_arr);
                    //echo '<pre> patch_table_arr :: '; print_r($patch_table_arr);

                    foreach($data->pkList as $key1 => $val1) {
                        foreach($val1 as $key2 => $val2) {
                            $temp_arr2[$key2] = $val2;
                        }

                        $pk_list_arr[] = [$temp_arr2];
                        //echo '<pre> pk_list_arr :: '; print_r($pk_list_arr);
                    }
                }
            }
        }

        $patch_json_arr = ['PK' => $pk_list_arr, 'patch_json' => $patch_table_arr];

        foreach($patch_list_arr[0] as $patch_list_key => $patch_list)
            $patch_json_arr['patch_json'][$patch_list_key] = $patch_list;

        //echo 'json encode :: '.json_encode($patch_json_arr);

        //$success = false;
        //$addedExtension = [];

		$replace_execute_function_arr = array();

		$replace_execute_extension_id = '';
		$replace_execute_extension_lib = '';
		$replace_execute_extension = '';

		//echo '<pre>'; print_r($screenData); die;

		if(!empty($screenData)) {
			foreach($screenData as $screen) {
				if(!empty($screen['tpl']->template_layout)) {
					foreach ($screen['tpl']->template_layout as $key => $val) {
						if($val->layout_type == 'LIST') {
							if(!empty($val->layout_fields)) {
								foreach ($val->layout_fields as $key1 => $val1) {
									if(!empty($val1)) {
										$list_with_extension_field = false;

										foreach($val1 as $key2 => $val2) {
											if(!empty($val2)) {
												foreach($val2 as $key3 => $val3) {
													if(!is_array($val3)) {
														if($val2['name'] == 'field_type' && $val2['value'] == 'List With Extension Function') {
															$list_with_extension_field = true;
														}

														if($val2['name'] == 'list_name' && $val2['value'] != '' && $list_with_extension_field == true)
															$replace_execute_function_arr['list_name'] = $val2['value'];
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		//echo '<pre> $replace_execute_function_arr :: '; print_r($replace_execute_function_arr);
		//echo 'field_val :: ' . $field_val;

		if(!empty($replace_execute_function_arr)) {
			foreach($replace_execute_function_arr as $key_r => $val_r) {
				if($val_r != '') {
					$list_info = Screen::getListInfo('list_name', $val_r);

					//echo '<pre> $list_info :: '; print_r($list_info);

					if(!empty($list_info) && !empty($field_val)) {
						foreach($list_info as $info) {
							if($info['entry_name'] == $field_val) {
								$extension_info = Screen::getExtensionInfo($info['note']);

								//echo '<pre> $extension_info :: '; print_r($extension_info);

								if(!empty($extension_info)) {
									$replace_execute_extension_id = $extension_info->{0}['id'];
									$replace_execute_extension_lib = $extension_info->{0}['extension_lib'];
									$replace_execute_extension = $extension_info->{0}['extension_func'];
								}
							}
						}
					}
				}
			}
		}

		//$field_list = json_decode($field_list_json);

		//echo '<pre> $field_list :: '; print_r($field_list);

		/*if(!empty($field_list) && array_key_exists($field_val, $field_list)) {
			$list_info = Screen::getListInfo('list_name', $field_list);

			if(!empty($list_info))
				echo '<pre> $list_info :: '; print_r($list_info);
				//foreach($list_info as $list)
					//$replace_execute_function = $list['note'];
		}*/

        if (!empty($screenTemplate->screen_extensions)) {
            //echo '<pre> $screenTemplate->screen_extensions :: '; print_r($screenTemplate->screen_extensions); die;

            if(!empty($screenTemplate->screen_extensions['execute']['pre'])) {
                $pre_arr = array();

                foreach($screenTemplate->screen_extensions['execute']['pre'] as $pre_key => $pre) {
                    $pre_arr[$pre_key]['id'] = $pre['extension_id'];
                    $pre_arr[$pre_key]['signature'] = $pre['extension_lib'].';'.$pre['extension'].';';
                }

                $patch_json_arr['func_extensions_pre'] = $pre_arr;
            }

            if(!empty($screenTemplate->screen_extensions['execute']['post'])) {
                $post_arr = array();

                foreach($screenTemplate->screen_extensions['execute']['post'] as $post_key => $post) {
                    $post_arr[$post_key]['id'] = $post['extension_id'];
                    $post_arr[$post_key]['signature'] = $post['extension_lib'].';'.$post['extension'].';';
                }

                $patch_json_arr['func_extensions_post'] = $post_arr;
            }

            if(!empty($screenTemplate->screen_extensions['executeFunction']['execute'])) {
                $execute_arr = array();

                foreach($screenTemplate->screen_extensions['executeFunction']['execute'] as $execute_key => $execute) {
					if($replace_execute_extension_id != '') {
						$execute_arr[$execute_key]['id'] = $replace_execute_extension_id;
						$execute_arr[$execute_key]['signature'] = $replace_execute_extension_lib.';'.$replace_execute_extension.';';
					} else {
						$execute_arr[$execute_key]['id'] = $execute['extension_id'];
						$execute_arr[$execute_key]['signature'] = $execute['extension_lib'].';'.$execute['extension'].';';
					}
                }

                $patch_json_arr['func_extensions_execute'] = $execute_arr;
            }

			if (!empty($approvedMessagesCode)) {
                $patch_json_arr['confirmed_messages'] = $approvedMessagesCode;
            }

            //echo '<pre> screen_extension_functions :: '; print_r($screen_extension_functions);

            $processData = [
                "func_name" => "ExecuteAliasFramework",
                "func_param" => $patch_json_arr,
                "lib_name" => $tabData['screen_lib']
            ];

            //echo 'processData :: ' . json_encode($processData); die;

            $result = (new static())->processData($processData);

			if (($result['requestresult'] ?? '') != 'successfully') {
                return $result ?? null;
            }

			if (!empty($result['record_list'])) {
                return $result['record_list'];
            }

			//return (!empty($result['record_list'])) ? (object)$result['record_list'] : [];

            //$success = true;
        } else {
			return true;
		}

        /*if ($success) {
			if (!empty($result['record_list'])) {
                return (object)$result['record_list'];
            }

            //return $success;
        } else {
            return true;
            //return null;
        }*/

		return null;
    }

    public static function executeCustom($dataId, array $tabData, $getFunction, $pre = [], $execute, $post = [], $lastFoundData, $row_data = [], $approvedMessagesCode = [], $pk_key = '') {
		//echo 'in Screen Model executeCustom'; die;

		//echo '<pre> $dataId :: '; print_r($dataId); die;

		//echo $temp_key = key($dataId);

        if (empty($tabData['screen_tab_template'])) {
            return null;
        }

        /*$libName = $tabData['screen_lib'];
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
        }*/

		$screenTemplate = self::decodeTemplate($tabData['screen_tab_template'], true);

        $lib_name = 'CodiacSDK.Universal';

        $field_out_list = array();

        $func_param = array();
        $patch_list_arr = array();
        $patch_table_arr = array();
        $pk_list_arr = array();
        $patch_json_arr = array();
        $func_param_json = array();

        $check = 1;

		//echo '<pre> $screenTemplate->template_layout :: '; print_r($screenTemplate->template_layout); die;

		if(!empty($row_data)) {
			end($row_data);

			$alias_key = key($row_data);
			$explode_key = explode('.', $alias_key);

			$table_section_table_name = 'Search_'.$explode_key[1];

			$getPKList = CustomLibs::getPK($lib_name, $table_section_table_name);

			//$pk_list_explode = explode(';', $getPKList[0]);

			//echo '<pre> $getPKList :: '; print_r($getPKList); die;
			//echo '<pre> $pk_list_explode :: '; print_r($pk_list_explode); die;

			//$pk_list_key = $explode_key[0].'.'.$explode_key[1].'.'.$getPKList[1];
			//echo $pk_list_key_val = $dataId[$pk_list_explode[0]]; die;
		}

		//echo '<pre> $row_data :: '; print_r($row_data);

        foreach ($screenTemplate->template_layout as $item_key => $item) {
            $func_name = $item->data_source_get;
            $layout_type = $item->layout_type;

            if($layout_type == 'TABLE' || $layout_type == 'CHART')
                $isGetParent = true;
            else
                $isGetParent = false;

            $subDataPK = [];
            $relatedField = CustomLibs::getRelated($lib_name, $func_name);

            if ($isGetParent && !empty($dataId)) {
                if (CustomLibs::getTableName($lib_name, $func_name)) {
                    $subDataPK = CustomLibs::getPK($lib_name, $func_name);
                } else if (!empty($screenTemplate->search_configuration->pk_configuration)) {
                    $fieldParams = CommandData::getFieldListForQuery($dataId);
                }
            }

            if (empty($fieldParams)) {
                $fieldParams = CommandData::getFieldListForQuery($dataId, $relatedField);
            }

			//echo '<pre> $fieldParams :: '; print_r($fieldParams);

            $postData = [
                'lib_name' => $lib_name,
                'func_name' => $func_name,
                'alias_framework_info' => $screenTemplate->alias_framework,
                'search_function_info' => [
                    'config' => $screenTemplate->search_configuration,
                    'data' => $lastFoundData
                ]
            ];

			//echo '<pre> $subDataPK :: '; print_r($subDataPK);

            if ($isGetParent) {
                $additionalParam = CommandData::getGridFieldOutListForQuery($item, $subDataPK);
            } else {
                $additionalParam = CommandData::getFieldOutListForQuery($lib_name, $func_name);
            }

            if(!empty($field_out_list) && !empty($additionalParam['field_out_list']))
                if(array_diff($field_out_list, $additionalParam['field_out_list'])) {
                    $check = 1;
                } else {
                    $check = 0;
                }

			if(!empty($additionalParam['field_out_list'])) {
				foreach($additionalParam['field_out_list'] as $key_add => $val_add) {
					//echo '<pre> $val_add :: '; print_r($val_add);

					if(is_array($val_add))
						$additionalParam['field_out_list'][$key_add] = $val_add[0];
					else if(strpos($val_add, '__button_') !== false || strpos($val_add, '__image_') !== false)
						unset($additionalParam['field_out_list'][$key_add]);
					else
						$additionalParam['field_out_list'][$key_add] = $val_add;
				}
			}

            $field_out_list = $additionalParam['field_out_list'];

            if($check == 1) {
                $data = CommandData::getData($fieldParams, $postData, $additionalParam);

				//echo '<pre> $data :: '; print_r($data);
				//echo '<pre> $data->list :: '; print_r($data->list);

                if(!empty($data->list)) {
                    $temp_arr = array();
                    $temp_arr2 = array();

                    foreach($data->list as $key1 => $val1) {
                        foreach($val1 as $key2 => $val2) {
							$temp_arr[$key2] = $val2;
                        }

                        if($isGetParent) {
							if(!empty($temp_arr)) {
								$temp_arr['update'] = true;

								array_push($patch_table_arr, $temp_arr);
							}
						} else {
                            array_push($patch_list_arr, $temp_arr);
                        }
                    }

                    foreach($data->pkList as $key1 => $val1) {
						if(!empty($row_data))
							$temp_arr2 = array();

                        foreach($val1 as $key2 => $val2) {
							if(!empty($row_data)) {
								if(array_key_exists($key2, $row_data) && $val2 == $pk_key)
									$temp_arr2[$key2] = $val2;
							} else {
								$temp_arr2[$key2] = $val2;
							}
						}

						//echo '<pre> $temp_arr2 :: '; print_r($temp_arr2);

						if(!empty($temp_arr2))
							$pk_list_arr[] = [$temp_arr2];
                    }
                }
            }
        }

        if (empty($pk_list_arr) && !empty($data->subData) && !empty($row_data)) {
            foreach ($data->subData as $tName => $tData) {
                foreach ($tData as $tDataKey => $tDataValue) {
                    foreach ($tDataValue as $subTableName => $subTableData) {
                        foreach($subTableData as $dataRowKey => $dataRowVal) {
                            if ($dataRowVal['pk'] == $pk_key) {
                                unset($dataRowVal['pk']);
                                foreach ($dataRowVal as $k => $v) {
                                    $temp_arr2[$k] = $pk_key;
                                }
                            }
                        }
                    }
                }
            }
            if(!empty($temp_arr2))
                $pk_list_arr[] = [$temp_arr2];
        }

		if(!empty($row_data)) {
			$patch_table_arr = [];

			$row_data['update'] = true;

			array_push($patch_table_arr, $row_data);

			$patch_json_arr = ['PK' => $pk_list_arr, 'patch_json' => (object) $patch_table_arr];
		} else {
			$patch_json_arr = ['PK' => $pk_list_arr, 'patch_json' => $patch_table_arr];

			if(!empty($patch_list_arr)) {
				foreach($patch_list_arr[0] as $patch_list_key => $patch_list)
					$patch_json_arr['patch_json'][$patch_list_key] = $patch_list;
			}
		}

		//echo '<pre> $patch_json_arr :: '; print_r($patch_json_arr); die;

		//echo '<pre> $pre :: '; print_r($pre); die;

		if (!empty($pre)) {
			$pre_param = array();

			foreach($pre as $key => $pre_temp) {
				$pre_param[$key]['id'] = (int) $pre_temp['extension_id'];
				$pre_param[$key]['signature'] = $pre_temp['extension_lib'].';'.$pre_temp['extension'];
			}

			$patch_json_arr['func_extensions_pre'] = $pre_param;
		}

		if (!empty($execute)) {
			$execute_param = array();

			foreach($execute as $key => $execute_temp) {
				$execute_param[$key]['id'] = (int) $execute_temp['extension_id'];
				$execute_param[$key]['signature'] = $execute_temp['extension_lib'].';'.$execute_temp['extension'];
			}

			$patch_json_arr['func_extensions_execute'] = $execute_param;
		}

		if (!empty($post)) {
			$post_param = array();

			foreach($post as $key => $post_temp) {
				$post_param[$key]['id'] = (int) $post_temp['extension_id'];
				$post_param[$key]['signature'] = $post_temp['extension_lib'].';'.$post_temp['extension'];
			}

			$patch_json_arr['func_extensions_post'] = $post_param;
		}

		if (!empty($approvedMessagesCode)) {
			$patch_json_arr['confirmed_messages'] = $approvedMessagesCode;
		}

		$processData = [
			"func_name" => "ExecuteAliasFramework",
			"func_param" => $patch_json_arr,
			"lib_name" => $tabData['screen_lib']
		];

		//echo '<pre> $processData :: '; print_r($processData);
		//echo json_encode($processData); die;

		$result = (new static())->processData($processData);

		if (($result['requestresult'] ?? '') != 'successfully') {
            return $result ?? null;
        }

		return (!empty($result['record_list'])) ? (object)$result['record_list'] : null;
    }

    public function getList()
    {
        $list = parent::getList();
        return array_filter($list, function ($item) {
            return in_array('W', explode(';', $item['screen_tab_devices']));
        });
    }
}