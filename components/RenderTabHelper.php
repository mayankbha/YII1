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
    const SECTION_TYPE_CHART_SCATTER_WITH_LINEAR_REGRESSION = 'CHART-SCATTER-WITH-LINEAR-REGRESSION';
    const SECTION_TYPE_CHART_TIME_SERIES = 'CHART-TIME-SERIES';
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
        self::SECTION_TYPE_CHART_SCATTER_WITH_LINEAR_REGRESSION => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_TIME_SERIES => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_BAR_HORIZONTAL => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_BAR_VERTICAL => RenderChartWidget::class,
        self::SECTION_TYPE_CHART_DOUGHNUT => RenderChartWidget::class,
        self::SECTION_TYPE_DOCUMENT => RenderDocumentWidget::class,
    );

    public static $template_id;
    public static $active_passive;
    public static $search_screen;
    public static $login_screen;

    public static $user_details=null;
    public static $identify_verification=null;
    public static $account_protection=null;

    public static $account_status=null;

    public static $account_type=null;
    public static $tenant_code=null;
    public static $user_type=null;
    public static $default_group=null;
    public static $group_membership=null;
    public static $document_groups=null;
    public static $notification_user_type_email_template=null;
    public static $notification_password_type_email_template=null;

    public static $screen_self_registration_primary_table=null;

    public static $reset_password_template;

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
        'header_fields' => null,
        'tableSectionFilterArray' => null
    ];

    public function __construct(array $config = [])
    {
        //echo 'in __construct';

        //echo '<pre> $config :: '; print_r($config); die;

        self::$template_id = $config['id'];
        self::$active_passive = $config['active_passive'];
        self::$search_screen = $config['search_screen'];
        self::$login_screen = $config['login_screen'];

        self::$reset_password_template = null;

        $this->library = $config['screen_lib'];

        $this->template = Screen::decodeTemplate($config['screen_tab_template'], true);

        //echo '<pre> $this->template :: '; print_r($this->template); die;

        if($config['login_screen']) {
            if(!empty($this->template)) {
                foreach($this->template->template_layout as $temp_template) {
                    if($temp_template->row_num == 1 && $temp_template->col_num == 5) {
                        self::$user_details = 1;

                        if(isset($temp_template->identify_verification))
                            self::$identify_verification = $temp_template->identify_verification;

                        if(isset($temp_template->account_protection))
                            self::$account_protection = $temp_template->account_protection;

                        if(isset($temp_template->account_status))
                            self::$account_status = $temp_template->account_status;

                        if(isset($temp_template->self_registration_account_type))
                            self::$account_type = $temp_template->self_registration_account_type;

                        if(isset($temp_template->self_registration_tenant_code))
                            self::$tenant_code = $temp_template->self_registration_tenant_code;

                        if(isset($temp_template->self_registration_user_type))
                            self::$user_type = $temp_template->self_registration_user_type;

                        if(isset($temp_template->self_registration_default_group))
                            self::$default_group = $temp_template->self_registration_default_group;

                        if(isset($temp_template->self_registration_group_membership))
                            self::$group_membership = json_encode($temp_template->self_registration_group_membership);

                        if(isset($temp_template->self_registration_document_groups))
                            self::$document_groups = json_encode($temp_template->self_registration_document_groups);

                        if(isset($temp_template->self_registration_notification_user_type))
                            self::$notification_user_type_email_template = $temp_template->self_registration_notification_user_type;

                        if(isset($temp_template->self_registration_notification_password_type))
                            self::$notification_password_type_email_template = $temp_template->self_registration_notification_password_type;

                        if(isset($temp_template->screen_self_registration_primary_table))
                            self::$screen_self_registration_primary_table = $temp_template->screen_self_registration_primary_table;
                    }
                }
            }

            /*$this->account_status = $this->configuration->account_status;
            $this->identify_verification = $this->configuration->identify_verification;
            $this->account_protection = $this->configuration->account_protection;
            $this->screen_self_registration_primary_table = $this->configuration->screen_self_registration_primary_table;*/
        }

        //echo '<pre> $this->template :: '; print_r($this->template);

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

        if((isset($this->renderConfig['section_to_refresh']) && $this->renderConfig['section_to_refresh'] != '' && $this->renderConfig['section_to_refresh'] != null)) {
            $section_to_refresh_explode = explode('-', $this->renderConfig['section_to_refresh']);

            //echo '<pre> section_to_refresh_explode :: '; print_r($section_to_refresh_explode);
        }

        $id = $this->renderConfig['id'];
        $lib_name = 'CodiacSDK.Universal';

        for ($row = 1; $row <= $this->template->layout_type['row_count']; $row++) {
            $rowResult = null;

            for ($col = 1; $col <= $this->template->layout_type['col_count']; $col++) {
                $template_layout_section_depth_cnt = 1;
                $final_template = '';

                if($col == 1)
                    $temp_col = 1;
                else if($col == 2)
                    $temp_col = 8;

                foreach ($this->template->template_layout as $item) {
                    //echo '<pre> $item :: '; print_r($item);

                    if(isset($item->reset_password_notification_template))
                        self::$reset_password_template = $item->reset_password_notification_template;

                    if ($item->row_num == $row && $item->col_num == $temp_col) {
                        if(isset($item->template_layout_section_depth_cnt) && $item->template_layout_section_depth_cnt != '') {
                            if($temp_col == 1)
                                $template_layout_section_depth_cnt = $item->template_layout_section_depth_cnt;
                            else if($temp_col == 8 && $template_layout_section_depth_cnt == 1)
                                $template_layout_section_depth_cnt = $temp_col;
                            else if($temp_col == 8 && $template_layout_section_depth_cnt > 1)
                                $template_layout_section_depth_cnt = $temp_col + $item->template_layout_section_depth_cnt;
                        }
                    }

                    if(isset($template_layout_section_depth_cnt) && $template_layout_section_depth_cnt != '') {
                        for ($i = $temp_col; $i <= $template_layout_section_depth_cnt; $i++) {
                            if($item->row_num == $row && ($item->col_num == $i))
                                $final_template .= $this->renderSection($item, $this->template->layout_type['col_count']);
                        }
                    }
                }

                $rowResult .= $final_template;
            }

            $result .= Html::tag('div', $rowResult, ['class' => 'row common_section_depth_'.self::$template_id, 'data-template-layout-section-depth-cnt' => $template_layout_section_depth_cnt, 'data-template-layout-section-depth-row-num' => $row]);
        }

        return $result;
    }

    public function renderHeaderSection()
    {
        if (empty($this->template->layout_type['header'])) {
            return null;
        }

        foreach ($this->template->template_layout as $key => $sectionTemplate) {
            if ($sectionTemplate->row_num == 0 && $sectionTemplate->col_num == 0) {
                $sectionTemplate->active_passive = (bool) self::$active_passive;

                unset($this->template->template_layout->$key);
                return Html::tag('div', $this->renderSectionContent($sectionTemplate), [
                    'class' => 'alert alert-warning header-section' . (!empty(self::$active_passive) ? ' header-active-table' : ''),
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

        $section_depth_linked_field_value = '';

        if(isset($configuration->layout_section_depth_linked_field) && isset($configuration->layout_section_depth_active_value) && ($configuration->layout_section_depth_linked_field != '') && $configuration->layout_section_depth_active_value != '')
            $section_depth_linked_field_value = $configuration->layout_section_depth_active_value;

        //$template_layout_active_section_depth_row_num = $configuration->row_num;
        //$template_layout_active_section_depth_col_num = '';
        //$template_layout_section_depth_cnt = 1;

        //if(isset($configuration->template_layout_section_depth_cnt) && isset($configuration->template_layout_section_depth_cnt))
            //$template_layout_section_depth_cnt = $configuration->template_layout_section_depth_cnt;

        /*if($configuration->col_num == 1 || $configuration->col_num == 2) {
            if(isset($configuration->template_layout_active_section_depth_row_num) && isset($configuration->template_layout_active_section_depth_col_num)) {
                $template_layout_active_section_depth_row_num = $configuration->template_layout_active_section_depth_row_num;
                $template_layout_active_section_depth_col_num = $configuration->template_layout_active_section_depth_col_num;
            }
        }*/

        //echo '$template_layout_active_section_depth_row_num :: ' . $template_layout_active_section_depth_row_num;
        //echo '$template_layout_active_section_depth_col_num :: ' . $template_layout_active_section_depth_col_num;
        //die;

        //echo self::$login_screen;

         $option = ['style' => ''];

        if(!empty($configuration->layout_formatting)) {
            //echo '<pre> $configuration :: '; print_r($configuration->layout_formatting);

            //if(isset($configuration->layout_formatting['label_font_size']))
                //echo $configuration->layout_formatting['label_font_size'];

            foreach($configuration->layout_formatting as $formatting) {
                //echo '<pre> $formatting :: '; print_r($formatting);

                if ($formatting['name'] == 'label_text_align' && $formatting['value'] != '') {
                    switch ($formatting['name']) {
                        case 'left':
                            $justifyContent = 'flex-start';
                            break;
                        case 'right':
                            $justifyContent = 'flex-end';
                            break;
                        default:
                            $justifyContent = 'center';
                            break;
                    }

                    Html::addCssStyle($option, [
                        'text-align' => $formatting['value'],
                        'justify-content' => $justifyContent
                    ]);
                }

                if ($formatting['name'] == 'label_text_color' && $formatting['value'] != '') {
                    Html::addCssStyle($option, ['color' => $formatting['value'] . '!important']);
                }

                if ($formatting['name'] == 'label_bg_color' && $formatting['value'] != '') {
                    Html::addCssStyle($option, ['background-color' => $formatting['value']]);
                }

                if ($formatting['name'] == 'label_font_family' && $formatting['value'] != '') {
                    Html::addCssStyle($option, ['font-family' => $formatting['value']]);
                }

                if ($formatting['name'] == 'label_font_size' && $formatting['value'] != '') {
                    Html::addCssStyle($option, ['font-size' => $formatting['value'] . 'px']);
                }
            }
        }

        //echo '<pre> $option :: '; print_r($option);
        //echo '$option :: ' . $option['style'];

        if(self::$login_screen == 1) {
            $show_detach_icon = null;
        } else {
            $show_detach_icon = Html::tag('span', null, ['class' => "glyphicon glyphicon-new-window detach-icon", 'aria-hidden' => "true", 'title' => "Detach panel"]) . Html::tag('span', null, ['class' => "glyphicon glyphicon-remove attach-icon", 'aria-hidden' => "true", 'title' => "Attach panel"]);
        }

        return Html::tag('div',
            Html::tag('div',
                Html::tag('div',
                    Html::tag('h3', $label, ['class' => "panel-title", 'style' => $option['style']]) .
                    Html::tag('span', $show_detach_icon, ['class' => "panel-controls"]),
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
                'class' => 'col-sm-' . (12 / (int)$columnCount) . ' stats-section common_section_depth_class_'.self::$template_id,
                'id' => 'section_depth_'.self::$template_id.'_'.$configuration->row_num.'-'.$configuration->col_num,
                //'style' => 'display: none;',
                'data' => [
                    'row' => $configuration->row_num,
                    'col' => $configuration->col_num,
                    'template-id' => self::$template_id,
                    'template-layout-section-depth-linked-field-value' => $section_depth_linked_field_value,
                    'reset-password-template' => self::$reset_password_template
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

        //echo '<pre> $this->template :: '; print_r($this->template);

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