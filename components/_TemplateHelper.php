<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */
namespace app\components;

class _TemplateHelper
{
    public $layout_type = [];
    public $screen_step = [];
    public $search_configuration = [];
    public $template_layout = [];
    public $screen_extensions = [];
    public $search_custom_query = [];
    public $step_screen = [];
    public $alias_framework = [];

    public static function run($template) {
        $model = new static();
        $searchConfigDefault = [
            'func_inparam_configuration' => [],
            'pk_configuration' => null,
            'data_source_get' => '',
        ];
        $screenExtensionsDefault = [
            'add' => [],
            'delete' => [],
            'edit' => [],
            'inquire' => [],
            'execute' => [],
            'executeFunction' => [],
        ];
        $searchCustomQueryDefault = [
            'query_pk' => '',
            'query_params' => [],
        ];
        $stepScreenDefault = [
            'enable' => false,
            'icon' => null,
            'shape' => null,
            'text' => null,
            'lockable' => null,
            'release' => null,
            'group' => null,
        ];
        $aliasFrameworkDefault = [
            'enable' => false,
            'request_primary_table' => null,
            'data_source_update' => null,
            'data_source_delete' => null,
            'data_source_insert' => null,
        ];

        if (!empty($template)) {
            foreach ($template as $key => $value) {
                if (isset($model->$key) && !empty($value) && $value != 'null') $model->$key = $value;
            }
        }

        $model->setDefaultToTemplate();

        $model->template_layout = (object) $model->template_layout;
        $model->search_configuration = !empty($model->search_configuration) ? (object) array_merge($searchConfigDefault, $model->search_configuration) : null;
        $model->screen_extensions = !empty($model->screen_extensions) ? array_merge($screenExtensionsDefault, $model->screen_extensions) : null;
        $model->search_custom_query = !empty($model->search_custom_query) ? (object) array_merge($searchCustomQueryDefault, $model->search_custom_query) : null;
        $model->step_screen = !empty($model->step_screen) ? (object) array_merge($stepScreenDefault, $model->step_screen) : (object) $stepScreenDefault;
        $model->alias_framework = !empty($model->alias_framework) ? (object) array_merge($aliasFrameworkDefault, $model->alias_framework) : (object) $aliasFrameworkDefault;

        if (!$model->alias_framework->enable) {
            $model->alias_framework->data_source_update = null;
            $model->alias_framework->data_source_delete = null;
            $model->alias_framework->data_source_insert = null;

            if ($model->search_configuration) {
                $model->search_configuration->pk_configuration = null;
            }
        }

        return $model;
    }

    protected function setDefaultToTemplate() {
        $defaultValues = [
            'row_num' => '1',
            'col_num' => '1',
            'section_height' => null,
            'data_source_get' => '',
            'data_source_update' => null,
            'data_source_delete' => null,
            'data_source_create' => null,
            'layout_type' => '',
            'screen_step' => '',
            'layout_subtype' => '',
            'layout_label' => '*Default_label',
            'layout_fields' => false,
            'layout_table' => [
                'column_configuration' => [],
                'count' => '0',
                'show_type' => 'PAGING',
                'label_orientation' => 'TOP',
                'alias_framework' => [],
            ],
            'layout_configuration' => [
                'params' => [],
                'labels' => [],
                'labels_internationalization' => [],
                'format_type' => [],
                'params_type' => []
            ]
        ];

        foreach ($this->template_layout as $i => $layout) {
            foreach($defaultValues as $key => $value) {
                if (is_array($value) && !empty($layout[$key])) {
                    $this->template_layout[$i][$key] = (object) array_merge($value, $layout[$key]);
                } else if (!isset($layout[$key])) {
                    $this->template_layout[$i][$key] = $value;
                }
            }
            $this->template_layout[$i] = (object) $this->template_layout[$i];
        }
    }
}