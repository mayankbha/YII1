<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @property string|array $data_source_get;
 * @property string $viewName;
 * @property array $data;
 * @property array $config;
 */

namespace app\components;

use app\models\CommandData;
use app\models\CustomLibs;
use Yii;
use yii\base\Widget;

/**
 * Class BaseRenderWidget
 * @property \stdClass|array $data
 * @property \stdClass $_search_configuration
 */
class BaseRenderWidget extends Widget
{
    const FIELD_ACCESS_READ = 'R';
    const FIELD_ACCESS_UPDATE = 'E';
    const FIELD_ACCESS_FULL = 'U';
    const FIELD_ACCESS_NONE = 'N';

    const MAX_COUNT_SUBTABLES = 20;

    public $viewName = '';

    public $lib_name;
    public $func_name;
    public $configuration;
    public $mode = false;
    public $id;
    public $cache = false;
    public $lastFoundData = [];
    public $section_to_refresh = null;
    public $section_depth_value = null;
    public $button_action = null;
    public $header_fields = null;
    public $tableSectionFilterArray = null;

    public $_search_configuration = null;
    public $_alias_framework = null;

    public $limit = false;
    public $offset = false;
    public $isAjax = false;

    protected $isGrid = false;
    protected $isChartLine = false;

    protected $data = [];
    protected $dataAccess = [];

    protected $dataCount = 0;

    public $primary_table=null;
    public $inputValues = false;

    public $screen_tabs = [];

    /**
     * Js events lists, that don't connection with screen action (add, edit, delete and other)
     * @var array
     */
    static public $independentJsEvents = [
        'change'
    ];

    public function init()
    {
        //echo 'in init';

        parent::init();

        $this->fixedApiResult();

        //echo '<pre> $this :: '; print_r($this);

        $this->func_name = $this->configuration->data_source_get;

        if(is_object($this->_alias_framework))
            $this->primary_table = $this->_alias_framework->request_primary_table;
        else
            $this->primary_table = $this->_alias_framework['request_primary_table'];

        //echo '<pre> $this :: '; print_r($this);
        //echo '<pre> $this->configuration :: '; print_r($this);

        if ($this->id) {
            $this->getData($this->isGrid || $this->isChartLine);
        } else if(isset($this->button_action) && $this->button_action != '' && $this->button_action == 'search_submit') {
            $this->getSearchScreenData();
        }
    }

    protected function fixedApiResult()
    {
        //echo 'in fixedApiResult';

        $labels = [];
        $paramsType = [];
        $formatType = [];
        $config = $this->configuration->layout_configuration;

        $prepareFunction = function ($param) use (&$labels, &$paramsType, &$formatType, $config) {
            if(is_object($this->_alias_framework))
                $newKey = CommandData::fixedApiResult($param, (!empty($this->_alias_framework) && $this->_alias_framework->enable));
            else
                $newKey = CommandData::fixedApiResult($param, (!empty($this->_alias_framework) && $this->_alias_framework['enable']));

            if (!empty($config->labels[$param])) {
                $labels[$newKey] = ($config->labels[$param]);
            }
            $paramsType[$newKey] = isset($config->params_type[$param]) ? $config->params_type[$param] : null;
            $formatType[$newKey] = isset($config->format_type[$param]) ? $config->format_type[$param] : null;

            return $newKey;
        };

        if ($this->isChartLine && !empty($config->params['x']) && !empty($config->params['y'])) {
            foreach ($config->params['x'] as $key => $param) {
                $config->params['x'][$key] = $prepareFunction($param);
            }
            foreach ($config->params['y'] as $key => $param) {
                $config->params['y'][$key] = $prepareFunction($param);
            }
        } else {
            foreach ($config->params as $key => $param) {
                $config->params[$key] = $prepareFunction($param);
            }
        }
        $config->labels = $labels;
        $config->params_type = $paramsType;
        $config->format_type = $formatType;
    }

    /**
     * Getting data from API server
     *
     * @param boolean $isGetParent - Set TRUE if is a parent data
     */
    protected function getData($isGetParent)
    {
        //echo 'in BaseRenderWidget getData'; die;

        //echo $isGetParent;

		$workflow = false;
        $subDataPK = [];
        $relatedField = CustomLibs::getRelated($this->lib_name, $this->func_name);

        if ($isGetParent && !empty($this->id)) {
            if (CustomLibs::getTableName($this->lib_name, $this->func_name)) {
                $subDataPK = CustomLibs::getPK($this->lib_name, $this->func_name);
            } else if (is_object($this->_search_configuration) && !empty($this->_search_configuration->pk_configuration)) {
                $fieldParams = CommandData::getFieldListForQuery($this->id);
            } else if(!empty($this->_search_configuration['pk_configuration'])) {
                $fieldParams = CommandData::getFieldListForQuery($this->id);
            }
        }

		if (empty($fieldParams)) {
			$fieldParams = CommandData::getFieldListForQuery($this->id, $relatedField);
		}

        if (!empty($this->inputValues) && (empty($this->configuration->active_passive) || ($this->configuration->col_num != 0 && $this->configuration->row_num != 0))) {
            $postData = [
                'lib_name' => $this->lib_name,
                'func_name' => $this->func_name,
                'alias_framework_info' => $this->_alias_framework,
                'input_values' => $this->inputValues
            ];
        } else {
            $postData = [
                'lib_name' => $this->lib_name,
                'func_name' => $this->func_name,
                'alias_framework_info' => $this->_alias_framework,
                'search_function_info' => [
                    'config' => $this->_search_configuration,
                    'data' => $this->lastFoundData,
                ]
            ];
        }

        if (!empty($this->configuration->layout_configuration->params['x'])) {
            array_push($subDataPK, ...$this->configuration->layout_configuration->params['x']);
        }
        if (!empty($this->configuration->layout_configuration->params['y'])) {
            array_push($subDataPK, ...$this->configuration->layout_configuration->params['y']);
        }

        if($isGetParent) {
            $additionalParam = CommandData::getGridFieldOutListForQuery($this->configuration, $subDataPK);
        } else {
            $additionalParam = CommandData::getFieldOutListForQuery($this->lib_name, $this->func_name);
        }

        if(!empty($additionalParam['field_out_list'])) {
            foreach($additionalParam['field_out_list'] as $key_add => $val_add) {
                //echo '<pre> $val_add :: '; print_r($val_add);

                if(is_array($val_add))
                    $additionalParam['field_out_list'][$key_add] = $val_add[0];
                else
                    $additionalParam['field_out_list'][$key_add] = $val_add;
            }
        }

        //echo '<pre> $additionalParam :: '; print_r($additionalParam);

        $filter = array();

        if(!empty($this->tableSectionFilterArray)) {
            $search_key = null;

            //echo '<pre> $this->tableSectionFilterArray :: '; print_r($this->tableSectionFilterArray);

            foreach($this->tableSectionFilterArray as $key => $table_filter) {
                $search_key = key($table_filter);

                $filter[$search_key] = $table_filter[$search_key];
            }

            //echo '<pre> $filter :: '; print_r($filter);
        }

        if ($this->limit && $isGetParent) {
            $additionalParam = array_merge($additionalParam, [
                'limitnum' => $this->limit,
                'offsetnum' => $this->offset,
                'filter' => $filter
            ]);
        }

        if (!empty($this->screen_tabs)) {
            $additionalParam['screen_tabs'] = $this->screen_tabs;
        }
        //echo '<pre> $additionalParam :: '; print_r($additionalParam);

        if($isGetParent == 1)
    		$this->data = CommandData::getData($fieldParams, $postData, $additionalParam);
        else
            $this->data = CommandData::getData($fieldParams, $postData, $additionalParam);

        if ($this->limit && !$this->isAjax && !empty($relatedField)) {
            $selectID = CommandData::getData($fieldParams, $postData, $additionalParam);
            $this->dataCount = (!empty($selectID->list)) ? count($selectID->list) : 0;
        }

        //$this->dataCount = (!empty($this->data->list)) ? count($this->data->list) : 0;

        //echo '<pre> $this->data :: '; print_r($this->data); die;

        if ($this->data) {
            if (isset(Yii::$app->session['tabData']->fieldsAccess[$postData['lib_name']][$postData['func_name']])) {
                $this->dataAccess = Yii::$app->session['tabData']->fieldsAccess[$postData['lib_name']][$postData['func_name']];
            }

            $pkList = !empty($this->data->pkList) ? $this->data->pkList : [];
            $this->view->registerJs("common.setLastGetDataPK(" . json_encode($pkList) . ", '$this->func_name', $isGetParent)");

            if (!empty($this->data->list) && !empty($this->data->subData)) {
                $data = $this->getDataWithSubTables($this->data->list);
                $this->data = $data;
            } elseif ($isGetParent) {
                $this->data = $this->data->list;

                foreach ($this->data as $key => $row) {
                    $newPK = [];

                    foreach($subDataPK as $item) {
                        if (!empty($row[$item])) $newPK[] = $row[$item];
                    }

                    $newPK = implode(';', $newPK);

                    if($newPK != '')
                        $this->data[$key]['pk'] = $newPK;
                    else if(!empty($pkList) && isset($pkList[$key]))
                        $this->data[$key]['pk'] = $pkList[$key];
                }
            } else {
                $this->data = $this->data->list[0];
            }
        }
    }

    protected function getSearchScreenData()
    {
        //echo 'in getSearchScreenData';

        //echo '<pre> $header_fields :: '; print_r($this->header_fields);

        $subDataPK = [];

        $relatedField = CustomLibs::getRelated($this->lib_name, $this->func_name);

        if (CustomLibs::getTableName($this->lib_name, $this->func_name))
            $subDataPK = CustomLibs::getPK($this->lib_name, $this->func_name);

        $alias_input = array();
        $alias_output = array();

        if(!empty($this->header_fields)) {
            foreach($this->header_fields as $key => $header_field)
                $alias_input[$header_field['name']] = $header_field['value'];
        }

        //echo '<pre> $alias_input :: '; print_r($alias_input);

        $additionalParam = CommandData::getGridFieldOutListForQuery($this->configuration);

        //echo '<pre> additionalParam :: '; print_r($additionalParam);

        if(!empty($additionalParam) && !empty($additionalParam['field_out_list'])) {
            $alias_output = $additionalParam['field_out_list'];
        }

        //echo '<pre> $alias_output :: '; print_r($alias_output);

        /*if ($this->limit) {
            $additionalParam = array_merge($additionalParam, [
                'limitnum' => $this->limit,
                'offsetnum' => $this->offset
            ]);
        }*/

        $this->data = CommandData::searchScreenData($alias_input, $alias_output);

        /*if ($this->limit && !$this->isAjax) {
            $selectID = CommandData::getData($fieldParams, $postData, $additionalParam);
            $this->dataCount = (!empty($selectID->list)) ? count($selectID->list) : 0;
        }*/

        //echo '<pre>'; print_r($this->data);

        if ($this->data) {
            foreach ($this->data as $key => $row) {
                $newPK = [];

                foreach($subDataPK as $item) {
                    if (!empty($row[$item])) $newPK[] = $row[$item];
                }

                $newPK = implode(';', $newPK);

                $this->data[$key]['pk'] = $newPK;
            }
        }
    }

    public function run()
    {
        return $this->renderWidget();
    }

    protected function getRenderParams()
    {
        return ['data' => $this->data];
    }

    protected function renderWidget()
    {
        return empty($this->viewName) ? null : $this->render('@app/views/render/' . $this->viewName, $this->getRenderParams());
    }

    public function getSectionType()
    {
        return $this->configuration->layout_type;
    }

    public function executeLibFunction($fieldList, $postData, $additionalParam) {
        $postData['func_extensions'] = ($decodeJSON = json_decode($this->configuration->data_source_extension, true)) ? [$decodeJSON] : [$this->configuration->data_source_extension];
        return CommandData::getData($fieldList, $postData, $additionalParam);
    }

    public function generateJsTemplate($jsCode, $idBlock, $nameEvent)
    {
        if (!empty($idBlock)) {
            return /** @lang JavaScript */ '
                $("#' . $idBlock . '").on("' . $nameEvent . '", "input", function() {
                    try {
                        ' . base64_decode($jsCode) . '
                        $(this).removeClass("not-valid-data");
                    } catch(e) {
                        $(this).addClass("not-valid-data");
                        throw new Error(e.message);
                    }
                });
            ';
        }

        return null;
    }

    public function generateJsTemplateField($jsCode, $idBlock, $nameEvent, $field_type)
    {
        $jsCode = base64_decode($jsCode);

        if (!empty($idBlock)) {
            //echo $nameEvent;

            $skipErrors = '';

            if (in_array($nameEvent, self::$independentJsEvents)) {
               $skipErrors = 'var skipErrors = [];';
            }

            if($nameEvent == 'change')
                if($field_type != '' && $field_type == 'textarea')
                    $nameEvent = 'blur';
                else
                    $nameEvent = 'blur change';

            return $skipErrors .
                /** @lang JavaScript */ '            
                $(".' . $idBlock . '").on("'.$nameEvent.'", function () {
                    console.log("'.$nameEvent.'");

                    if ("' . $nameEvent . '" == "blur change") {
                        var flagName = "blurChangeFlag_' . $idBlock . '";
                        if (!common[flagName] && $(this).prop("readonly") != true) {
                            common[flagName] = true;
                            setTimeout(function () {
                                common[flagName] = false;
                            }, 100);
                        } else {
                            return false;
                        }
                    }

                    try {
                        ' . $jsCode . '
                        $(this).removeClass("not-valid-data");
                        this.setCustomValidity("");
                    } catch (e) {
                        var message = common.getErrorMessageI18N(e.message),
                            errorModal;
                        
                        this.setCustomValidity(message);
                        $(this).addClass("not-valid-data");
                        
                        if (errorModal = common.getErrorModalType(e.message)) {
                            errorModal(common.getErrorMessageI18N(e.message), $(this));
                        } else {
                            throw new Error(message);
                        }
                    }
                });
            ';
        }

        return null;
    }


    protected function getErrorModalType($jsCode)
    {
        $isConfirm = strpos($jsCode, '//confirm');

        return $isConfirm !== false;
    }

    public function generateEditJs($idBlock, $nameEvent, $index)
    {
        return /** @lang JavaScript */ '
            $("#' . $idBlock . ' input[data-sub-id!=\'-1\']").on("edit-left-table-custom-js", function(event) {
                if ($(event.target).closest("tr").data("key") == ' . $index . ') {
                    try {
                        $(event.target).trigger("' . $nameEvent . '");
                    } catch (e) {
                        common.customJsException(e.message);
                    }
                }
            });
        ';
    }

    public function generateInsertJs($idBlock, $index, $nameEvent)
    {
        return /** @lang JavaScript */ '
            $("#' . $idBlock . ' .add-sub-item").on("insert-left-table-custom-js", function() {
                try {
                    $("#' . $idBlock . ' tr:eq(' . $index . ') input").eq(0).trigger("' . $nameEvent . '");
                } catch (e) {
                    common.customJsException(e.message);
                }
            });
        ';
    }

    public function generateInsertJsTopTable($idBlock, $index, $nameEvent)
    {
        return /** @lang JavaScript */ '
            $("#' . $idBlock . ' .add-sub-item").on("insert-top-table-custom-js", function(event) {
                try {
                    $("#' . $idBlock . ' input").eq(' . $index . ').trigger("' . $nameEvent . '");
                } catch (e) {
                    common.customJsException(e.message);
                }
            });
        ';
    }

    public function generateEditJsTopTable($idBlock, $index, $nameEvent)
    {
        return /** @lang JavaScript */ '
            $("#' . $idBlock . ' input[data-sub-id!=\'-1\']").on("edit-top-table-custom-js", function(event) {
                if ($(event.target).closest("td")[0].cellIndex == ' . $index . ') {
                    try {
                        $(event.target).trigger("' . $nameEvent . '");
                    } catch (e) {
                        common.customJsException(e.message);
                    }
                }
            });
        ';
    }

    /*public function getFieldTempId($config)
    {
        if (!empty($config['identifier'])) {
            return $config['identifier'];
        }

        $filteredDataSource = str_replace([':', ',', '.'], '-', $this->configuration->data_source_get);

        $filteredName = CommandData::fixedApiResult($config['data_field'], (!empty($this->_alias_framework) && $this->_alias_framework->enable));
        $filteredName = str_replace([':', ',', '.'], '-' , $filteredName);

        return $filteredName;
    }*/

    public function getFieldId($config, $only_name = false)
    {
        if (!empty($config['identifier'])) {
            return $config['identifier'];
        }

        $filteredDataSource = str_replace([':', ',', '.'], '-', $this->configuration->data_source_get);

        $filteredName = CommandData::fixedApiResult($config['data_field'], (!empty($this->_alias_framework) && $this->_alias_framework->enable));
        $filteredName = str_replace([':', ',', '.'], '-' , $filteredName);

        if($only_name)
            return $filteredName;
        else
            return $filteredDataSource . '--' . $filteredName;
    }

	public function generateJsTemplateFunctionField($jsCode)
    {
		$jsCode = base64_decode($jsCode);

		return /** @lang JavaScript */ '
        ' . $jsCode . '
        ';
	}

    private function getDataWithSubTables($data)
    {
        foreach ($data as $key => $value) {
            $subTables = [];
            foreach ($value as $fieldName => $fieldData) {
                $data[$key]['pk'] = $this->data->pkList[$key][$fieldName] ?? '';

                $nameParts = explode('.', $fieldName);
                $tableName = implode('.', [$nameParts[0], $nameParts[1]]);
                $subTables[$tableName][$this->data->pkList[$key][$fieldName]] = $this->data->pkList[$key][$fieldName];

                if (!empty($this->data->subData[$tableName]) && !empty($this->data->subData[$tableName][$data[$key]['pk']])) {
                    $data[$key]['subData'][$tableName] = [$data[$key]['pk'] => $this->data->subData[$tableName][$data[$key]['pk']]];
                }

                $arrayKeys = [];
                $limitIteration = 0;
                do {
                    $baseSubTables = $subTables;
                    if (!empty($data[$key]['subData'])) {
                        foreach ($data[$key]['subData'] as $parent => $value) {
                            foreach ($value as $parentPK => $subTable) {
                                foreach ($subTable as $subTableName => $subTableRow) {
                                    foreach ($subTableRow as $subTableRowValue) {
                                        $arrayKeys[$subTableRowValue['pk']] = $subTableRowValue['pk'];
                                    }
                                    if (empty($subTables[$subTableName])) {
                                        $subTables[$subTableName] = $arrayKeys;
                                    } else {
                                        $subTables[$subTableName] = array_merge($subTables[$subTableName], $arrayKeys);
                                    }
                                }
                            }
                        }

                        foreach ($subTables as $subTableName => $keys) {
                            foreach ($keys as $pkKey) {
                                if (!empty($this->data->subData[$subTableName]) && !empty($this->data->subData[$subTableName][$pkKey])) {
                                    if (!empty($data[$key]['subData'][$subTableName])) {
                                        $data[$key]['subData'][$subTableName][$pkKey] = $this->data->subData[$subTableName][$pkKey];
                                    } else {
                                        $data[$key]['subData'][$subTableName] = [$pkKey => $this->data->subData[$subTableName][$pkKey]];
                                    }
                                }
                            }
                        }
                    }
                    $limitIteration++;
                } while ($subTables !== $baseSubTables && $limitIteration <= self::MAX_COUNT_SUBTABLES);
            }
        }

        return $data;
    }
}