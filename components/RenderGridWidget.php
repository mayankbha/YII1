<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use Yii;
use app\models\CommandData;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class RenderGridWidget extends BaseRenderWidget
{
    const DEFAULT_PAGE_LIMIT = 5;

    public $viewName = 'grid';
    public $isAjax = false;
    public $page = 1;

    public $row = false;
    public $col = false;
    public $tid = false;

    public $aliasFrameworkInfo = false;
    public $search_configuration = false;
    public $FoundData = false;

    protected $isGrid = true;

    public function init()
    {
        if ($this->isAjax) {
            if (!empty(Yii::$app->session['tabData']->tplData[$this->tid])) {
				$this->_alias_framework = $this->aliasFrameworkInfo;

                $template_layout = Yii::$app->session['tabData']->tplData[$this->tid]['tpl']->template_layout;
                foreach($template_layout as $section) {
                    if ($section->row_num == $this->row && $section->col_num == $this->col) {
                        $this->configuration = $section;
                    }
                }

				if(Yii::$app->session['tabData']->tplData[$this->tid]['tpl']->search_custom_query != null)
					$this->_search_configuration = (array) Yii::$app->session['tabData']->tplData[$this->tid]['tpl']->search_custom_query;
				else
					$this->_search_configuration = (array) Yii::$app->session['tabData']->tplData[$this->tid]['tpl']->search_configuration;

				$this->lastFoundData = $this->FoundData;
			}
        }

        //if ($this->configuration->layout_table->show_type == 'SCROLL') $this->limit = 0;
        if (!empty($this->configuration->layout_table->count)) $this->limit = (int) $this->configuration->layout_table->count;
        else $this->limit = self::DEFAULT_PAGE_LIMIT;

        $this->offset = ($this->page - 1) * $this->limit;

        parent::init();
    }

    /**
     * @return array|null|string
     * @throws \Exception
     */
    public function run()
    {
        if ($this->isAjax) return $this->getRenderParams();
        else return parent::run();
    }

    /**
     * @return array|string - Data for grid render
     * @throws \Exception
     */
    public function getRenderParams()
    {
        $columns = [];
        $data = [];
        $isTopOrientation = true;
        $includeColumnFilter = null;
        $includeExtendedSearch = null;
        $updateOnly = null;
        $rowIds = [];
        $tableTime = str_replace(".","",microtime(true));
        $idTable = 'activity_grid' . $tableTime;

        if (!empty($this->configuration->layout_table)) {
			$layout_table = $this->configuration->layout_type;
            $_layout_table = $this->configuration->layout_table;

            $isTopOrientation = $_layout_table->label_orientation == 'TOP';

			if(isset($_layout_table->include_column_filter))
				$includeColumnFilter = $_layout_table->include_column_filter;

			if(isset($_layout_table->include_extended_search))
				$includeExtendedSearch = $_layout_table->include_extended_search;

			if(isset($_layout_table->update_only))
				$updateOnly = $_layout_table->update_only;

			$tableConfig = $_layout_table->column_configuration;
            $config = $this->configuration->layout_configuration;
            $hiddenRows = [];
	
			//echo '<pre> $tableConfig :: '; print_r($tableConfig);
			//echo '<pre> $config :: '; print_r($config);

            foreach ($config->params as $paramKey => $param) {
				$field_name = '';
				$alias_field_name = '';

				if(isset($param) && $param != '' && $param != null) {
					$field_name_explode = explode('.', $param);
					$alias_field_name = $param;

					if(isset($field_name_explode[2]))
						$field_name = $field_name_explode[2];
				}

                if (!empty($config->labels_internationalization[$param][Yii::$app->language])) {
                    $label = $config->labels_internationalization[$param][Yii::$app->language];
                } else {
                    $label = $config->labels[$param];
                }

                $currentConfiguration = [];
                foreach ($tableConfig as $tableParam => $configuration) {
                    $tableParam = CommandData::fixedApiResult($tableParam, (!empty($this->_alias_framework) && $this->_alias_framework->enable));
                    if ($tableParam == $param) {
                        $currentConfiguration = $configuration;
                        break;
                    }
                }
                $currentConfiguration = ArrayHelper::map($currentConfiguration, 'name', 'value');
                if (!empty($this->configuration->layout_formatting)) {
                    $sectionFormatting = ArrayHelper::map($this->configuration->layout_formatting, 'name', 'value');

                    $sectionFormatting = array_filter($sectionFormatting);
                    $currentConfiguration = array_filter($currentConfiguration);

                    $currentConfiguration = array_merge($sectionFormatting, $currentConfiguration);
                }

                $headerOptions = '';
				$footerOptions = '';
				$data_hide = 0;

				if (isset($currentConfiguration['field_width_value']) && $currentConfiguration['field_width_value'] != '') $headerOptions .= 'width: ' . $currentConfiguration['field_width_value'] . ';';
                if (isset($currentConfiguration['hidden']) && $currentConfiguration['hidden'] == 'Y') $headerOptions .= 'display: none;';
				if (isset($currentConfiguration['hidden']) && $currentConfiguration['hidden'] == 'Y') $footerOptions .= 'display: none;';
                if (isset($currentConfiguration['hidden']) && $currentConfiguration['hidden'] == 'Y') $data_hide = 1;
                if (!empty($currentConfiguration['label_bold'])) $headerOptions .= 'font-weight: bold;';
                if (!empty($currentConfiguration['label_italic'])) $headerOptions .= 'font-style: italic;';

                $textDecoration = '';
                if (!empty($currentConfiguration['label_strike'])) $textDecoration = 'line-through';
                if (!empty($currentConfiguration['label_underline'])) $textDecoration .= ' underline';
                if ($textDecoration) $headerOptions .= 'text-decoration: ' . $textDecoration . ';';

                if (!empty($currentConfiguration['label_text_color'])) $headerOptions .= 'color: ' . $currentConfiguration['label_text_color'] . ' !important;';
                if (!empty($currentConfiguration['label_bg_color'])) $headerOptions .= 'background-color: ' . $currentConfiguration['label_bg_color'] . ' !important;';

                if (!empty($currentConfiguration['label_font_family'])) $headerOptions .= 'font-family: ' . $currentConfiguration['label_font_family'] . ' !important;';
                if (!empty($currentConfiguration['label_font_size'])) $headerOptions .= 'font-size: ' . $currentConfiguration['label_font_size'] . 'px !important;';

                $labelLink = $this->getLabelLink($currentConfiguration, $label);

                if ($isTopOrientation) {
                    $columns[] = [
                        'attribute' => $label,
                        'format' => 'raw',
                        'encodeLabel' => empty($labelLink),
                        'label' => $labelLink ?? $label,
                        //'headerOptions' => ['style' => $headerOptions],
                        //'headerOptions' => ['style' => $headerOptions, 'data-alias-field-name' => $alias_field_name, 'data-field-name' => $field_name],
						'headerOptions' => ['style' => $headerOptions, 'data-hide' => $data_hide, 'data-alias-field-name' => $alias_field_name, 'data-field-name' => $field_name],
                        //'footerOptions' => ['data-alias-field-name' => $alias_field_name, 'data-field-name' => $field_name],
						'footerOptions' => ['style' => $footerOptions, 'data-hide' => $data_hide, 'data-alias-field-name' => $alias_field_name, 'data-field-name' => $field_name],
                        'contentOptions' => (isset($currentConfiguration['hidden']) && $currentConfiguration['hidden'] == 'Y') ? ['style' => 'display: none;'] : []
                    ];
                }

                if (
                    (
                        $this->mode === RenderTabHelper::MODE_EDIT
                        || $this->mode === RenderTabHelper::MODE_INSERT
                        || $this->mode === RenderTabHelper::MODE_COPY
                    )
                    && $isTopOrientation
                    && empty($this->configuration->active_passive)
                ) {
                    $widgetConfig = [
                        'mode' => $this->mode,
                        'libName' => $this->lib_name,
                        'value' => null,
                        'dataField' => $param,
                        'dataAccess' => $this->dataAccess,
                        'config' => $currentConfiguration,
                        'dataId' => -1,
                        'isGridField' => true,
                        'data_source_get' => $this->configuration->data_source_get,
						'layout_type' => 'grid',
						'layout_table' => $layout_table,
						'readonly' => (isset($_layout_table->readonly) && $_layout_table->readonly) ? $_layout_table->readonly : null,
						'primary_table' => $this->primary_table
                    ];
                    if (!empty($this->_alias_framework) && $this->_alias_framework->enable) {
                        $widgetConfig['data_source_update'] = $this->_alias_framework->data_source_update;
                        $widgetConfig['data_source_delete'] = $this->_alias_framework->data_source_delete;
                        $widgetConfig['data_source_create'] = $this->_alias_framework->data_source_insert;

                        if (!empty($this->configuration->layout_table->alias_framework)) {
                            $widgetConfig['aliasFrameworkPKParts'] = $this->configuration->layout_table->alias_framework;
                        }
                    }

                    $data[-1][$label] = _FieldsHelper::widget($widgetConfig);
                }

                $rowIds = [];
                if (!empty($currentConfiguration['js_event_edit']) || !empty($currentConfiguration['js_event_insert'])) {
                    $id = 'activity_grid' . str_replace(".","",microtime(true));
                    $rowIds[$paramKey] = $id;
                }

                $template = '';
                $template_funtion = '';
                $field_id = str_replace([':', ',', '.', ' '], '-', $param) . '_' . RenderTabHelper::$template_id;
                if ($isTopOrientation) {
                    $nameEventEdit = 'js_table_edit' . rand();
                    $nameEventInsert = 'js_table_insert' . rand();
                    if (!empty($currentConfiguration['js_event_edit'])) {
                        $template = $this->generateJsTemplate($currentConfiguration['js_event_edit'], $idTable, $nameEventEdit);
                        $template .= $this->generateEditJsTopTable($idTable, $paramKey, $nameEventEdit);
						//$template_funtion .= $this->generateJsTemplateFunctionField($currentConfiguration['js_event_edit']);
                    } if (!empty($currentConfiguration['js_event_insert'])) {
                        //$template .= $this->generateJsTemplate($currentConfiguration['js_event_insert'], $idTable, $nameEventInsert);
                        //$template .= $this->generateInsertJsTopTable($idTable, $paramKey, $nameEventInsert);
                    }
                    if (!empty($currentConfiguration['js_event_change'])) {
                        $template .= $this->generateJsTemplateField($currentConfiguration['js_event_change'], $field_id, 'blur', '');
                    }
                    if (!empty($currentConfiguration['js_event_onfocus'])) {
                        $template .= $this->generateJsTemplateField($currentConfiguration['js_event_onfocus'], $field_id, 'focus', '');
                    }
                } else {
                    $nameEventEdit = 'js_table_edit' . rand();
                    $nameEventInsert = 'js_table_insert' . rand();
                    if (!empty($currentConfiguration['js_event_edit'])) {
                        $template = $this->generateJsTemplate($currentConfiguration['js_event_edit'], $idTable, $nameEventEdit);
                        $template .= $this->generateEditJs($idTable, $nameEventEdit, $paramKey);
                        //$template_funtion .= $this->generateJsTemplateFunctionField($currentConfiguration['js_event_edit']);
                    }
                    if (!empty($currentConfiguration['js_event_insert'])) {
                        //$template .= $this->generateJsTemplate($currentConfiguration['js_event_insert'], $idTable, $nameEventInsert);
                        //$template .= $this->generateInsertJs($idTable, $paramKey + 2, $nameEventInsert);
                    }
                    if (!empty($currentConfiguration['js_event_change'])) {
                        $template .= $this->generateJsTemplateField($currentConfiguration['js_event_change'], $field_id, 'blur', '');
                    }
                    if (!empty($currentConfiguration['js_event_onfocus'])) {
                        $template .= $this->generateJsTemplateField($currentConfiguration['js_event_onfocus'], $field_id, 'focus', '');
                    }
                }

                if ($template) {
                    $this->view->registerJs($template);
                    $this->view->registerJs($template_funtion);
                }

                if (
                    !$isTopOrientation
                    && ($updateOnly == null)
                    && (
                        $this->mode === RenderTabHelper::MODE_EDIT
                        || $this->mode === RenderTabHelper::MODE_INSERT
                        || $this->mode === RenderTabHelper::MODE_COPY
                    )
                    && empty($this->configuration->active_passive)
                ) {
                    $data[-1]['header'] = '';
                    $data[-1]['new'] = Html::tag('span', null, ['class' => 'glyphicon glyphicon-plus sub-item-control add-sub-item']);
                }

				//echo '<pre> $this->data :: '; print_r($this->data);

                if ($this->data) {
                    for ($i = 0; $i < count($this->data); $i++) {
                        $widgetConfig = [
                            'mode' => $this->mode,
                            'libName' => $this->lib_name,
                            'value' => (!empty($this->data[$i][$param])) ? $this->data[$i][$param] : null,
                            'dataField' => $param,
                            'dataAccess' => $this->dataAccess,
                            'config' => $currentConfiguration,
                            'dataId' => (isset($this->data[$i]['pk'])) ? $this->data[$i]['pk'] : null,
                            'isGridField' => true,
                            'data_source_get' => $this->configuration->data_source_get,
                            'layout_type' => 'grid',
                            'cnt' => $i,
                            'tmpData' => $this->data,
							'layout_table' => $layout_table,
							'readonly' => (isset($_layout_table->readonly) && $_layout_table->readonly) ? $_layout_table->readonly : null,
                            'layout_config' => $config,
							'primary_table' => $this->primary_table
                        ];

                        if (!empty($this->_alias_framework) && $this->_alias_framework->enable) {
                            $widgetConfig['dataId'] = $tableTime . $this->configuration->row_num . $this->configuration->col_num . $this->page . $i;
                            $widgetConfig['data_source_update'] = $this->_alias_framework->data_source_update;
                            $widgetConfig['data_source_delete'] = $this->_alias_framework->data_source_delete;
                            $widgetConfig['data_source_create'] = $this->_alias_framework->data_source_insert;

                            if (!empty($this->configuration->layout_table->alias_framework)) {
                                $widgetConfig['aliasFrameworkPKParts'] = $this->configuration->layout_table->alias_framework;
                            }

                            $pks = CommandData::getPKAliasFramework();
                            foreach ($pks as $pksInfo) {
                                if (stristr($param, '.') == ('.' . $pksInfo['table_name'] . '.' . $pksInfo['column_name'])) {
                                    $widgetConfig['config']['edit_type'] = _FieldsHelper::PROPERTY_READ_ONLY;
                                }
                            }
                        }

                        $field = _FieldsHelper::widget($widgetConfig);

						if(isset($this->data[$i]['pk']))
							$field = $this->addSubTableToField($param, $this->data[$i], $field, $this->data[$i]['pk']);
						else
							$field = $this->addSubTableToField($param, $this->data[$i], $field, array());

                        if ($isTopOrientation) {
                            $data[$i][$label] = $field;
                            if (isset($this->data[$i]['pk'])) {
                                $data[$i]['pk'] = is_array($this->data[$i]['pk']) ? current($this->data[$i]['pk']) : $this->data[$i]['pk'];
                            } else {
                                $data[$i]['pk'] = null;
                            }

                            if (
                                ($updateOnly == null)
                                && (
                                    $this->mode === RenderTabHelper::MODE_EDIT
                                    || $this->mode === RenderTabHelper::MODE_INSERT
                                    || $this->mode === RenderTabHelper::MODE_COPY
                                )
                                && empty($this->configuration->active_passive)
                            ) {
                                $data[$i]['control'] = Html::tag('span', null, [
                                    'class' => 'glyphicon glyphicon-trash sub-item-control remove-sub-item',
                                    'data-id' => $widgetConfig['dataId']
                                ]);
                            }

							if($this->mode === null || $this->mode === '') {
								if(!preg_match('/__[\w]+/', $param)) {
									if($columns[$paramKey]['headerOptions']['data-hide'] == 1)
										$data_hide = 'display: none;';
									else
										$data_hide = '';

									$columns[$paramKey]['contentOptions'] = function ($model, $index, $column) use ($param, $data_hide) {
									    $dataPKVal = null;
									    if (isset($this->data[$index]['pk'])) {
                                            $dataPKVal = (is_array($this->data[$index]['pk']) && !empty($this->data[$index]['pk'][$param]))
                                                ? $this->data[$index]['pk'][$param]
                                                : $this->data[$index]['pk'];
                                        }
                                        return [
                                            'style' => $data_hide,
                                            'data-column-alias-name' => $param,
                                            'data-col-val' => (isset($this->data[$index][$param])) ? $this->data[$index][$param] : null,
                                            'data-pk-val' => $dataPKVal,
                                        ];
									};
								}

								/*$data_col_val = $this->data[$i];

								foreach ($columns as $cellKey => $cell) {
									if(isset($cell['headerOptions']['data-alias-field-name']) && !preg_match('/__[\w]+/', $cell['headerOptions']['data-alias-field-name'])) {
										if($cell['headerOptions']['data-hide'] == 1)
											$data_hide = 'display: none;';
										else
											$data_hide = '';

										$columns[$cellKey]['contentOptions'] = function ($model, $key, $index, $column) use ($cell, $data_col_val, $param, $data_hide) {
											if(isset($this->data[$index][$cell['headerOptions']['data-alias-field-name']]))
												return ['style' => $data_hide, 'data-column-alias-name' => $cell['headerOptions']['data-alias-field-name'], 'data-col-val' => $this->data[$index][$cell['headerOptions']['data-alias-field-name']]];
										};
									}
								}*/
							}
                        } else {
                            if (
                                ($updateOnly == null)
                                && (
                                    $this->mode === RenderTabHelper::MODE_EDIT
                                    || $this->mode === RenderTabHelper::MODE_INSERT
                                    || $this->mode === RenderTabHelper::MODE_COPY
                                )
                                && empty($this->configuration->active_passive)
                            ) {
                                $data[-1][$i] = Html::tag('span', null, [
                                    'class' => 'glyphicon glyphicon-trash sub-item-control remove-sub-item',
                                    'data-id' => $widgetConfig['dataId']
                                ]);
                            }

                            $data[$paramKey][$i] = $field;
                            $columns[$i] = [
                                'attribute' => $i,
                                'format' => 'raw',
								'label' => $i
                            ];

                            if (isset($currentConfiguration['hidden']) && $currentConfiguration['hidden'] == 'Y' && !in_array($paramKey, $hiddenRows)) {
                                $hiddenRows[] = $paramKey;
                            }
                        }
                    }
                }

                if (!$isTopOrientation) {
                    $data[$paramKey]['header'] = Html::tag('span', $label, ['style' => $headerOptions]);
                    if (
                        ($updateOnly == null)
                        && (
                            $this->mode === RenderTabHelper::MODE_EDIT
                            || $this->mode === RenderTabHelper::MODE_INSERT
                            || $this->mode === RenderTabHelper::MODE_COPY
                        )
                        && empty($this->configuration->active_passive)
                    ) {
                        $data[-1]['header'] = '';
                        $data[-1]['new'] = Html::tag('span', null, ['class' => 'glyphicon glyphicon-plus sub-item-control add-sub-item']);
                    }

                    if ($this->mode === RenderTabHelper::MODE_EDIT || $this->mode === RenderTabHelper::MODE_INSERT) {
                        $widgetConfig = [
                            'mode' => $this->mode,
                            'libName' => $this->lib_name,
                            'value' => null,
                            'dataField' => $param,
                            'dataAccess' => $this->dataAccess,
                            'config' => $currentConfiguration,
                            'dataId' => -1,
                            'isGridField' => true,
                            'data_source_get' => $this->configuration->data_source_get,
							'layout_type' => 'grid',
							'layout_table' => $layout_table,
							'readonly' => (isset($_layout_table->readonly) && $_layout_table->readonly) ? $_layout_table->readonly : null,
							'primary_table' => $this->primary_table
                        ];

                        if (!empty($this->_alias_framework) && $this->_alias_framework->enable) {
                            $widgetConfig['data_source_update'] = $this->_alias_framework->data_source_update;
                            $widgetConfig['data_source_delete'] = $this->_alias_framework->data_source_delete;
                            $widgetConfig['data_source_create'] = $this->_alias_framework->data_source_insert;

                            if (!empty($this->configuration->layout_table->alias_framework)) {
                                $widgetConfig['aliasFrameworkPKParts'] = $this->configuration->layout_table->alias_framework;
                            }
                        }

                        $data[$paramKey]['new'] = _FieldsHelper::widget($widgetConfig);
                    }
                }
            }

            if (!$isTopOrientation) {
                if ($this->mode === RenderTabHelper::MODE_EDIT || $this->mode === RenderTabHelper::MODE_INSERT || $this->mode === RenderTabHelper::MODE_COPY) {
                    array_unshift($columns, [
                        'attribute' => 'new',
                        'format' => 'raw'
                    ]);
                }
                array_unshift($columns, [
                    'attribute' => 'header',
                    'format' => 'html',
                    'contentOptions' => ['style' => '
                        border-right: 2px solid #ddd;
                        white-space: nowrap;
                    ']
                ]);

                foreach ($columns as $cellKey => $cell) {
                    $columns[$cellKey]['contentOptions'] = function ($model, $key, $index, $grid) use ($cell, $hiddenRows) {
                        $result = ['style' => ''];
                        if ($cell['attribute'] == 'header') {
                            $result['style'] = 'border-right: 2px solid #ddd; white-space: nowrap;';
                        }

                        if (in_array($index, $hiddenRows)) {
                            $result['style'] .= 'display: none;';
                        }

                        return $result;
                    };
                }
            }

            if (
                (
                    $this->mode === RenderTabHelper::MODE_EDIT
                    || $this->mode === RenderTabHelper::MODE_INSERT
                    || $this->mode === RenderTabHelper::MODE_COPY
                )
                && $isTopOrientation
                && ($updateOnly == null)
                && empty($this->configuration->active_passive)
            ) {
                $data[-1]['control'] = Html::tag('span', null, ['class' => 'glyphicon glyphicon-plus sub-item-control add-sub-item']);
                $columns[] = [
                    'attribute' => 'control',
                    'format' => 'raw',
                    'headerOptions' => ['style' => 'display: none;'],
                ];
            }
        }

        if (!empty($this->configuration->active_passive)) {
            $radioHeader = [
                'attribute' => '__selected_row',
                'format' => 'raw',
                'label' => '',
                'headerOptions' => [
                    'style' => [
                        'color' => 'rgba(0,0,0,0)',
                    ]
                ],
                'contentOptions' => [
                    'class' => 'text-center active-table-row'
                ]
            ];
            array_unshift($columns, $radioHeader);

            foreach ($data as $dkey => $dvalue) {
                $radio = Html::input('radio', 'activeRow_' . RenderTabHelper::$template_id, $dkey, ['data-pk' => $dvalue['pk']]);
                $data[$dkey]['__selected_row'] = $radio;
            }
        }

        if ($this->isAjax) return json_encode($data);

		//echo '<pre> $data :: '; print_r($data);
		//echo '<pre> $columns :: '; print_r($columns);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'sort' => false,
            'pagination' => (boolean)$this->dataCount
        ]);

        if (isset($_layout_table)) {
            if ($_layout_table->show_type == 'SCROLL') {
                $this->dataCount = false;
            }
        }

		//$temp_count = count($dataProvider->allModels);

		//echo '<pre> $this->configuration :: '; print_r(Yii::$app->session['tabData']);
		//echo '$this->tid :: ' . RenderTabHelper::$template_id;

        return [
            'idTable' => $idTable,
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'ids' => $rowIds,
            'isTopOrientation' => $isTopOrientation,
            'includeColumnFilter' => $includeColumnFilter,
            'includeExtendedSearch' => $includeExtendedSearch,
            'updateOnly' => $updateOnly,
			'mode' => $this->mode,
            'limit' => $this->limit,
            //'pageCount' => ($temp_count) ? ceil($temp_count / $this->limit) : false,
            'pageCount' => ($this->dataCount) ? ceil($this->dataCount / $this->limit) : false,
            'scrollPixelHeight' => !empty($this->configuration->layout_table->scroll_pixel_height) ? $this->configuration->layout_table->scroll_pixel_height : 0,
            'isScrollTable' => !empty($this->configuration->layout_table->show_type) && ($this->configuration->layout_table->show_type == 'SCROLL'),
			'row_num' => $this->configuration->row_num,
			'col_num' => $this->configuration->col_num,
			'tab_id' => RenderTabHelper::$template_id,
			'search_configuration' => $this->_search_configuration
        ];
    }

    private function addSubTableToField(
        string $param,
        array $data,
        string $field,
        $subTableRowPk
    ) {
        $random = rand();
        $addToField = '';
        $fieldsHasSubTable = [];

        if (!empty($this->configuration->sub_tables_template)) {
            $fieldsHasSubTable = array_map(function ($subTable) {
                return $subTable['linked_to'];
            }, $this->configuration->sub_tables_template);
        }

        foreach ($fieldsHasSubTable as $fieldKey => $fieldName) {
            if ($fieldName == $param) {
                $subTableTemplate = $this->configuration->sub_tables_template[$fieldKey];
                $subTableData = [];
                $subTableColumns = [];

                foreach ($subTableTemplate['layout_configuration']['params'] as $subTableParamKey => $subTableParam) {
                    $subTableLabel = $subTableTemplate['layout_configuration']['labels'][$subTableParam];
                    $parentTableName = substr($param, 0, strrpos($param, "."));
                    $subTableName = substr($subTableParam, 0, strrpos($subTableParam, "."));
                    $subTableDataParams = [];
                    $currentConfiguration = [];
                    if (
                        !empty($data['subData'])
                        && !empty($data['subData'][$parentTableName])
                        && !empty($data['subData'][$parentTableName][$subTableRowPk])
                        && !empty($data['subData'][$parentTableName][$subTableRowPk][$subTableName])
                    ) {
                        $subTableDataParams = $data['subData'][$parentTableName][$subTableRowPk][$subTableName];
                    }

                    if (!empty($subTableTemplate['layout_configuration']['label_internationalization'][$subTableParam][Yii::$app->language])) {
                        $subTableLabel = $subTableTemplate['layout_configuration']['label_internationalization'][$subTableParam][Yii::$app->language];
                    } else {
                        $subTableLabel = $subTableTemplate['layout_configuration']['labels'][$subTableParam];
                    }

                    if (!empty($subTableTemplate['layout_table']['column_configuration'][$subTableParam])) {
                        $currentConfiguration = ArrayHelper::map($subTableTemplate['layout_table']['column_configuration'][$subTableParam], 'name', 'value');
                    }

                    $contentOptions = (isset($currentConfiguration['hidden']) && $currentConfiguration['hidden'] == 'Y') ? ['style' => 'display: none;'] : [];

                    foreach ($subTableDataParams as $rowKey => $rowData) {
                        $widgetConfig = [
                            'value' => $rowData[$subTableParam],
                            'dataField' => $subTableParam,
                            'config' => $currentConfiguration,
                            'isGridField' => true,
                            'layout_type' => 'grid',
                            'layout_table' => $subTableTemplate['layout_table'],
                            'readonly' => true,
                            'layout_config' => $subTableTemplate,
                        ];
                        $subTableField = _FieldsHelper::widget($widgetConfig);

                        // recursion is used at this point
                        $subTableField = $this->addSubTableToField($subTableParam, $data, $subTableField, $rowData['pk']);

                        $subTableData[$rowKey][$subTableLabel] = $subTableField;
                        $contentOptions = array_merge(
                            $contentOptions,
                            [
                                'data-column-alias-name' => $subTableParam,
                                'data-col-val' => $rowData[$subTableParam],
                                'data-pk-val' => $rowData['pk']
                            ]
                        );
                    }

                    $headerOptions = '';
                    if (isset($currentConfiguration['field_width_value']) && $currentConfiguration['field_width_value'] != '') $headerOptions .= 'width: ' . $currentConfiguration['field_width_value'] . ' !important;';
                    if (isset($currentConfiguration['hidden']) && $currentConfiguration['hidden'] == 'Y') $headerOptions .= 'display: none !important;';
                    if (!empty($currentConfiguration['label_bold'])) $headerOptions .= 'font-weight: bold !important;';
                    if (!empty($currentConfiguration['label_italic'])) $headerOptions .= 'font-style: italic !important;';

                    if (!empty($currentConfiguration['label_text_color'])) $headerOptions .= 'color: ' . $currentConfiguration['label_text_color'] . ' !important;';
                    if (!empty($currentConfiguration['label_bg_color'])) $headerOptions .= 'background-color: ' . $currentConfiguration['label_bg_color'] . ' !important;';
                    if (!empty($currentConfiguration['label_font_family'])) $headerOptions .= 'font-family: ' . $currentConfiguration['label_font_family'] . ' !important;';
                    if (!empty($currentConfiguration['label_font_size'])) $headerOptions .= 'font-size: ' . $currentConfiguration['label_font_size'] . 'px !important;';

                    $subTableColumns[] = [
                        'attribute' => $subTableLabel,
                        'format' => 'raw',
                        'label' => $subTableLabel,
                        'headerOptions' => ['style' => $headerOptions],
                        'contentOptions' => $contentOptions
                    ];

                    if (strpos($subTableParam, "__") === 0) {
                        $widgetConfig = [
                            'value' => $subTableParam,
                            'dataField' => $subTableParam,
                            'config' => $currentConfiguration,
                            'isGridField' => true,
                            'layout_type' => 'grid',
                            'layout_table' => $subTableTemplate['layout_table'],
                            'readonly' => true,
                            'layout_config' => $subTableTemplate,
                        ];
                        $specialElement = _FieldsHelper::widget($widgetConfig);

                        foreach ($subTableData as $key => $value) {
                            $subTableData[$key][$subTableLabel] = $specialElement;
                        }
                    }
                }

                $subTableDataProvider = new ArrayDataProvider([
                    'allModels' => $subTableData,
                    'sort' => false,
                ]);

                $subTableLimit = (int) ($subTableTemplate['layout_table']['count'] ?? 0);
                $overflowStyle = '';
                if ($subTableLimit && $subTableLimit <= count($subTableDataProvider->allModels)) {
                    $overflowStyle = "max-height:550px; overflow:auto;";
                }

                $subTable = GridView::widget([
                    'dataProvider' => $subTableDataProvider,
                    'columns' => $subTableColumns,
                    'layout' => '<div class="table-responsive">{items}</div>',
                    'tableOptions' => [
                        'class' => 'table table-hover table-bordered common-table-section-class',
                    ],
                ]);

                $addToField .= '<div class="sub-table-wrapper hide" style="' . $overflowStyle . '">' . $subTable . '</div>';
            }
        }

        if (!empty($addToField)) {
            $checkbox = '<span class="show-sub-table" data-field="' . $param . $random . '" title="Show / Hide Subtables"></span>&nbsp;';
            $field = $checkbox . $field . $addToField;
        }

        return $field;
    }

    /**
     * @param array $currentConfiguration
     * @param string $label
     * @return string|null
     */
    private function getLabelLink(array $currentConfiguration, string $label)
    {
        if (!empty($currentConfiguration['table_header_link'])) {
            $thLabelParams = [];

            foreach ($currentConfiguration['table_header_link'] as $key => $val) {
                $thLabelParams[$val['name']] = $val['value'];
            }

            if (!empty($thLabelParams['type-link']) && !empty($thLabelParams['field_link_menu'])) {
                $labelUrl = ['/screen/index',
                    'menu' => $thLabelParams['field_link_menu'],
                    'screen' => $thLabelParams['field_group_screen_link'],
                    '#' => 'tab=' . $thLabelParams['field_screen_link'] . '&search[' . $thLabelParams['field_settings_link'] . ']=' . ($this->lastFoundData[$thLabelParams['field_settings_link']] ?? ''),
                    'isFrame' => $thLabelParams['type-link'] == '_modal'
                ];
                return Html::a(
                    $label,
                    $labelUrl,
                    [
                        'data-field-refresh-base-page-when-exit' => $thLabelParams['refresh_base_page_when_Exit'] ?? '',
                        'target' => $thLabelParams['type-link'],
                        'class' => 'field-custom-link'
                    ]
                );
            }
        }
        return null;
    }
}