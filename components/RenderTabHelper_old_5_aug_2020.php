<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use Yii;
use app\models\Screen;
use yii\base\Component;
use yii\helpers\Html;

use app\models\CommandData;
use app\models\CustomLibs;

use app\components\BaseRenderWidget;

/**
 * Class RenderTabHelper
 * @property _TemplateHelper|string $template
 */
class RenderTabHelper extends Component
{
    const DEFAULT_SECTION_LABEL_NAME = '*Default_label';

    const SECTION_TYPE_GRID = 'TABLE';
    const SECTION_TYPE_LIST = 'LIST';
    const SECTION_TYPE_CHART_PIE = 'CHART-PIE';
    const SECTION_TYPE_CHART_LINE = 'CHART-LINE';
    const SECTION_TYPE_CHART_BAR_HORIZONTAL = 'CHART-BAR-HORIZONTAL';
    const SECTION_TYPE_CHART_BAR_VERTICAL = 'CHART-BAR-VERTICAL';
    const SECTION_TYPE_CHART_DOUGHNUT = 'CHART-DOUGHNUT';
    const SECTION_TYPE_DOCUMENT = 'DOCUMENT';

    const MODE_EDIT = 'edit';
    const MODE_INSERT = 'insert';
    const MODE_EXECUTE = 'execute';
    const MODE_COPY = 'copy';

    const CHART_LINE_COLOR = '#3e95cd';

    public $widgetClass = array(
        self::SECTION_TYPE_LIST => RenderListWidget::class,
        self::SECTION_TYPE_GRID => RenderGridWidget::class,
        self::SECTION_TYPE_CHART_PIE => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_LINE => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_BAR_HORIZONTAL => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_BAR_VERTICAL => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_DOUGHNUT => RenderChartWidget::class,
        self::SECTION_TYPE_DOCUMENT => RenderDocumentWidget::class,
    );

    public static $template_id;

    private $library;
    private $template;

    private $renderConfig = [
        'mode' => '',
        'id' => [],
        'cache' => false,
        'lastFoundData' => null,
        'section_to_refresh' => null,
        'section_depth_value' => null,
        'button_action' => null,
        'header_fields' => null
    ];

    public function __construct(array $config = [])
    {
        //echo 'in __construct';

        //echo '<pre> $config :: '; print_r($config);

        self::$template_id = $config['id'];

        $this->library = $config['screen_lib'];

        $this->template = Screen::decodeTemplate($config['screen_tab_template'], true);

        if (empty($this->template->layout_type)) {
            $this->template->layout_type = [
                'header' => true,
                'row_count' => 0,
                'col_count' => 0
            ];
        } else {
            $this->template->layout_type = Screen::$types[(int)$this->template->layout_type];
        }

        parent::__construct([]);
    }

    public function render(array $config = [])
    {
        //echo 'in render';

        $this->renderConfig = array_merge($this->renderConfig, $config);

        $result = $this->renderHeaderSection();

        //echo '<pre> $section_insert_data :: '; print_r($this->renderConfig);

        //echo '<pre> $this->renderConfig :: '; echo print_r($this->renderConfig);

        //echo '<pre> $this->template :: '; print_r($this->template);

        //Default setup without multi section tab
        /*for ($row = 1; $row <= $this->template->layout_type['row_count']; $row++) {
            $rowResult = null;
            for ($col = 1; $col <= $this->template->layout_type['col_count']; $col++) {
                foreach ($this->template->template_layout as $item) {
                    if ($item->row_num == $row && $item->col_num == $col) {
                        $rowResult .= $this->renderSection($item, $this->template->layout_type['col_count']);
                    }
                }
            }
            $result .= Html::tag('div', $rowResult, ['class' => 'row']);
        }*/

        if((isset($this->renderConfig['section_to_refresh']) && $this->renderConfig['section_to_refresh'] != '' && $this->renderConfig['section_to_refresh'] != null)) {
            $section_to_refresh_explode = explode('-', $this->renderConfig['section_to_refresh']);

            //echo '<pre> section_to_refresh_explode :: '; print_r($section_to_refresh_explode);
        }

        $id = $this->renderConfig['id'];
        $lib_name = 'CodiacSDK.Universal';

        for ($row = 1; $row <= $this->template->layout_type['row_count']; $row++) {
            $rowResult = null;

            for ($col = 1; $col <= $this->template->layout_type['col_count']; $col++) {
                $template_layout_section_depth = false;
                $final_template = '';

                if($col == 1)
                    $temp_col = 1;
                else if($col == 2)
                    $temp_col = 8;

                foreach ($this->template->template_layout as $item) {
                    if ($item->row_num == $row && $item->col_num == $temp_col) {
                        if(isset($item->template_layout_section_depth_cnt) && $item->template_layout_section_depth_cnt != '') {
                           $template_layout_section_depth_cnt = $item->template_layout_section_depth_cnt;
                           $template_layout_section_depth = true;

                            $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);
                        }
                    }

                    if(isset($template_layout_section_depth_cnt) && $template_layout_section_depth_cnt != '') {
                        for ($i = $temp_col; $i <= $template_layout_section_depth_cnt; $i++) {
                            if($item->row_num == $row && ($item->col_num == $temp_col))
                                $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);

                            if(isset($section_to_refresh_explode) && !empty($section_to_refresh_explode)) {
                                if($item->row_num == $row && $item->col_num == $i) {
                                    /*echo '$row :: '.$row.'<br>';
                                    echo '$item->row_num :: '.$item->row_num.'<br>';
                                    echo '$col :: '.$col.'<br>';
                                    echo '$item->col_num :: '.$item->col_num.'<br>';
                                    echo '$temp_col :: '.$temp_col.'<br>';
                                    echo '$i :: '.$i.'<br>';

                                    if(isset($section_to_refresh_explode) && !empty($section_to_refresh_explode)) {
                                        echo '$section_to_refresh_explode_row[0] :: '.$section_to_refresh_explode[0].'<br>';
                                        echo '$section_to_refresh_explode_col[1] :: '.$section_to_refresh_explode[1].'<br>';
                                    }

                                    echo '$item->layout_section_depth_linked_field :: '.$item->layout_section_depth_linked_field.'<br>';
                                    echo '$item->layout_section_depth_active_value :: '.$item->layout_section_depth_active_value.'<br>';
                                    echo '$this->renderConfig[section_depth_value] :: '.$this->renderConfig['section_depth_value'].'<br>';*/

                                    $func_name = $item->data_source_get;
                                    $layout_type = $item->layout_type;

                                    if($layout_type == 'TABLE' || $layout_type == 'CHART')
                                        $isGetParent = true;
                                    else
                                        $isGetParent = false;

                                    $subDataPK = [];
                                    $relatedField = CustomLibs::getRelated($lib_name, $func_name);

                                    if ($isGetParent && !empty($id)) {
                                        if (CustomLibs::getTableName($lib_name, $func_name)) {
                                            $subDataPK = CustomLibs::getPK($lib_name, $func_name);
                                        } else if (!empty($this->renderConfig->pk_configuration)) {
                                            $fieldParams = CommandData::getFieldListForQuery($id);
                                        }
                                    }

                                    if (empty($fieldParams)) {
                                        $fieldParams = CommandData::getFieldListForQuery($id, $relatedField);
                                    }

                                    if ($isGetParent) {
                                        $additionalParam = CommandData::getGridFieldOutListForQuery($item, $subDataPK);
                                    } else {
                                        $additionalParam = CommandData::getFieldOutListForQuery($lib_name, $func_name);
                                    }

                                    //echo '<pre> $additionalParam :: '; print_r($additionalParam);

                                    if($item->row_num == $section_to_refresh_explode[0] && $temp_col == $section_to_refresh_explode[1]) {
                                        //echo 'in if now';

                                        foreach($additionalParam['field_out_list'] as $additional_param) {
                                            if($additional_param == $item->layout_section_depth_linked_field && $item->layout_section_depth_active_value == $this->renderConfig['section_depth_value'])
                                                $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);
                                        }
                                    }
                                }
                            } else {
                                if($item->row_num == $row && $item->col_num == $i) {
                                    /*echo '$row :: '.$row.'<br>';
                                    echo '$item->row_num :: '.$item->row_num.'<br>';
                                    echo '$col :: '.$col.'<br>';
                                    echo '$item->col_num :: '.$item->col_num.'<br>';
                                    echo '$temp_col :: '.$temp_col.'<br>';
                                    echo '$i :: '.$i.'<br>';

                                    if(isset($section_to_refresh_explode) && !empty($section_to_refresh_explode)) {
                                        echo '$section_to_refresh_explode_row[0] :: '.$section_to_refresh_explode[0].'<br>';
                                        echo '$section_to_refresh_explode_col[1] :: '.$section_to_refresh_explode[1].'<br>';
                                    }

                                    echo '$item->layout_section_depth_linked_field :: '.$item->layout_section_depth_linked_field.'<br>';
                                    echo '$item->layout_section_depth_active_value :: '.$item->layout_section_depth_active_value.'<br>';
                                    echo '$this->renderConfig[section_depth_value] :: '.$this->renderConfig['section_depth_value'].'<br>';*/

                                    $func_name = $item->data_source_get;
                                    $layout_type = $item->layout_type;

                                    if($layout_type == 'TABLE' || $layout_type == 'CHART')
                                        $isGetParent = true;
                                    else
                                        $isGetParent = false;

                                    $subDataPK = [];
                                    $relatedField = CustomLibs::getRelated($lib_name, $func_name);

                                    if ($isGetParent && !empty($id)) {
                                        if (CustomLibs::getTableName($lib_name, $func_name)) {
                                            $subDataPK = CustomLibs::getPK($lib_name, $func_name);
                                        } else if (!empty($this->renderConfig->pk_configuration)) {
                                            $fieldParams = CommandData::getFieldListForQuery($id);
                                        }
                                    }

                                    if (empty($fieldParams)) {
                                        $fieldParams = CommandData::getFieldListForQuery($id, $relatedField);
                                    }

                                    $postData = [
                                        'lib_name' => $lib_name,
                                        'func_name' => $func_name,
                                        'alias_framework_info' => $this->template->alias_framework,
                                        'search_function_info' => [
                                            'config' => $this->template->search_configuration,
                                            'data' => $this->renderConfig['lastFoundData']
                                        ]
                                    ];

                                    if ($isGetParent) {
                                        $additionalParam = CommandData::getGridFieldOutListForQuery($item, $subDataPK);
                                    } else {
                                        $additionalParam = CommandData::getFieldOutListForQuery($lib_name, $func_name);
                                    }

                                    $data = CommandData::getData($fieldParams, $postData, $additionalParam);

                                    if ($isGetParent) {
                                        if(!empty($data) && $data != null) {
                                            $data = $data->list;

                                            foreach ($data as $key => $row1) {
                                                $newPK = [];
                                                foreach($subDataPK as $item) {
                                                    if (!empty($row1[$item])) $newPK[] = $row1[$item];
                                                }
                                                $newPK = implode(';', $newPK);
                                                $data[$key]['pk'] = $newPK;
                                            }
                                        }
                                    } else {
                                        if(!empty($data) && $data != null) {
                                            $data = $data->list[0];
                                        }
                                    }

                                    //echo '<pre> $data :: '; print_r($data);
                                    //echo '<pre> $additionalParam :: '; print_r($additionalParam);

                                    //echo 'layout_section_depth_linked_field :: '.$item->layout_section_depth_linked_field.'<br>';
                                    //echo 'layout_section_depth_active_value :: '.$item->layout_section_depth_active_value.'<br>';

                                    if(!empty($data)) {
                                        if(isset($item->layout_section_depth_linked_field) && array_key_exists($item->layout_section_depth_linked_field, $data) && $item->layout_section_depth_active_value == $data[$item->layout_section_depth_linked_field])
                                                $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);
                                    }
                                }
                            }
                        }
                    } else if(!$template_layout_section_depth) {
                       if ($item->row_num == $row && $item->col_num == $col) {
                            $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);
                        }
                    }

                    /*if(isset($template_layout_section_depth_cnt) && $template_layout_section_depth_cnt != '') {
                        for ($i = $temp_col; $i <= $template_layout_section_depth_cnt; $i++) { echo 'col :: '.$i;
                            if($item->row_num == $row && ($item->col_num == 1 || $item->col_num == 8))
                                $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);

                            if((isset($item->template_layout_active_section_depth_row_num) && isset($item->template_layout_active_section_depth_col_num)) && ($item->template_layout_active_section_depth_row_num != '' && $item->template_layout_active_section_depth_col_num != '') && ($item->template_layout_active_section_depth_row_num == $row && $item->template_layout_active_section_depth_col_num == $i)) {
                                    echo $item->layout_section_depth_linked_field.' :: '.$item->layout_section_depth_active_value;

                                    $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);
                            }
                        }
                    } else {
                        if ($item->row_num == $row && $item->col_num == $col)
                            $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);
                    }*/
                }

                /*foreach ($this->template->template_layout as $item) {
                    if ($item->row_num == $row && ($item->col_num == 1 || $item->col_num == 8)) {
                        $temp_col = $item->col_num;

                        if(isset($item->template_layout_section_depth_cnt))
                            $template_layout_section_depth_cnt = $item->template_layout_section_depth_cnt;
                    }
                }*/

                /*if($col == 1) {
                    $temp_col = 1;
                } else if($col == 2) {
                    $temp_col = 8;

                    $template_layout_section_depth_cnt = (7 + $template_layout_section_depth_cnt);
                }*/

                /*if($temp_col == 8)
                    $template_layout_section_depth_cnt = (7 + $template_layout_section_depth_cnt);

                for ($i = $temp_col; $i <= $template_layout_section_depth_cnt; $i++) {
                    foreach ($this->template->template_layout as $item) {
                        if((isset($item->template_layout_active_section_depth_row_num) && isset($item->template_layout_active_section_depth_col_num)) && ($item->template_layout_active_section_depth_row_num != '' && $item->template_layout_active_section_depth_col_num != '')) {
                                if($item->row_num == $row && $item->template_layout_active_section_depth_col_num == $temp_col) {
                                    $rowResult .= $this->renderSection($item, $this->template->layout_type['col_count']);
                                }
                        }
                    }
                }*/

                $rowResult .= $final_template;
            }

            $result .= Html::tag('div', $rowResult, ['class' => 'row']);
        }

        //First setup for new screen as well as old screen i.e. for single tab section as well as multi tab section
        /*for ($row = 1; $row <= $this->template->layout_type['row_count']; $row++) {
            $rowResult = null;

            for ($col = 1; $col <= $this->template->layout_type['col_count']; $col++) {
                if($col == 1)
                    $temp_col = 1;
                else if($col == 2)
                    $temp_col = 8;

                $template_layout_section_depth_cnt = $this->template->layout_type['col_count'];

                foreach ($this->template->template_layout as $item) {
                    //echo '<pre>'; print_r($item); die;

                    if ($item->row_num == $row && $item->col_num == $temp_col)
                        if(isset($item->template_layout_section_depth_cnt))
                            $template_layout_section_depth_cnt = $item->template_layout_section_depth_cnt;
                }

               // die;

                //echo $template_layout_section_depth_cnt;

                for ($temp_col = 1; $temp_col <= $template_layout_section_depth_cnt; $temp_col++) {
                    foreach ($this->template->template_layout as $item) {
                        if($item->row_num == $row && ($item->col_num == 1 || $item->col_num == 8))
                            $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);

                        $func_name = $item->data_source_get;
                        $layout_type = $item->layout_type;

                        if($layout_type == 'TABLE' || $layout_type == 'CHART')
                            $isGetParent = true;
                        else
                            $isGetParent = false;

                        $subDataPK = [];
                        $relatedField = CustomLibs::getRelated($lib_name, $func_name);

                        if ($isGetParent && !empty($id)) {
                            if (CustomLibs::getTableName($lib_name, $func_name)) {
                                $subDataPK = CustomLibs::getPK($lib_name, $func_name);
                            } else if (!empty($this->renderConfig->pk_configuration)) {
                                $fieldParams = CommandData::getFieldListForQuery($id);
                            }
                        }

                        if (empty($fieldParams)) {
                            $fieldParams = CommandData::getFieldListForQuery($id, $relatedField);
                        }

                        $postData = [
                            'lib_name' => $lib_name,
                            'func_name' => $func_name,
                            'alias_framework_info' => $this->template->alias_framework,
                            'search_function_info' => [
                                'config' => $this->template->search_configuration,
                                'data' => $this->renderConfig['lastFoundData']
                            ]
                        ];

                        //echo '<pre>'; print_r($postData);

                        if ($isGetParent) {
                            $additionalParam = CommandData::getGridFieldOutListForQuery($item, $subDataPK);
                        } else {
                            $additionalParam = CommandData::getFieldOutListForQuery($lib_name, $func_name);
                        }

                        $data = CommandData::getData($fieldParams, $postData, $additionalParam);

                        if ($isGetParent) {
                            $data = $data->list;
                            foreach ($data as $key => $row1) {
                                $newPK = [];
                                foreach($subDataPK as $item) {
                                    if (!empty($row1[$item])) $newPK[] = $row1[$item];
                                }
                                $newPK = implode(';', $newPK);
                                $data[$key]['pk'] = $newPK;
                            }
                        } else {
                            $data = $data->list[0];
                        }

                        //echo '<pre> data :: '; print_r($data);

                        if ($item->row_num == $row && $item->col_num == $temp_col && !empty($data)) {
                            if((isset($item->layout_section_depth_linked_field) && isset($item->layout_section_depth_active_value)) && ($item->layout_section_depth_linked_field != '' && $item->layout_section_depth_active_value != '') && (array_key_exists($item->layout_section_depth_linked_field, $data) && ($item->layout_section_depth_active_value == $data[$item->layout_section_depth_linked_field]))) {
                                    $final_template = $this->renderSection($item, $this->template->layout_type['col_count']);
                            }
                        }
                    }
                }

                $rowResult .= $final_template;
            }

            $result .= Html::tag('div', $rowResult, ['class' => 'row']);
        }*/

        return $result;
    }

    public function renderHeaderSection()
    {
        if (empty($this->template->layout_type['header'])) {
            return null;
        }

        foreach ($this->template->template_layout as $key => $sectionTemplate) {
            if ($sectionTemplate->row_num == 0 && $sectionTemplate->col_num == 0) {
                unset($this->template->template_layout->$key);
                return Html::tag('div', $this->renderSectionContent($sectionTemplate), [
                    'class' => 'alert alert-warning header-section',
                    'data-source-get' => $sectionTemplate->data_source_get,
                    'data-type' => $sectionTemplate->layout_type,
                    'style' => [
                        'position' => 'relative',
                        'height' => ($sectionTemplate->section_height) ? $sectionTemplate->section_height . 'px' : 'auto'
                    ]
                ]);
            }
        }

        return null;
    }

    /**
     * @param $configuration - Template of section settings
     * @param integer $columnCount - Column count
     * @return string
     */
    private function renderSection($configuration, $columnCount)
    {
        if (!empty($configuration->layout_label_internationalization[Yii::$app->language])) {
            $label = $configuration->layout_label_internationalization[Yii::$app->language];
        } else if (!empty($configuration->layout_label)) {
            $label = $configuration->layout_label;
        }

        return Html::tag('div',
            Html::tag('div',
                Html::tag('div',
                    Html::tag('h3', $label, ['class' => "panel-title"]) .
                    Html::tag('span',
                        Html::tag('span', null, ['class' => "glyphicon glyphicon-new-window detach-icon", 'aria-hidden' => "true", 'title' => "Detach panel"]) .
                        Html::tag('span', null, ['class' => "glyphicon glyphicon-remove attach-icon", 'aria-hidden' => "true", 'title' => "Attach panel"]),
                        ['class' => "panel-controls"]
                    ),
                    ['class' => "panel-heading"]
                ) .
                Html::tag('div',
                    $this->renderSectionContent($configuration),
                    ['class' => "panel-body"]
                ),
                [
                    'class' => "panel panel-default panel-window",
                    'style' => [
                        'width' => '100%',
                        'height' => ($configuration->section_height) ? $configuration->section_height . 'px' : 'auto'
                    ],
                    'data' => [
                        'type' => $configuration->layout_type,
                        'source-get' => $configuration->data_source_get,
                        'source-delete' => (empty($this->template->alias_framework->data_source_delete)) ? null : $this->template->alias_framework->data_source_delete
                    ]
                ]
            ),
            [
                'class' => 'col-sm-' . (12 / (int)$columnCount) . ' stats-section',
                'data' => [
                    'row' => $configuration->row_num,
                    'col' => $configuration->col_num
                ]
            ]
        );
    }

    /**
     * Getting section HTML code
     * @param $configuration
     * @return string
     */
    private function renderSectionContent($configuration)
    {
        $widget = $this->getWidget($configuration->layout_type);

        if (!empty($widget)) {
            $_alias_framework = $this->template->alias_framework;
            $lib_name = $this->library;

            if (isset($this->template->search_custom_query)) {
                $_search_configuration = $this->template->search_custom_query;
            } else {
                $_search_configuration = $this->template->search_configuration;
            }

            $localConfig = compact('lib_name', 'configuration', '_search_configuration', '_alias_framework');
            $widgetConfig = array_merge($localConfig, $this->renderConfig);
            $this->renderConfig['cache'] = false;

            return $widget::widget($widgetConfig);
        }
        return '';
    }

    public function getWidget($layoutType)
    {
        return (!empty($layoutType) && array_key_exists($layoutType, $this->widgetClass)) ? $this->widgetClass[$layoutType] : null;
    }

    /**
     * Generate random color rgba
     * 
     * @param array $userColors
     * @param int $index
     *
     * @return string
     */
    public static function getColor($userColors = [], $index = 0)
    {
        $colors = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#6600CC',
            '#006633',
            '#66B2FF',
            '#CCFFE5'

        ];
        $userColors += $colors;
        return $userColors[$index] ? $userColors[$index] : $userColors[0];
    }
}