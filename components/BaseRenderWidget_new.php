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

    public $viewName = '';

    public $lib_name;
    public $func_name;
    public $configuration;
    public $mode = false;
    public $id;
    public $cache = false;
    public $lastFoundData = [];

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

    /**
     * Js events lists, that don't connection with screen action (add, edit, delete and other)
     * @var array
     */
    static public $independentJsEvents = [
        'change'
    ];

    public function init()
    {
        parent::init();

        $this->fixedApiResult();

        $this->func_name = $this->configuration->data_source_get;
        if ($this->id) {
            $this->getData($this->isGrid || $this->isChartLine);
        }
    }

    protected function fixedApiResult()
    {
        $labels = [];
        $paramsType = [];
        $formatType = [];
        $config = $this->configuration->layout_configuration;

        $prepareFunction = function ($param) use (&$labels, &$paramsType, &$formatType, $config) {
            $newKey = CommandData::fixedApiResult($param, (!empty($this->_alias_framework) && $this->_alias_framework->enable));

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
        $subDataPK = [];
        $relatedField = CustomLibs::getRelated($this->lib_name, $this->func_name);

        if ($isGetParent && !empty($this->id)) {
            if (CustomLibs::getTableName($this->lib_name, $this->func_name)) {
                $subDataPK = CustomLibs::getPK($this->lib_name, $this->func_name);
            } else if (!empty($this->_search_configuration->pk_configuration)) {
                $fieldParams = CommandData::getFieldListForQuery($this->id);
            }
        }


        if (empty($fieldParams)) {
            $fieldParams = CommandData::getFieldListForQuery($this->id, $relatedField);
        }

        $postData = [
            'lib_name' => $this->lib_name,
            'func_name' => $this->func_name,
            'alias_framework_info' => $this->_alias_framework,
            'search_function_info' => [
                'config' => $this->_search_configuration,
                'data' => $this->lastFoundData
            ]
        ];

        if ($isGetParent) {
            $additionalParam = CommandData::getGridFieldOutListForQuery($this->configuration, $subDataPK);
        } else {
            $additionalParam = CommandData::getFieldOutListForQuery($this->lib_name, $this->func_name);
        }

        if ($this->limit && $isGetParent) {
            $additionalParam = array_merge($additionalParam, [
                'limitnum' => $this->limit,
                'offsetnum' => $this->offset
            ]);
        }

        $this->data = CommandData::getData($fieldParams, $postData, $additionalParam);

        if ($this->limit && !$this->isAjax && !empty($relatedField)) {
            $selectID = CommandData::getData($fieldParams, $postData, ['field_out_list' => [$relatedField]]);
            $this->dataCount = (!empty($selectID->list)) ? count($selectID->list) : 0;
        }

        if ($this->data) {
            if (isset(Yii::$app->session['tabData']->fieldsAccess[$postData['lib_name']][$postData['func_name']])) {
                $this->dataAccess = Yii::$app->session['tabData']->fieldsAccess[$postData['lib_name']][$postData['func_name']];
            }

            $pkList = !empty($this->data->pkList) ? json_encode($this->data->pkList) : '[]';
            $this->view->registerJs("common.setLastGetDataPK($pkList, '$this->func_name', $isGetParent)");

            if ($isGetParent) {
                $this->data = $this->data->list;
                foreach ($this->data as $key => $row) {
                    $newPK = [];
                    foreach($subDataPK as $item) {
                        if (!empty($row[$item])) $newPK[] = $row[$item];
                    }
                    $newPK = implode(';', $newPK);
                    $this->data[$key]['pk'] = $newPK;
                }
            } else {
                $this->data = $this->data->list[0];
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

	public function getStringsBetween($str, $start='function getCurentDate() {', $end='}', $with_from_to=true) {
		$arr = [];
		$last_pos = 0;
		$last_pos = strpos($str, $start, $last_pos);
		while ($last_pos !== false) {
			$t = strpos($str, $end, $last_pos);
			$arr[] = ($with_from_to ? $start : '').substr($str, $last_pos + 1, $t - $last_pos - 1).($with_from_to ? $end : '');
			$last_pos = strpos($str, $start, $last_pos+1);
		}
		return $arr;
	}

    public function generateJsTemplate($jsCode, $idBlock, $nameEvent)
    {
        if (!empty($idBlock)) {
			$content = base64_decode($jsCode);

			$temp = $this->getStringsBetween($content);

            return /** @lang JavaScript */ '
                $("#' . $idBlock . '").on("' . $nameEvent . '", "input", function() {
                    try {
						' . $content . '
                        ' . json_encode($temp) . '
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

    public function generateJsTemplateField($jsCode, $idBlock, $nameEvent)
    {
        $jsCode = base64_decode($jsCode);
        if (!empty($idBlock)) {
            $skipErrors = '';
            if (in_array($nameEvent, self::$independentJsEvents)) {
               $skipErrors = 'var skipErrors = [];';
            }

            return $skipErrors .
                /** @lang JavaScript */ '            
                $("#' . $idBlock . '").on("' . $nameEvent . '", function () {
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
            $("#' . $idBlock . ' .add-sub-item").on("insert-top-table-custom-js", function() {
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

    public function getFieldId($config)
    {
        if (!empty($config['identifier'])) {
            return $config['identifier'];
        }

        $filteredDataSource = str_replace([':', ',', '.'], '-', $this->configuration->data_source_get);

        $filteredName = CommandData::fixedApiResult($config['data_field'], (!empty($this->_alias_framework) && $this->_alias_framework->enable));
        $filteredName = str_replace([':', ',', '.'], '-' , $filteredName);

        return $filteredDataSource . '--' . $filteredName;
    }

	public function generateJsTemplateFunctionField($jsCode)
    {
		$jsCode = base64_decode($jsCode);

		return /** @lang JavaScript */ '
        ' . $jsCode . '
        ';
	}
}