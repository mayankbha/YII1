<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use app\models\Report;
use Yii;
use app\models\CommandData;
use app\models\DocumentGroup;
use app\models\FileModel;
use app\models\GetListList;
use kartik\date\DatePicker;
use kartik\datetime\DateTimePicker;
use kartik\typeahead\Typeahead;
use kato\DropZone;
use yii\base\Widget;
use yii\bootstrap\Dropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

use app\components\RenderTabHelper;

/**
 * Class _FieldsHelper
 * @package app\components
 * @property _FormattedHelper $Formatted
 */
class _FieldsHelper extends Widget
{
    const TYPE_SELECT = 'List';
    const TYPE_SELECT_WITH_EXTENSION_FUNCTION = 'List With Extension Function';
    const TYPE_MULTI_SELECT = 'Multi-select list';
    const TYPE_CHECKBOX = 'Checkbox';
    const TYPE_NUMERIC = 'Numeric';
    const TYPE_TEXT = 'Text';
    const TYPE_TEXTAREA = 'Textarea';
    const TYPE_RADIO = 'Radio';
    const TYPE_DOCUMENT = 'Document';
    const TYPE_INLINE_SEARCH = 'Inline search';
    const TYPE_ALERT = 'Alert';
    const TYPE_LINK = 'Link';
    const TYPE_LABEL = 'Label';
    const TYPE_BUTTON = 'Button';
    const TYPE_IMAGE = 'Image';
    const TYPE_IMAGE_DYNAMIC = 'DImage';
    const TYPE_DATALIST = 'Datalist';
    const TYPE_DATALIST_RELATION = 'Relation datalist';
    const TYPE_WITH_DEPENDENT_FIELD = 'With dependent field';

    const TYPE_FORMAT_LINK_LOCAL = 'Local';

    const TYPE_ALERT_SUCCESS = 'Success';
    const TYPE_ALERT_WARNING = 'Warning';
    const TYPE_ALERT_ERROR = 'Error';

    //const MULTI_SELECT_DELIMITER = ',';
    const MULTI_SELECT_DELIMITER = ';';

    const PROPERTY_READ_ONLY = 'R';
    const PROPERTY_READ_ONLY_EXCEPT_INSERT = 'R_E_I';
    const PROPERTY_TRUE = 'Y';
    const PROPERTY_FALSE = 'N';

    const FIELD_WIDTH_TYPE_LENGTH = 'L';
    const FIELD_WIDTH_TYPE_VALUE = 'V';

    public $dataField;
    public $dataAccess;
    public $mode;
    public $libName;
    public $value;
    public $config;
    public $dataId;
    public $isGridField = false;
    public $isKeyField = false;
    public $isAlwaysShowFieldRule = false;
    public $aliasFrameworkPKParts = false;
    public $internationalization = [];

	public $layout_table;
	public $readonly;
	
    public $layout_type;
    public $cnt;
    public $tmpData;
    public $layout_config;

    public $data_source_get;
    public $data_source_update;
    public $data_source_delete;
    public $data_source_create;

    protected $Formatted;
    protected $configDefault = [
        'field_type' => self::TYPE_TEXT,
        'copyable_field' => self::PROPERTY_FALSE,
        'format_type' => _FormattedHelper::TEXT_FORMAT,
        'field_length' => false,
        'list_name' => false,
        'key_field' => false,
        'field_link_menu' => false,
        'field_group_screen_link' => false,
        'field_screen_link' => false,
        'field_settings_link' => false,
        'field_pass_through_link' => false,
        'type-link' => false,
        'edit_type' => false,
        'edit_type_except_insert' => false,
        'datalist_relation_default' => [],
        'datalist_relation_id' => [],
        'custom_query_pk' => false,
        'link_type' => self::PROPERTY_FALSE
    ];

    public function run()
    {
        $this->Formatted = new _FormattedHelper();
        if (!empty($this->config['param_type'])) {
            $this->config['field_type'] = $this->config['param_type'];
        }

        $this->isKeyField = isset($this->config['key_field']) && ($this->config['key_field'] == self::PROPERTY_TRUE);
        $this->isAlwaysShowFieldRule = isset($this->config['always_show_field_border']) && $this->config['always_show_field_border'] == self::PROPERTY_TRUE;
        $this->config = array_merge($this->configDefault, $this->config);

        $dataAccess = $this->accessFilter();

		//echo 'dataAccess :: ' . $dataAccess;
		//echo '$this->isField() :: ' . $this->isField();

        switch($dataAccess) {
            case BaseRenderWidget::FIELD_ACCESS_NONE:
                return Html::tag('i', '(access denied)', ['style' => ['color' => 'red']]);
                break;
            case BaseRenderWidget::FIELD_ACCESS_READ:
                return $this->getFormattedData();
                break;
            default:
                return $this->isField() ? $this->getField() : $this->getFormattedData();
        }
    }

    private function accessFilter() {
        $accessRights = (!empty($this->dataAccess[$this->dataField])) ? $this->dataAccess[$this->dataField] : BaseRenderWidget::FIELD_ACCESS_FULL;
        if (empty(Yii::$app->getUser()->getIdentity()->group_area)) {
            return $accessRights;
        }

        $groupAreas = explode(';', Yii::$app->getUser()->getIdentity()->group_area);
        if (!empty($this->config['access_view'])) {
            $accessRights = BaseRenderWidget::FIELD_ACCESS_NONE;
            foreach($groupAreas as $group) {
                if (in_array($group, $this->config['access_view'])) {
                    $accessRights = BaseRenderWidget::FIELD_ACCESS_READ;
                    break;
                }
            }
        }

        $isGetFieldMode = in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]);
        $currentAccessCanUpdate = $accessRights != BaseRenderWidget::FIELD_ACCESS_NONE;

        if (!empty($this->config['access_update']) && $isGetFieldMode && $currentAccessCanUpdate) {
            $accessRights = BaseRenderWidget::FIELD_ACCESS_READ;
            foreach($groupAreas as $group) {
                if (in_array($group, $this->config['access_update'])) {
                    $accessRights = BaseRenderWidget::FIELD_ACCESS_FULL;
                    break;
                }
            }
        }

        return $accessRights;
    }

    /**
     * @return bool
     */
    private function isField()
    {
        $basicRule = in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]);
        $isAlwaysShowFieldRule = $this->isAlwaysShowFieldRule && $this->config['field_type'] !== self::TYPE_DOCUMENT;
        $isKeyFieldRule = $this->isKeyField && $this->config['field_type'] !== self::TYPE_DOCUMENT;

        return $basicRule || $isKeyFieldRule || $isAlwaysShowFieldRule;
    }

    /**
     * @return bool
     */
    private function isDisabledField()
    {
        $isReadOnlyFieldConfig = $this->config['edit_type'] == self::PROPERTY_READ_ONLY;
        $isWithDependField = $this->config['field_type'] == self::TYPE_WITH_DEPENDENT_FIELD;

        $isEditMode = $this->mode === RenderTabHelper::MODE_EDIT;
        $basicRule = in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]);

        return  (!$this->isKeyField && ($isReadOnlyFieldConfig || ($this->isAlwaysShowFieldRule && !$basicRule))) ||
                ($this->isKeyField && ($isEditMode || $isReadOnlyFieldConfig)) ||
                $isWithDependField;
    }

    /**
     * @return string
     */
    private function getFieldName()
    {
		if(isset($this->dataField) && $this->dataField !== '')
			return $this->dataField;
		else if((isset($this->dataField) && $this->dataField == '') && isset($this->config['field_identifier']) && $this->config['field_identifier'] != '')
			return $this->config['field_identifier'].'-'.$this->dataField;
		else if(isset($this->config['field_label']) && $this->config['field_label'] != '')
			if(isset($this->config['field_identifier']) && $this->config['field_identifier'] != '')
				return $this->config['field_identifier'].'-'.$this->config['field_label'];
			else
				return $this->config['field_label'];
		else
			return 'test';
    }

    private function getFieldFuncData()
    {
        $data = [];
        if ($this->data_source_get) {
            $data['get-func'] = $this->data_source_get;
        }

        if ($this->data_source_update) {
            $data['update-func'] = $this->data_source_update;
        }

        if ($this->data_source_delete) {
            $data['delete-func'] = $this->data_source_delete;
        }

        if ($this->data_source_create) {
            $data['create-func'] = $this->data_source_create;
        }

        if ($this->dataId) {
            $data['sub-id'] = $this->dataId;
        }

        if ($this->aliasFrameworkPKParts) {
            $data['af-pk-part'] = $this->aliasFrameworkPKParts;
        }

        return ['data' => $data];
    }

    private function getFieldID()
    {
        $filteredDataSource = str_replace([':', ',', '.'], '-', $this->data_source_get);
        $dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());

        return $filteredDataSource . '--' . $dataField;
    }

    /**
     * @return array
     */
    private function getBasicStyles()
    {
        //$option = [];
        $option = ['style' => ['max-width' => '100%']];

        $textDecoration = '';
        $textDecoration .= (!empty($this->config['field_strike'])) ? 'line-through' : '';
        $textDecoration .= (!empty($this->config['field_underline'])) ? ' underline' : '';

        if (!empty($textDecoration)) {
            Html::addCssStyle($option, ['text-decoration' => $textDecoration]);
        }
        if (!empty($this->config['field_bold'])) {
            Html::addCssStyle($option, ['font-weight' => 'bold']);
        }
        if (!empty($this->config['field_italic'])) {
            Html::addCssStyle($option, ['font-style' => 'italic']);
        }

        if (!empty($this->config['field_text_color'])) {
            Html::addCssStyle($option, ['color' => $this->config['field_text_color']]);
        }
        if (!empty($this->config['field_bg_color'])) {
            Html::addCssStyle($option, ['background-color' => $this->config['field_bg_color']]);
        }

        if (!empty($this->config['field_font_family'])) {
            Html::addCssStyle($option, ['font-family' => $this->config['field_font_family']]);
        }
        if (!empty($this->config['field_font_size'])) {
            Html::addCssStyle($option, ['font-size' => $this->config['field_font_size'] . 'px']);
        }

        Html::addCssStyle($option, ['display' => 'inline-block', 'vertical-align' => 'top']);

        return $option;
    }

    /**
     * @return array
     */
    private function getFieldOptions()
    {
		$readonly = false;

		$explode_dataField = explode('.', $this->dataField);

		if($this->mode == RenderTabHelper::MODE_EDIT) {
			$isReadOnlyExceptInsertFieldConfig = $this->config['edit_type_except_insert'] == self::PROPERTY_READ_ONLY_EXCEPT_INSERT;

			if($isReadOnlyExceptInsertFieldConfig)
				$readonly = true;
		}

		if($this->isDisabledField())
			$readonly = $this->isDisabledField();

		if($explode_dataField[0] == 'Custom' || $explode_dataField[0] == 'Multi')
			$readonly = true;

		if($this->layout_table == 'TABLE' && $this->readonly)
			$readonly = true;

        $options = $this->getBasicStyles();
        $options += [
            'class' => 'form-control',
            'readonly' => $readonly,
            'maxlength' => isset($this->config['field_length']) ? $this->config['field_length'] : null,
            'id' => (is_null($this->dataId)) ? $this->getFieldID() : null,
            'required' => isset($this->config['required']) && ($this->config['required'] == self::PROPERTY_TRUE)
        ];

        $options['class'] .= ($this->isGridField) ? ' form-control-grid' : '';
        $options['class'] .= (isset($this->config['key_field']) && ($this->config['key_field'] == self::PROPERTY_TRUE)) ? ' form-control-key' : '';

        $options = array_merge($options, $this->getFieldFuncData());

        if (!empty($this->config['field_width_type'])) {
            if ($this->config['field_width_type'] === self::FIELD_WIDTH_TYPE_LENGTH) {
                $options['size'] = $this->config['field_length'];
                Html::addCssStyle($options, ['width' => 'auto']);
            } elseif ($this->config['field_width_type'] === self::FIELD_WIDTH_TYPE_VALUE && !empty($this->config['field_width_value'])) {
                Html::addCssStyle($options, ['width' => $this->config['field_width_value']]);
            }
        }

        if (!empty($this->config['notification_pk'])) {
            foreach ($this->config['notification_pk'] as $pk) {
                if (isset($this->config['notification_action'][$pk]) && ($this->config['notification_action'][$pk] == $this->mode)) {
                    $options['data']['notifications'][] = $pk;
                    if (isset($this->config['notification_recipient_id'][$pk]) && $options['id']) {
                        $this->view->registerJs("
                            $('#{$this->config['notification_recipient_id'][$pk]}').change(function () {
                                var recipient = $('#" . $options['id'] . "').data('notificationRecipient') || {};
                                recipient['$pk'] = $(this).val();
                                $('#" . $options['id'] . "').data('notificationRecipient', recipient);
                            });
                            $('#{$this->config['notification_recipient_id'][$pk]}').trigger('change');
                        ");
                    }

                    if (empty($this->config['notification_type'][$pk])) {
                        continue;
                    }

                    foreach ($this->config['notification_type'][$pk] as $param => $type) {
                        switch($type) {
                            case 'field_name':
                                $value = $this->getFieldName();
                                break;
                            case 'library':
                                $value = $this->libName;
                                break;
                            case 'new_value':
                                $value = 'new_value';
                                break;
                            default:
                                $value = isset($this->config['notification_param'][$pk][$param]) ? $this->config['notification_param'][$pk][$param] : '';
                        }

                        $options['data']['notification-params'][$pk][$param] = $value;
                    }
                }
            }
        }

        if ($this->config['field_type'] == self::TYPE_WITH_DEPENDENT_FIELD) {
            $options['data']['dependent-field'] = $this->addDependentFieldOptions();
        }

        return $options;
    }

    private function addDependentFieldOptions()
    {
        $dependentFieldOptions = [];
        if (!empty($this->config['depend_field_leadtime'])) {
            $dependentFieldOptions['timestamp'] = $this->config['depend_field_leadtime'];
        }

        if (!empty($this->config['depend_field_id'])) {
            $dependentFieldOptions['id'] = $this->config['depend_field_id'];
        }

        if (!empty($this->config['depend_field_type'])) {
            $dependentFieldOptions['type'] = $this->config['depend_field_type'];
        }

        if (!empty($this->config['depend_field_pivot_id'])) {
            $dependentFieldOptions['pivot-id'] = $this->config['depend_field_pivot_id'];
        }

        if (!empty($this->config['depend_field_pivot_value'])) {
            $dependentFieldOptions['pivot-value'] = $this->config['depend_field_pivot_value'];
        }

        if (!empty($this->config['depend_field_pivot_leadtime'])) {
            $dependentFieldOptions['pivot-timestamp'] = $this->config['depend_field_pivot_leadtime'];
        }

        return $dependentFieldOptions;
    }

    /**
     * @param array $options
     * @param string $type
     *
     * @return string
     */
    private function getSelectField(array $options, $type)
	{
		//echo 'in getSelectField';
		//echo '<pre>'; print_r($this->config);

		if (empty($this->config['num_rows'])) {
			if ($type === self::TYPE_MULTI_SELECT) {
				$options['size'] = 1;
			} else {
				$options['size'] = 1;
			}
		} else {
			$options['size'] = $this->config['num_rows'];
		}

		if(isset($this->config['multi-select-type']) && $this->config['multi-select-type'] == self::PROPERTY_TRUE) {
			$options['multiple'] = 'multiple';
			$options['size'] = (!empty($this->config['num_rows'])) ? $this->config['num_rows'] : 1;
		} else {
			$options['multiple'] = $type === self::TYPE_MULTI_SELECT;

			//$options['prompt'] = 'Please select';

			$filteredDataSource = str_replace([':', ',', '.'], '-', $this->data_source_get);
			$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());

			if(isset($this->config['field_identifier']) && $this->config['field_identifier'] != '') {
				$field_identifier = $this->config['field_identifier'];
				$options['data-field-identifier'] = $field_identifier;

				$options['data-id'] = $filteredDataSource.'--'.$field_identifier.'-'.$dataField;
			}

			if($type === self::TYPE_SELECT) {
				Html::addCssClass($options, 'common-linked-list-field');

				if(isset($this->config['refresh_section']) && $this->config['refresh_section'] == 'Y' && $this->config['section_to_refresh'] != '') {
					Html::addCssClass($options, 'common-refresh-section-class');

					$options['data-section-to-refresh'] = $this->config['section_to_refresh'];
				}
			} else if($type === self::TYPE_SELECT_WITH_EXTENSION_FUNCTION) {
				Html::addCssClass($options, 'common-list-with-extensin-function-field');
				//$options['class'] = 'form-control common-list-with-extensin-function-field '.$field_identifier;

				$this->view->registerJs("$('.{$field_identifier}').on('change', function (event) {common.triggerAction('execute_list_with_extension_function', {$field_identifier})});");

				//$this->view->registerJs("$('#{$this->config['identifier']}').on('click', function (event) {common.triggerAction('prev-step', event.target)});");
			}

			if((isset($this->config['linked_list']) && $this->config['linked_list'] == 'Y') && (isset($this->config['field_id_to_monitor']) && $this->config['field_id_to_monitor'] != '') && (isset($this->config['query_to_execute']) && $this->config['query_to_execute'] != '')) {
				$options['data-source-field-identifier'] = $filteredDataSource.'--'.$field_identifier.'-'.$dataField;
				$options['data-target-field-identifier'] = str_replace([':', ',', '.', ' '], '-', $this->config['field_id_to_monitor']);

				$options['data-query-to-execute'] = $this->config['query_to_execute'];
			}
		}

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());

		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

		$select_field_options = array();
		$select_field_options_list = array();
		$select_field_options_data_attribute_list = array();
		$items = array();

		if(!empty($this->config['list_name'])) {
			$select_field_options = $this->getOptionsList($this->config['list_name']);

			//echo '<pre>select_field_options :: '; print_r($select_field_options);

			if(!empty($select_field_options)) {
				foreach($select_field_options as $key => $select_field_option) {
					$select_field_options_list[$select_field_option['entry_name']] = $select_field_option['description'];
					$select_field_options_data_attribute_list[$select_field_option['entry_name']] = $select_field_option['list_name'];
				}
			}

			$options['data-select-field-list'] = json_encode($select_field_options_data_attribute_list);

			//echo '<pre>select_field_options_list'; print_r($select_field_options_list);
			//echo '<pre>select_field_options_data_attribute_list'; print_r($select_field_options_data_attribute_list);

			$items = $select_field_options_list;

			//$items = ArrayHelper::map($select_field_options, 'entry_name', 'description');

			//echo '<pre>'; print_r($items);
		}

		$value = ($options['multiple']) ? explode(self::MULTI_SELECT_DELIMITER, $this->value) : $this->value;

		if (!empty($this->config['apply_input_mask']) && $this->config['apply_input_mask'] == self::PROPERTY_TRUE) {
			Html::addCssClass($options, 'apply-input-mask');
		}

		if ($this->isDisabledField()) {
			if(isset($this->config['multi-select-type']) && $this->config['multi-select-type'] == self::PROPERTY_TRUE) {
				$selected_options = explode(self::MULTI_SELECT_DELIMITER, $this->value);
				$result = array_intersect($items, $selected_options);

				$options['data-toggle'] = 'tooltip';
				$options['data-html'] = 'true';
				$options['title'] = implode(",", $result);

				$value = ($options['multiple']) ? implode(",", $result) : $this->value;

				return Html::textInput($this->getFieldName(), $value, $options);
			} else {
				$items = ArrayHelper::map($select_field_options, 'entry_name', 'description');

				if(empty($items) && $this->value != '')
					return Html::dropDownList($this->getFieldName(), $value, array($this->value => $this->value), $options);
				else
					return Html::dropDownList($this->getFieldName(), $value, $items, $options);
			}
		} else {
			if(isset($this->config['multi-select-type']) && $this->config['multi-select-type'] == self::PROPERTY_TRUE) {
				$options['data-live-search'] = 'true';
				return \brussens\bootstrap\select\Widget::widget([
					//'id' => "datalist-$id",
					'name' => $this->getFieldName(),
					'value' => $value,
					'options' => $options,
					'items' => $items
				]);
			} else {
				if($type === self::TYPE_MULTI_SELECT)
					Html::addCssStyle($options, ['max-height' => '230px', 'overflow' => 'auto']);

				if(empty($items) && $this->value != '')
					return Html::dropDownList($this->getFieldName(), $value, array($this->value => $this->value), $options);
				else
					return Html::dropDownList($this->getFieldName(), $value, $items, $options);
			}
		}
	}

    /**
     * @param array $options
     *
     * @return string
     */
    private function getCheckboxField(array $options)
    {
        if (!empty($this->config['field_group'])) {
            $options['data']['field-group'] = $this->config['field_group'];
        }

        $value = (int)!(empty($this->value) || $this->value == 'f');
        $options['checked'] = (boolean)$value;

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        Html::removeCssClass($options, 'form-control');

		if (!empty($options['readonly'])) {
			$new_options = array_merge($options, ['style' => 'display: none;']);
			$data =  Html::input('checkbox', $this->getFieldName(), $value, $new_options);

			$options['disabled'] = true;
			$data .= Html::input('checkbox', $this->getFieldName(), $value, $options);

			return $data;
		} else {
			return Html::input('checkbox', $this->getFieldName(), $value, $options);
		}
	}

    /**
     * @param array $options
     *
     * @return string
     */
    private function getRadioField(array $options)
    {
        if (!empty($this->config['field_group'])) {
            $options['data']['field-group'] = $this->config['field_group'];
        }

        $value = (int)!empty($this->value);
        $options['checked'] = (boolean)$value;
        Html::removeCssClass($options, 'form-control');

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        return Html::input('radio', $this->getFieldName(), $value, $options);
    }

    /**
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    private function getDateTimePicker(array $options)
    {
        $options['data']['save-format'] = _FormattedHelper::getDefaultDateTimeFormat();

        if (empty($this->value) && empty($this->config['allow_empty']) && (is_null($this->dataId) || $this->dataId == -1)) {
            $this->value = date("Y-m-d H:i:s");
        }

        if (!empty($this->value)) {
            $this->value = $this->Formatted->run($this->value, _FormattedHelper::DATE_TIME_TEXT_FORMAT);
        }

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        return DateTimePicker::widget([
            'name' => $this->getFieldName(),
            'id' => 'w-' . str_replace(".", "", microtime(true)),
            'type' => DateTimePicker::TYPE_COMPONENT_PREPEND,
            'value' => $this->value,
            'removeButton' => false,
            'options' => $options,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => $this->Formatted->getFormatDateTimeForPicker(),
                //'ignoreReadonly' => true
            ]
        ]);
    }

    /**
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    private function getDatePicker(array $options)
    {
        $options['data']['save-format'] = _FormattedHelper::getDefaultDateFormat();

        if (empty($this->config['allow_empty']) && empty($this->value) && (is_null($this->dataId) || $this->dataId == -1)) {
            $this->value = date("Y-m-d", time() - 86400);
        }

        if (!empty($this->value)) {
            $this->value = $this->Formatted->run($this->value, _FormattedHelper::DATE_TEXT_FORMAT);
        }

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        $options['data']['original-format'] = $this->Formatted->getFormatDateForPicker();

        return DatePicker::widget([
            'name' => $this->getFieldName(),
            'id' => 'w-' . str_replace(".", "", microtime(true)),
            'type' => DatePicker::TYPE_COMPONENT_PREPEND,
            'value' => $this->value,
            'removeButton' => false,
            'options' => $options,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => $this->Formatted->getFormatDateForPicker(),
                'enableOnReadonly' => false
            ]
        ]);
    }

	private function getNumericField(array $options)
    {
        Html::addCssClass($options, 'numeric-input');

		//$options['pattern'] = '^[1-9][0-9]*$';
		$options['onkeypress'] = 'return event.charCode >= 48 && event.charCode <= 57';

		//$options['onkeypress'] = "return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13) ? null : event.charCode > 48 && event.charCode <= 57";

		//$options['onkeypress'] = "isNumber(event)";
		//$options['oninput'] = "validity.valid||(value='')";

        if (!empty($this->config['apply_input_mask']) && $this->config['apply_input_mask'] == self::PROPERTY_TRUE) {
            Html::addCssClass($options, 'apply-input-mask');
        }

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        if($this->config['link_type'] == 'Y' && $this->mode == '') {
            if($this->value != '') {
                if($this->config['field_pass_through_link'] != '') {
                    //echo "<pre>"; print_r($this->tmpData);

                    //explode original dataField with . to get last element
                    $explode_dataField = explode('.', $this->dataField);

                    //explode last element with _ so we can attach new field name to it
                    $explode_fieldName = explode('_', end($explode_dataField));

                    //Create new field name to push in original array
                    $new_fieldName = $this->config['field_pass_through_link'];

                    //$new_fieldName = $explode_fieldName[0].'_'.$this->config['field_pass_through_link'];

                    //remove last element from original dataField
                    array_pop($explode_dataField);

                    //push new value to array at end to original dataField
                    array_push($explode_dataField, $new_fieldName);

                    //Join all array values with . again to make new dataField.
                    $implode_dataField = implode('.', $explode_dataField);

                    if($this->layout_type == 'grid')
                        $value = $this->tmpData[$this->cnt][$implode_dataField];
                    else
                        $value = $this->tmpData[$implode_dataField];
                } else {
                    $value = $this->value;
                }

                $url = ['/screen/index',
                    'menu' => $this->config['field_link_menu'],
                    'screen' => $this->config['field_group_screen_link'],
                    '#' => 'tab=' . $this->config['field_screen_link'] . '&search[' . $this->config['field_settings_link'] . ']=' . $value,
                    'isFrame' => $this->config['type-link'] == '_modal'
                ];

                $input = Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, ['target' => $this->config['type-link'], 'class' => 'field-custom-link']);

                if(isset($this->config['data_field']) && $this->config['data_field'] != '')
                    return Html::tag('div', $input, ['style' => 'background-color: #eee; padding: 6px; border-radius: 4px;']);
                else
                    return Html::tag('div', $input, ['style' => 'padding: 6px; border-radius: 4px;']);
            } else {
                return '';
            }
        } else {
            $input = Html::input('text', $this->getFieldName(), $this->value, $options);
            return Html::tag('div', $input, ['class' => 'input-group']);
        }
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function getCurrencyField(array $options)
    {
        Html::addCssClass($options, 'currency-input');
        $options['required'] = true;
        $value = $this->Formatted->currencyNumbers($this->value);

        if (!empty($options['maxlength'])) {
            $str = str_repeat('1', $options['maxlength']);
            $newLength = strlen($this->Formatted->currencyNumbers($str)) - 3;

            $options['maxlength'] = $newLength;
            if (!empty($options['size'])) {
                $options['size'] = $newLength;
            }
        }

        if (!empty($this->config['apply_input_mask']) && $this->config['apply_input_mask'] == self::PROPERTY_TRUE) {
            Html::addCssClass($options, 'apply-input-mask');
        }

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        $suffix = Html::tag('div', $this->Formatted->getCurrencySuffix(), ['class' => 'input-group-addon']);
        $input = Html::input('text', $this->getFieldName(), $value, $options);

        return Html::tag('div', $suffix . $input, ['class' => 'input-group']);
    }

    private function getDecimalField(array $options)
    {
		//echo 'in getDecimalField';
		//echo 'js_event_edit :: '.$this->config['js_event_edit'];

        Html::addCssClass($options, 'decimal-input');
        $precision = 0;
        if (isset($this->config['numeric_field_decimal'])) {
            $options['data']['precision'] = $precision = $this->config['numeric_field_decimal'];
        }
        $value = $this->Formatted->decimalNumbers($this->value, $precision);

        if (!empty($options['maxlength'])) {
            $str = str_repeat('1', $options['maxlength']);
            $newLength = strlen($this->Formatted->decimalNumbers($str, $precision)) - 3;

            $options['maxlength'] = $newLength;
            if (!empty($options['size'])) {
                $options['size'] = $newLength;
            }
        }

        if (!empty($this->config['apply_input_mask']) && $this->config['apply_input_mask'] == self::PROPERTY_TRUE) {
            Html::addCssClass($options, 'apply-input-mask');
        }

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        if($this->config['link_type'] == 'Y' && $this->mode == '') {
            if($this->value != '') {
                if($this->config['field_pass_through_link'] != '') {
                    //echo "<pre>"; print_r($this->tmpData);

                    //explode original dataField with . to get last element
                    $explode_dataField = explode('.', $this->dataField);

                    //explode last element with _ so we can attach new field name to it
                    $explode_fieldName = explode('_', end($explode_dataField));

                    //Create new field name to push in original array
                    $new_fieldName = $this->config['field_pass_through_link'];

                    //$new_fieldName = $explode_fieldName[0].'_'.$this->config['field_pass_through_link'];

                    //remove last element from original dataField
                    array_pop($explode_dataField);

                    //push new value to array at end to original dataField
                    array_push($explode_dataField, $new_fieldName);

                    //Join all array values with . again to make new dataField.
                    $implode_dataField = implode('.', $explode_dataField);

                    if($this->layout_type == 'grid')
                        $value = $this->tmpData[$this->cnt][$implode_dataField];
                    else
                        $value = $this->tmpData[$implode_dataField];
                } else {
                    $value = $this->value;
                }

                $url = ['/screen/index',
                    'menu' => $this->config['field_link_menu'],
                    'screen' => $this->config['field_group_screen_link'],
                    '#' => 'tab=' . $this->config['field_screen_link'] . '&search[' . $this->config['field_settings_link'] . ']=' . $value,
                    'isFrame' => $this->config['type-link'] == '_modal'
                ];

                $input = Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, ['target' => $this->config['type-link'], 'class' => 'field-custom-link']);

                if(isset($this->config['data_field']) && $this->config['data_field'] != '')
                    return Html::tag('div', $input, ['style' => 'background-color: #eee; padding: 6px; border-radius: 4px;']);
                else
                    return Html::tag('div', $input, ['style' => 'padding: 6px; border-radius: 4px;']);
            } else {
                return '';
            }
        } else {
            $input = Html::input('text', $this->getFieldName(), $value, $options);
            return Html::tag('div', $input, ['class' => 'input-group']);
        }
    }

    /**
     * @param array $options
     * @param string|null $formatType
     *
     * @return string
     */
    private function getTextField(array $options, $formatType = null)
    {
		//echo 'in getTextField';
		//echo '<pre>'; print_r($this->config);

        switch ($formatType) {
            case _FormattedHelper::EMAIL_TEXT_FORMAT:
                $inputType = 'email';
                break;
            default:
                $inputType = 'text';
        }

        if (!empty($this->config['apply_input_mask']) && $this->config['apply_input_mask'] == self::PROPERTY_TRUE) {
            Html::addCssClass($options, 'apply-input-mask');
        }

		if (!empty($this->config['field_value1']) && $this->config['field_value1'] != '') {
			$options['data-field-value'] = $this->config['field_value1'];
        }

		if (!empty($this->config['field_value1_helper']) && $this->config['field_value1_helper'] != '') {
			$options['placeholder'] = $this->config['field_value1_helper'];
        }

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());

		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

        if($this->config['link_type'] == 'Y' && $this->mode == '') {
            if($this->value != '') {
                if($this->config['field_pass_through_link'] != '') {
                    //echo "<pre>"; print_r($this->tmpData);
					//echo $this->dataField; die;

                    //explode original dataField with . to get last element
                    $explode_dataField = explode('.', $this->dataField);

                    //explode last element with _ so we can attach new field name to it
                    $explode_fieldName = explode('_', end($explode_dataField));

                    //Create new field name to push in original array
                    $new_fieldName = $this->config['field_pass_through_link'];

                    //$new_fieldName = $explode_fieldName[0].'_'.$this->config['field_pass_through_link'];

                    //remove last element from original dataField
                    array_pop($explode_dataField);

                    //push new value to array at end to original dataField
                    array_push($explode_dataField, $new_fieldName);

                    //Join all array values with . again to make new dataField.
                    $implode_dataField = implode('.', $explode_dataField);

                    if($this->layout_type == 'grid')
                        $value = $this->tmpData[$this->cnt][$implode_dataField];
                    else
                        $value = $this->tmpData[$implode_dataField];
                } else {
                    $value = $this->value;
                }

                $url = ['/screen/index',
                    'menu' => $this->config['field_link_menu'],
                    'screen' => $this->config['field_group_screen_link'],
                    '#' => 'tab=' . $this->config['field_screen_link'] . '&search[' . $this->config['field_settings_link'] . ']=' . $value,
                    'isFrame' => $this->config['type-link'] == '_modal'
                ];

                $input = Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, ['target' => $this->config['type-link'], 'class' => 'field-custom-link']);

				$inputType = 'hidden';
				$input .= Html::input($inputType, $this->getFieldName(), $this->value, $options);

                if(isset($this->config['data_field']) && $this->config['data_field'] != '')
                    return Html::tag('div', $input, ['style' => 'background-color: #eee; padding: 6px; border-radius: 4px;']);
                else
                    return Html::tag('div', $input, ['style' => 'padding: 6px; border-radius: 4px;']);
            } else {
                return '';
            }
        } else {
			if((!empty($this->config['custom_query_pk']) && $this->config['custom_query_pk'] != '') && (!empty($this->config['custom_query_param']) && $this->config['custom_query_param'] != '')) {
				//echo 'in else if';

				$field_id = $options['id'];

				$id = str_replace(".", "", microtime(true));

				$name = str_replace(':', '', $this->config['custom_query_param']);

				$query_param = '';

				return Typeahead::widget([
					'id' => "inline-search-$id",
					'name' => $this->getFieldName(),
					'value' => $this->value,
					'options' => $options,
					'pluginOptions' => ['highlight' => true],
					'dataset' => [
						[
							'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
							'display' => $name,
							'limit' => CommandData::SEARCH_LIMIT,
							'remote' => ['url' => '#'],
							'source' => new JsExpression("
								function (query, syncResults, asyncResults) {
									var check = false;

									var final_search_string = '';
									var last_index_position = '';
									var last_element = '';

									if(query.lastIndexOf('[') !== -1 && query.lastIndexOf(']') == -1) {
										console.log('in lastIndexOf [ if');

										last_index_position = query.lastIndexOf('[');
										last_element = query.substring(last_index_position+1);

										check = true;
									}

									if(query.lastIndexOf(']') !== -1) {
										console.log('in lastIndexOf ] if');

										last_index_position = query.lastIndexOf(']');
										final_search_string = query.substring(last_index_position+1);

										if(final_search_string.lastIndexOf('[') !== -1) {
											last_index_position = final_search_string.lastIndexOf('[');
											last_element = final_search_string.substring(last_index_position+1);

											check = true;
										}
									}

									console.log('last_element');
									console.log(last_element);

									console.log('check');
									console.log(check);

									if(check) {
										setTimeout(function()  {
											common.inlineSearchResults('{$this->config['custom_query_pk']}', [{name: '{$this->config['custom_query_param']}', value: last_element, query_param: query}], asyncResults);
										}, 1000);
									} else {
										setTimeout(function()  {
											$('input.tt-input').removeClass('loading');
											$('#inline-search-$id').removeClass('loading');
										}, 1000);
									}
								}
							"),
							'templates' => [
								'notFound' => Html::tag('div', 'No search result', ['class' => 'text-danger', 'style' => ['padding' => '0 8px']])
							]
						]
					],
					'pluginEvents' => [
						"typeahead:asyncrequest" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
							
						}",
						"typeahead:asynccancel" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
						}",
						"typeahead:asyncreceive" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
						}",
						"typeahead:change" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
						}",
						"typeahead:select" => "function(e, data) {
							console.log('in select');
							console.log(data);

							if (data['$name']) {
								console.log('in if');
								console.log($('#$field_id').typeahead());

								$('#$field_id').typeahead('val', data['query_param']+'['+data['$name']+']');
							}
						}"
					]
				]);
			} else {
				return Html::input($inputType, $this->getFieldName(), $this->value, $options);
			}
        }
    }

    private function getTextAreaField(array $options)
    {
		//echo 'in getTextAreaField';
		//echo $this->config['js_event_change'];

		$dataField = str_replace([':', ',', '.', ' '], '-', $this->getFieldName());
		if (!empty($this->config['js_event_edit']) || !empty($this->config['js_event_insert']) || !empty($this->config['js_event_change'])) {
			Html::addCssClass($options, $dataField.'_'.RenderTabHelper::$template_id);

			//$options['class'] = 'form-control '.$dataField.'_'.RenderTabHelper::$template_id;
		}

		$options['rows'] = !empty($this->config['num_rows']) ? $this->config['num_rows'] : 2;

        if($this->config['link_type'] == 'Y' && $this->mode == '') {
            if($this->value != '') {
                if($this->config['field_pass_through_link'] != '') {
                    //echo "<pre>"; print_r($this->tmpData);

                    //explode original dataField with . to get last element
                    $explode_dataField = explode('.', $this->dataField);

                    //explode last element with _ so we can attach new field name to it
                    $explode_fieldName = explode('_', end($explode_dataField));

                    //Create new field name to push in original array
                    $new_fieldName = $this->config['field_pass_through_link'];

                    //$new_fieldName = $explode_fieldName[0].'_'.$this->config['field_pass_through_link'];

                    //remove last element from original dataField
                    array_pop($explode_dataField);

                    //push new value to array at end to original dataField
                    array_push($explode_dataField, $new_fieldName);

                    //Join all array values with . again to make new dataField.
                    $implode_dataField = implode('.', $explode_dataField);

                    if($this->layout_type == 'grid')
                        $value = $this->tmpData[$this->cnt][$implode_dataField];
                    else
                        $value = $this->tmpData[$implode_dataField];
                } else {
                    $value = $this->value;
                }

                $url = ['/screen/index',
                    'menu' => $this->config['field_link_menu'],
                    'screen' => $this->config['field_group_screen_link'],
                    '#' => 'tab=' . $this->config['field_screen_link'] . '&search[' . $this->config['field_settings_link'] . ']=' . $value,
                    'isFrame' => $this->config['type-link'] == '_modal'
                ];

                $input = Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, ['target' => $this->config['type-link'], 'class' => 'field-custom-link']);

				$new_options = array_merge($options, ['style' => 'display: none;']);
				$input .= Html::textarea($this->getFieldName(), $this->value, $new_options);

                if(isset($this->config['data_field']) && $this->config['data_field'] != '')
                    return Html::tag('div', $input, ['style' => 'background-color: #eee; padding: 6px;  border-radius: 4px;']);
                else
                    return Html::tag('div', $input, ['style' => 'padding: 6px;  border-radius: 4px;']);
            } else {
                return '';
            }
        } else {
			if((!empty($this->config['custom_query_pk']) && $this->config['custom_query_pk'] != '') && (!empty($this->config['custom_query_param']) && $this->config['custom_query_param'] != '')) {
				$field_id = $options['id'];

				$id = str_replace(".", "", microtime(true));

				$name = str_replace(':', '', $this->config['custom_query_param']);

				$query_param = '';

				return Typeahead::widget([
					'id' => "inline-search-$id",
					'name' => $this->getFieldName(),
					'value' => $this->value,
					'options' => $options,
					'pluginOptions' => ['highlight' => true],
					'dataset' => [
						[
							'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
							'display' => $name,
							'limit' => CommandData::SEARCH_LIMIT,
							'remote' => ['url' => '#'],
							'source' => new JsExpression("
								function (query, syncResults, asyncResults) {
									var check = false;

									var final_search_string = '';
									var last_index_position = '';
									var last_element = '';

									if(query.lastIndexOf('[') !== -1 && query.lastIndexOf(']') == -1) {
										console.log('in lastIndexOf [ if');

										last_index_position = query.lastIndexOf('[');
										last_element = query.substring(last_index_position+1);

										check = true;
									}

									if(query.lastIndexOf(']') !== -1) {
										console.log('in lastIndexOf ] if');

										last_index_position = query.lastIndexOf(']');
										final_search_string = query.substring(last_index_position+1);

										if(final_search_string.lastIndexOf('[') !== -1) {
											last_index_position = final_search_string.lastIndexOf('[');
											last_element = final_search_string.substring(last_index_position+1);

											check = true;
										}
									}

									console.log('last_element');
									console.log(last_element);

									if(check) {
										setTimeout(function()  {
											common.inlineSearchResults('{$this->config['custom_query_pk']}', [{name: '{$this->config['custom_query_param']}', value: last_element, query_param: query}], asyncResults);
										}, 1000);
									} else {
										setTimeout(function()  {
											$('input.tt-input').removeClass('loading');
											$('#inline-search-$id').removeClass('loading');
										}, 1000);

										//common.inlineSearchTemp();
									}
								}
							"),
							'templates' => [
								'notFound' => Html::tag('div', 'No search result', ['class' => 'text-danger', 'style' => ['padding' => '0 8px']])
							]
						]
					],
					'pluginEvents' => [
						"typeahead:asyncrequest" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
						}",
						"typeahead:asynccancel" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
						}",
						"typeahead:asyncreceive" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
						}",
						"typeahead:change" => "function(e) {
							$('input.tt-input').removeClass('loading');
							$('#inline-search-$id').removeClass('loading');
							$('#inline-search-$id').css('background-color', '#fff');
						}",
						"typeahead:select" => "function(e, data) {
							console.log('in select');
							console.log(data);

							if (data['$name']) {
								console.log('in if');
								console.log($('#$field_id').typeahead());

								$('#$field_id').typeahead('val', data['query_param']+'['+data['$name']+']');
							}
						}"
					]
				]);
			} else {
				return Html::textarea($this->getFieldName(), $this->value, $options);
			}
		}
    }

    private function getHiddenField(array $options)
    {
        ArrayHelper::remove($options, 'style');
        return Html::hiddenInput($this->getFieldName(), $this->value, $options);
    }

    private function getDocumentField(array $options)
    {
        if (!empty($options['disabled'])) {
            return Html::tag('div', 'Read only field', ['style' => 'color: red; padding: 7px 0 0 0;']);
        }

        if (empty($this->config['field_document_family']) || empty($this->config['field_document_category'])) {
            return Html::tag('div', 'Access denied', ['style' => 'color: red; padding: 7px 0 0 0;']);
        }

        $accessRight = DocumentGroup::getAccessPermission($this->config['field_document_family'], $this->config['field_document_category']);
        if ($accessRight != DocumentGroup::ACCESS_RIGHT_FULL) {
            return Html::tag('div', 'Access denied', ['style' => 'color: red; padding: 7px 0 0 0;']);
        }

        $containerId = 'drop-zone-' . str_replace(".", "", microtime(true));

        $this->getView()->registerCss("#$containerId {{$options['style']}}");
        $dropZone = DropZone::widget([
            'dropzoneContainer' => $containerId,
            'uploadUrl' => Url::to(['/file/upload'], true),
            'options' => [
                'paramName' => "file",
                'maxFilesize' => '20',
                'uploadMultiple' => false,
                'maxFiles' => 1,
                'acceptedFiles' => '.xlsx,.xls,.doc, .docx,.ppt, .pptx, text/plain, application/pdf, image/*',
                'previewTemplate' => '
                    <div class="dz-preview dz-file-preview">
                        <div class="dz-details">
                            <div class="dz-remove" data-dz-remove><span class="glyphicon glyphicon-remove"></span></div>
                            <div class="dz-size"><span data-dz-size></span></div>
                            <div class="dz-filename"><span data-dz-name></span></div>
                        </div>
                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                        <div class="dz-success-mark"><span class="glyphicon glyphicon-ok"></span></div>
                        <div class="dz-error-mark"><span class="glyphicon glyphicon-exclamation-sign"></span></div>
                        <div class="dz-error-message"><span data-dz-errormessage></span></div>
                    </div>
                '
            ],
            'clientEvents' => [
                'addedfile' => "function(file, xhr) {
                    $(file.previewElement).parents('.dropzone').find('.dz-message').hide();
                }",
                'success' => "function(file, xhr) {
                    var me = $(file.previewElement),
                        message = me.parents('.dropzone').find('.dz-message'),
                        uploadArrowButton = $(file.previewElement).parents('.dropzone').next('.upload-arrow-button');
                        
                    me.find('.dz-remove').click(function () {
                        message.show();
                        uploadArrowButton.show();
                        uploadArrowButton.prop('disabled', true).removeClass('is-active').removeClass('is-completed');
                        uploadArrowButton.attr('data-file-name', '');
                        uploadArrowButton.attr('data-original-file-name', '');
                        
                        uploadArrowButton.parent().find('input[type=\"hidden\"]').val('');
                    });
                    
                    uploadArrowButton.prop('disabled', false);
                    uploadArrowButton.attr('data-file-name', xhr.file_name);
                    uploadArrowButton.attr('data-original-file-name', xhr.original_file_name);
                }",
                'removedfile' => "function (file) {
                    var response = JSON.parse(file.xhr.response);
                    $.post('" . Url::to(['/file/delete'], true) . "', {file_name: response.file_name}).done(function(data) {
                        console.log('File \"' + response.file_name + '\" has been deleted');
                    });
                }"
            ],
        ]);

        $uploadButton = Html::button('<span class="glyphicon glyphicon-arrow-up"></span>', [
            'class' => 'btn btn-default upload-arrow-button',
            'data-family' => (!empty($this->config['field_document_family'])) ? $this->config['field_document_family'] : null,
            'data-category' => (!empty($this->config['field_document_category'])) ? $this->config['field_document_category'] : null,
            'disabled' => true
        ]);
        $hiddenInput = Html::hiddenInput($this->getFieldName(), $this->value, ['id' => $this->getFieldID(), 'class' => 'form-control', 'data' => $options['data']]);

        return Html::tag('div', $dropZone . $uploadButton . $hiddenInput, ['class' => 'upload-setting-block']);
    }

    private function getAlertField(array $options) {
        $button = Html::button('Edit Alert', [
            'class' => 'btn btn-default alert-field-btn',
            'data-target'=>'#alert-message-edit-modal',
            'data-toggle'=>'modal',
            'data-alert'=>$this->getFieldID(),
            'data-sub-id-btn' => (!is_null($this->dataId)) ? $this->dataId : null,
        ]);
        $input = Html::hiddenInput($this->getFieldName(), $this->value, [
            'id' => $this->getFieldID(),
            'class' => 'form-control form-control-grid sub-id',
            'data' => $options['data'],
        ]);

        return Html::tag('div', $button . $input, ['class'=>'alert-block']);
    }

    private function getInlineSearchField($options)
    {
		//echo 'in getInlineSearchField';

        if (empty($this->config['custom_query_pk']) || empty($this->config['custom_query_param'])) {
            return $this->getTextField($options);
        }

        if (!empty($this->config['apply_input_mask']) && $this->config['apply_input_mask'] == self::PROPERTY_TRUE) {
            Html::addCssClass($options, 'apply-input-mask');
        }

		$name = str_replace(':', '', $this->config['custom_query_param']);

        $id = str_replace(".", "", microtime(true));

        return Typeahead::widget([
            'id' => "inline-search-$id",
            'name' => $this->getFieldName(),
            'value' => $this->value,
            'options' => $options,
            'pluginOptions' => ['highlight' => true],
            'dataset' => [
                [
                    'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
                    'display' => $name,
                    //'display' => $this->config['custom_query_param'],
                    'limit' => CommandData::SEARCH_LIMIT,
                    'remote' => ['url' => '#'],
                    'source' => new JsExpression("
                        function (query, syncResults, asyncResults) {
                            setTimeout(function()  {
                                common.inlineSearchResults('{$this->config['custom_query_pk']}', [{name: '{$this->config['custom_query_param']}', value: query}], asyncResults);
                            }, 1000);
                        }
                    "),
                    'templates' => [
                        'notFound' => Html::tag('div', 'No search result', ['class' => 'text-danger', 'style' => ['padding' => '0 8px']])
                    ]
                ]
            ]
        ]);
    }

    private function getDatalistField($options)
    {
        $id = str_replace(".", "", microtime(true));

        $items = [];
		$value1 = '';

        if (isset($this->config['dropdown_values'])) {
            $items = explode(self::MULTI_SELECT_DELIMITER, $this->config['dropdown_values']);
            $items = array_combine($items, $items);
        }

        if ($this->isDisabledField()) {
			$selected_options = explode(self::MULTI_SELECT_DELIMITER, $this->value);
			$result = array_intersect($items, $selected_options);

			$options['data-toggle'] = 'tooltip';
			$options['data-html'] = 'true';
			$options['title'] = implode(",", $result);

            return Html::textInput($this->getFieldName(), implode(",", $result), $options);
        } else {
			if(isset($this->config['multi-select-type']) && $this->config['multi-select-type'] == self::PROPERTY_TRUE) {
				$options['size'] = 1;
				$options['multiple'] = 'multiple';

				$valuesList = explode(self::MULTI_SELECT_DELIMITER, $this->value);

				if (!empty($valuesList)) {
					foreach ($valuesList as $value) {
						$dataFromList = GetListList::getByListName($value, $this->config['list_name']);
						$value1 .= empty($dataFromList) ? $dataFromList['description'] : ('<br/>' . $dataFromList['description']);
					}

					if($value1 == '')
						$value1 = ($options['multiple']) ? explode(self::MULTI_SELECT_DELIMITER, $this->value) : $this->value;
				} else {
					$value1 = $this->value;
				}
            } else {
				$options['prompt'] = 'Please select';
				$value1 = $this->value;
			}

            $options['data-live-search'] = 'true';
            return \brussens\bootstrap\select\Widget::widget([
                'id' => "datalist-$id",
                'name' => $this->getFieldName(),
                'value' => $value1,
                'options' => $options,
                'items' => $items
            ]);
        }
    }

    private function getDataListRelationField($options) {
        if (empty($this->config['custom_query_pk'])) {
            return $this->getTextField($options);
        }

		$value1 = '';

		if(isset($this->config['multi-select-type']) && $this->config['multi-select-type'] == self::PROPERTY_TRUE) {
			$options['size'] = 3;
			$options['multiple'] = 'multiple';

			$valuesList = explode(self::MULTI_SELECT_DELIMITER, $this->value);

			if (!empty($valuesList)) {
				foreach ($valuesList as $value) {
					$dataFromList = GetListList::getByListName($value, $this->config['list_name']);
					$value1 .= empty($dataFromList) ? $dataFromList['description'] : ('<br/>' . $dataFromList['description']);
				}

				if($value1 == '')
					$value1 = ($options['multiple']) ? explode(self::MULTI_SELECT_DELIMITER, $this->value) : $this->value;
			} else {
				$value1 = $this->value;
			}
		} else {
			$options['prompt'] = 'Please select';
			$value1 = $this->value;
		}

		$options['data']['init-value'] = $value1;
		$options['data']['relation-field'] = true;
		$options['data']['relation-id'] = $this->config['datalist_relation_default'];
		$options['data']['relation-default'] = $this->config['datalist_relation_default'];
		$options['data']['custom-query'] = $this->config['custom_query_pk'];

		if(isset($this->config['multi-select-type']) && $this->config['multi-select-type'] == self::PROPERTY_TRUE) {
			if ($this->isDisabledField()) {
				//$selected_options = explode(self::MULTI_SELECT_DELIMITER, $this->value);
				//$result = array_intersect($items, $selected_options);

				$options['data-toggle'] = 'tooltip';
				$options['data-html'] = 'true';
				$options['title'] = str_replace(";", ',', $this->value);
				//$options['title'] = implode(",", $result);

				$data = Html::textInput($this->getFieldName(), str_replace(";", ',', $this->value), $options);
				//$data .= Html::dropDownList($this->getFieldName(), null, [$this->value => $this->value], $options);

				return $data;
			} else {
				$options['data-live-search'] = 'true';
				return \brussens\bootstrap\select\Widget::widget([
					//'id' => "datalist-$id",
					'name' => $this->getFieldName(),
					'value' => $value1,
					'options' => $options,
					//'items' => null
				]);
			}
		} else {
			return Html::dropDownList($this->getFieldName(), null, [$this->value => $this->value], $options);
		}
    }

    private function getButtonField($options) {
		//echo 'in getButtonField';
		//echo '<pre>'; print_r($this->config);
		//echo $this->config['identifier'];

        Html::addCssStyle($options, ['width' => '100%']);
        Html::addCssClass($options, 'btn btn-default screen-btn-custom-action');
        Html::removeCssClass($options, 'form-control');

        if (empty($this->config['identifier']) && $this->isGridField) {
            $this->config['identifier'] = 'grid-button-' . str_replace(".", "", microtime(true));
        }

        $options['id'] = $this->config['identifier'];

        if (in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]) && $this->mode == $this->config['button_action']) {
            Html::removeCssClass($options, 'screen-btn-custom-action');

            $buttons[] = Html::button(\yii\bootstrap\Html::icon('floppy-disk'), ['class' => 'btn btn-success', 'id' => "{$this->config['identifier']}_{$this->config['button_action']}_save"]);
            $buttons[] = Html::button(\yii\bootstrap\Html::icon('remove-circle'), ['class' => 'btn btn-danger', 'id' => "{$this->config['identifier']}_{$this->config['button_action']}_cancel"]);

            $this->view->registerJs("$('#{$this->config['identifier']}_{$this->config['button_action']}_save').on('click', function () {common.triggerSpecialAction('{$this->config['button_action']}', 'save')});");
            $this->view->registerJs("$('#{$this->config['identifier']}_{$this->config['button_action']}_cancel').on('click', function () {common.triggerSpecialAction('{$this->config['button_action']}', 'cancel')});");

            return Html::tag('div', implode('', $buttons), ['class' => 'btn-group']);
        }

        if ($this->config['button_action']) {
            if ($this->config['button_action'] == 'report') {
                $reportTemplate = Report::getModel($this->config['report_template']);
                if ($reportTemplate) {
                    if (!empty($reportTemplate['simple_search'])) {
                        $options['data-simple-search'] = $reportTemplate['simple_search'];
                    } elseif (!empty($reportTemplate['multi_search'])) {
                        $options['data-multi-search'] = $reportTemplate['multi_search'];
                    }

                    if ($reportTemplate['batch_query_name']) {
                        $customQuery = CommandData::getModel($reportTemplate['batch_query_name'], [
                            'lib_name' => 'CodiacSDK.CommonArea',
                            'func_name' => 'GetCustomQueryList'
                        ]);
                        if ($customQuery) {
                            $options['data-batch-query'] = $customQuery;
                        }
                    }
                }

                $options['data-report-template'] = $this->config['report_template'];
                $options['data-report-name'] = isset($reportTemplate['description']) ? $reportTemplate['description'] : null;
            } else if ($this->config['button_action'] == 'execute' && !empty($this->config['execute_function_custom'])) {
                if ($customFunctions = Json::decode($this->config['execute_function_custom'])) {
                    $customFunctions = explode(';', $customFunctions);
                    if (count($customFunctions) > 1) {
                        $preFunction = !empty($this->config['execute_function_pre']) ? $this->config['execute_function_pre'] : '{}';
                        $postFunction = !empty($this->config['execute_function_post']) ? $this->config['execute_function_post'] : '{}';
                        $subId = ($this->isGridField) ? "{id: '$this->dataId'}" : "null";

                        $this->view->registerJs("
                            $('#{$this->config['identifier']}').on('click', function () {
                                common.triggerExecute({$this->config['execute_function_get']}, ['$customFunctions[0]', '$customFunctions[1]'], $preFunction, $postFunction, $subId);
                            });
                        ");
                    }
                }
            } else if($this->config['button_action'] == 'document') {
				//echo '<pre>'; print_r($this->tmpData);

				$selected_document_category = array();
				$document_category = array();
				$document_family = '';

				if(!empty($this->tmpData)) {
					$document_category_list = GetListList::getDocumentCategory($this->config['field_btn_document_family']);
					//echo '<pre>'; print_r($document_category_list);

					if(!empty($this->config['field_btn_document_category'])) {
						//echo '<pre>'; print_r($this->config['field_btn_document_category'][0]);

						foreach($document_category_list as $key => $val) {
							$selected_document_category = array();

							$document_family = $val['family_name'];

							array_push($document_category, $val['category']);

							if(in_array($val['category'], $this->config['field_btn_document_category'][0])) {
								//print_r(array_keys($val, $val['key_part_1']));

								if($val['key_part_1'] != '')
									$selected_document_category['KP1'][] = $this->tmpData[$val['key_part_1']];
									//$selected_document_category['KP1'][] = $val['key_part_1'];

								if($val['key_part_2'] != '')
									$selected_document_category['KP2'][] = $this->tmpData[$val['key_part_2']];
									//$selected_document_category['KP2'][] = $val['key_part_2'];

								if($val['key_part_3'] != '')
									$selected_document_category['KP3'][] = $this->tmpData[$val['key_part_3']];

								if($val['key_part_4'] != '')
									$selected_document_category['KP4'][] = $this->tmpData[$val['key_part_4']];

								if($val['key_part_5'] != '')
									$selected_document_category['KP5'][] = $this->tmpData[$val['key_part_5']];
							}
						}

						//echo '<pre>'; print_r($selected_document_category);

						//$field_btn_document_category = implode(',', $this->config['field_btn_document_category'][0]);
						$options['data-document-kp'] = json_encode($selected_document_category);
						$options['data-document-category'] = json_encode($document_category);
						$options['data-document-family'] = $document_family;
						$options['data-id'] = $this->config['identifier'];
						$options['data-document-download-url'] = Url::to(['/file/document-download'], true);
					}
				}
			} else {
                $this->view->registerJs("$('#{$this->config['identifier']}').on('click', function (event) {common.triggerAction('{$this->config['button_action']}', event.target)});");
            }
        } else if($this->config['button_action'] == '') {
			$this->view->registerJs("$('#{$this->config['identifier']}').on('click', function (event) {common.triggerAction('prev-step', event.target)});");
		}

        if (isset($this->internationalization['value'][Yii::$app->language])) {
            $this->config['value'] = $this->internationalization['value'][Yii::$app->language];
        }

        return Html::button($this->config['value'], $options);
    }

	private function getImageField($options) {
		//echo 'in getImageField';
		//echo '<pre>'; print_r($this->config);

		Html::addCssClass($options, 'screen-btn-custom-action');
        Html::removeCssClass($options, 'form-control');

		$image_identifier = '';
		$image_value = '';
		$image_action = '';

		if(isset($this->config['image_identifier']) && $this->config['image_identifier'] != '') {
			$image_identifier = $this->config['image_identifier'];
			$image_value = $this->config['image_value'];
			$image_action = $this->config['image_action'];
		} else if(isset($this->config['tbl_image_identifier']) && $this->config['tbl_image_identifier'] != '') {
			$image_identifier = $this->config['tbl_image_identifier'];
			$image_value = $this->config['tbl_static_modal_image_name'];
			$image_action = $this->config['tbl_image_action'];
		}

        if (isset($this->config['image_identifier']) && $this->isGridField) {
            $this->config['image_identifier'] = 'grid-button-' . str_replace(".", "", microtime(true));
        }

		if (isset($this->config['tbl_image_identifier']) && $this->isGridField) {
            $this->config['tbl_image_identifier'] = 'grid-button-' . str_replace(".", "", microtime(true));
        }

        $options['id'] = $image_identifier;
		$link_options['id'] = $image_identifier;

		if(isset($this->config['image_value']) && $this->config['image_value'] != '') {
			$options['title'] = $image_value;
			$options['alt'] = $image_value;
		}

        if (in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]) && $this->mode == $image_action) {
            Html::removeCssClass($options, 'screen-btn-custom-action');

            $buttons[] = Html::button(\yii\bootstrap\Html::icon('floppy-disk'), ['class' => 'btn btn-success', 'id' => "{$image_identifier}_{$image_action}_save"]);
            $buttons[] = Html::button(\yii\bootstrap\Html::icon('remove-circle'), ['class' => 'btn btn-danger', 'id' => "{$image_identifier}_{$image_action}_cancel"]);

            $this->view->registerJs("$('#{$image_identifier}_{$image_action}_save').on('click', function () {common.triggerSpecialAction('{$image_action}', 'save')});");
            $this->view->registerJs("$('#{$image_identifier}_{$image_action}_cancel').on('click', function () {common.triggerSpecialAction('{$image_action}', 'cancel')});");

            return Html::tag('div', implode('', $buttons), ['class' => 'btn-group']);
        }

        if (isset($image_action) && $image_action) {
            if ($image_action == 'report') {
                $reportTemplate = Report::getModel($this->config['report_template']);
                if ($reportTemplate) {
                    if (!empty($reportTemplate['simple_search'])) {
                        $link_options['data-simple-search'] = $reportTemplate['simple_search'];
                    } elseif (!empty($reportTemplate['multi_search'])) {
                        $link_options['data-multi-search'] = $reportTemplate['multi_search'];
                    }

                    if ($reportTemplate['batch_query_name']) {
                        $customQuery = CommandData::getModel($reportTemplate['batch_query_name'], [
                            'lib_name' => 'CodiacSDK.CommonArea',
                            'func_name' => 'GetCustomQueryList'
                        ]);
                        if ($customQuery) {
                            $link_options['data-batch-query'] = $customQuery;
                        }
                    }
                }

                $link_options['data-report-template'] = $this->config['report_template'];
                $link_options['data-report-name'] = isset($reportTemplate['description']) ? $reportTemplate['description'] : null;
            }

            if ($image_action == 'execute' && !empty($this->config['execute_function_custom'])) {
                if ($customFunctions = Json::decode($this->config['execute_function_custom'])) {
                    $customFunctions = explode(';', $customFunctions);
                    if (count($customFunctions) > 1) {
                        $preFunction = !empty($this->config['execute_function_pre']) ? $this->config['execute_function_pre'] : '{}';
                        $postFunction = !empty($this->config['execute_function_post']) ? $this->config['execute_function_post'] : '{}';
                        $subId = ($this->isGridField) ? "{id: '$this->dataId'}" : "null";

                        $this->view->registerJs("
                            $('#{$image_identifier}').on('click', function () {
                                common.triggerExecute({$this->config['execute_function_get']}, ['$customFunctions[0]', '$customFunctions[1]'], $preFunction, $postFunction, $subId);
                            });
                        ");
                    }
                }
            } else {
                $this->view->registerJs("$('#{$image_identifier}').on('click', function (event) {common.triggerAction('{$image_action}', event.target)});");
            }

			if($image_action == 'document') {
				//echo '<pre>'; print_r($this->tmpData);

				$selected_document_category = array();
				$document_category = array();
				$document_family = '';

				$document_category_list = GetListList::getDocumentCategory($this->config['field_btn_document_family']);
				//echo '<pre>'; print_r($document_category_list);

				if(!empty($this->config['field_btn_document_category'])) {
					//echo '<pre>'; print_r($this->config['field_btn_document_category'][0]);

					foreach($document_category_list as $key => $val) {
						$document_family = $val['family_name'];

						array_push($document_category, $val['category']);

						if(in_array($val['category'], $this->config['field_btn_document_category'][0])) {
							//print_r(array_keys($val, $val['key_part_1']));

							if($val['key_part_1'] != '')
								$selected_document_category['KP1'][] = $this->tmpData[$val['key_part_1']];
								//$selected_document_category['KP1'][] = $val['key_part_1'];

							if($val['key_part_2'] != '')
								$selected_document_category['KP2'][] = $this->tmpData[$val['key_part_2']];
								//$selected_document_category['KP2'][] = $val['key_part_2'];

							if($val['key_part_3'] != '')
								$selected_document_category['KP3'][] = $this->tmpData[$val['key_part_3']];

							if($val['key_part_4'] != '')
								$selected_document_category['KP4'][] = $this->tmpData[$val['key_part_4']];

							if($val['key_part_5'] != '')
								$selected_document_category['KP5'][] = $this->tmpData[$val['key_part_5']];
						}
					}

					//echo '<pre>'; print_r($selected_document_category);

					//$field_btn_document_category = implode(',', $this->config['field_btn_document_category'][0]);
					$link_options['data-document-kp'] = json_encode($selected_document_category);
					$link_options['data-document-category'] = json_encode($document_category);
					$link_options['data-document-family'] = $document_family;
					$link_options['data-id'] = $image_identifier;
					$link_options['data-document-download-url'] = Url::to(['/file/document-download'], true);
				}
			}
        }

        if (isset($this->internationalization['image_value'][Yii::$app->language])) {
            $this->config['image_value'] = $this->internationalization['image_value'][Yii::$app->language];
        }

		//"data:image/jpeg;base64,base64_encode image data"
		if(isset($this->config['image_name']) && $this->config['image_name'] != '') {
			Html::addCssStyle($options, ['width' => '100%']);
			Html::addCssStyle($options, ['padding' => '6px 12px']);
			Html::addCssClass($options, 'responsive-img');
			Html::removeCssClass($options, 'form-control');

			$image_name = $this->config['image_name'];

			if(isset($this->config['allow_full_size_image']) && $this->config['allow_full_size_image'] == 'Y')
				return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", ["class" => "common_image_button_class", "id" => $image_identifier]);
			else
				return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", $link_options);
		} else if(isset($this->config['tbl_static_modal_image_name']) && $this->config['tbl_static_modal_image_name'] != '') {
			Html::addCssStyle($options, ['width' => $this->config['field_width_value']]);
			Html::addCssStyle($options, ['width' => $this->config['field_height_value']]);
			//Html::addCssStyle($options, ['padding' => '6px 12px']);
			Html::addCssClass($options, 'responsive-img');
			Html::removeCssClass($options, 'form-control');

			$image_name = $this->config['tbl_static_modal_image_name'];

			if(isset($this->config['tbl_allow_full_size_image']) && $this->config['tbl_allow_full_size_image'] == 'Y')
				return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", ["class" => "common_image_button_class", "id" => $image_identifier]);
			else
				return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", $link_options);
		} else {
			$image_name = '';

			return Html::img("data:image/jpeg;base64,$image_name", $options);
		}
    }

	private function getDImageField($options)
	{
		//echo 'in getDImageField';
		//echo '<pre>'; print_r($this->config);
		//echo $this->value;
		//echo $this->dataField;
		//echo '<pre>'; print_r($this->layout_config);

		$image_identifier = '';
		$image_value = '';
		$image_action = '';

		if(isset($this->config['dimage_identifier']) && $this->config['dimage_identifier'] != '') {
			$image_identifier = $this->config['dimage_identifier'];
			//$image_value = $this->config['image_value'];
			$image_action = $this->config['dimage_action'];
		} else if(isset($this->config['tbl_image_identifier']) && $this->config['tbl_image_identifier'] != '') {
			$image_identifier = $this->config['tbl_image_identifier'];
			$image_value = $this->config['tbl_static_modal_image_name'];
			$image_action = $this->config['tbl_image_action'];
		}

		Html::addCssClass($options, 'screen-btn-custom-action');
        Html::removeCssClass($options, 'form-control');

		$options['id'] = $image_identifier;
		$link_options['id'] = $image_identifier;

		if (in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]) && $this->mode == $image_action) {
			Html::removeCssClass($options, 'screen-btn-custom-action');

			$buttons[] = Html::button(\yii\bootstrap\Html::icon('floppy-disk'), ['class' => 'btn btn-success', 'id' => "{$image_identifier}_{$image_action}_save"]);
			$buttons[] = Html::button(\yii\bootstrap\Html::icon('remove-circle'), ['class' => 'btn btn-danger', 'id' => "{$image_identifier}_{$image_action}_cancel"]);

			$this->view->registerJs("$('#{$image_identifier}_{$image_action}_save').on('click', function () {common.triggerSpecialAction('{$image_action}', 'save')});");
			$this->view->registerJs("$('#{$image_identifier}_{$image_action}_cancel').on('click', function () {common.triggerSpecialAction('{$image_action}', 'cancel')});");

			return Html::tag('div', implode('', $buttons), ['class' => 'btn-group']);
		}

		if($this->mode === RenderTabHelper::MODE_EDIT || $this->mode === RenderTabHelper::MODE_INSERT) {
			$inputType = 'hidden';

			if($this->value != '')
				$input = '<div class="tbl-dynamic-common-div-class"><input type="file" class="common_tbl_field_upload_image_class" id="file'.$this->cnt.'_'.$image_identifier.'" data-input-id="'.$this->cnt.'_'.$image_identifier.'" style="display: none;" /><a href="javascript: void(0);" id="upload_link'.$this->cnt.'_'.$image_identifier.'" onclick="$(\'#file'.$this->cnt.'_'.$image_identifier.'\').trigger(\'click\'); return false;"><img src="" class="glyphicon glyphicon-picture"></a>&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/jpeg;base64,'.$this->value.'" id="dimage_show_'.$this->cnt.'_'.$image_identifier.'" style="width: 60px; height: 60px;"></div>';
			else
				$input = '<div class="tbl-dynamic-common-div-class"><input type="file" class="common_tbl_field_upload_image_class" id="file'.$this->cnt.'_'.$image_identifier.'" data-input-id="'.$this->cnt.'_'.$image_identifier.'" style="display: none;" /><a href="javascript: void(0);" id="upload_link'.$this->cnt.'_'.$image_identifier.'" onclick="$(\'#file'.$this->cnt.'_'.$image_identifier.'\').trigger(\'click\'); return false;"><img src="" class="glyphicon glyphicon-picture"></a>&nbsp;&nbsp;&nbsp;&nbsp;<img src="" id="dimage_show_'.$this->cnt.'_'.$image_identifier.'" style="width: auto; height: 60px; display: none;"></div>';

			$options['id'] = 'dimage'.$this->cnt.'_'.$image_identifier;
			$link_options['id'] = $image_identifier;

			$input .= Html::input($inputType, $this->getFieldName(), $this->value, $options);

			return Html::tag('div', $input, ['class' => 'tbl-dynamic-image-field-common-div']);
		} else {
			if (isset($this->config['dimage_identifier']) && $this->isGridField) {
				$this->config['dimage_identifier'] = 'grid-button-' . str_replace(".", "", microtime(true));
			}

			if (isset($this->config['tbl_image_identifier']) && $this->isGridField) {
				$this->config['tbl_image_identifier'] = 'grid-button-' . str_replace(".", "", microtime(true));
			}

			if (isset($image_action) && $image_action) {
				if ($image_action == 'report') {
					$reportTemplate = Report::getModel($this->config['report_template']);
					if ($reportTemplate) {
						if (!empty($reportTemplate['simple_search'])) {
							$link_options['data-simple-search'] = $reportTemplate['simple_search'];
						} elseif (!empty($reportTemplate['multi_search'])) {
							$link_options['data-multi-search'] = $reportTemplate['multi_search'];
						}

						if ($reportTemplate['batch_query_name']) {
							$customQuery = CommandData::getModel($reportTemplate['batch_query_name'], [
								'lib_name' => 'CodiacSDK.CommonArea',
								'func_name' => 'GetCustomQueryList'
							]);
							if ($customQuery) {
								$link_options['data-batch-query'] = $customQuery;
							}
						}
					}

					$link_options['data-report-template'] = $this->config['report_template'];
					$link_options['data-report-name'] = isset($reportTemplate['description']) ? $reportTemplate['description'] : null;
				}

				if ($image_action == 'execute' && !empty($this->config['execute_function_custom'])) {
					if ($customFunctions = Json::decode($this->config['execute_function_custom'])) {
						$customFunctions = explode(';', $customFunctions);
						if (count($customFunctions) > 1) {
							$preFunction = !empty($this->config['execute_function_pre']) ? $this->config['execute_function_pre'] : '{}';
							$postFunction = !empty($this->config['execute_function_post']) ? $this->config['execute_function_post'] : '{}';
							$subId = ($this->isGridField) ? "{id: '$this->dataId'}" : "null";

							$this->view->registerJs("
								$('#{$image_identifier}').on('click', function () {
									common.triggerExecute({$this->config['execute_function_get']}, ['$customFunctions[0]', '$customFunctions[1]'], $preFunction, $postFunction, $subId);
								});
							");
						}
					}
				} else {
					$this->view->registerJs("$('#{$image_identifier}').on('click', function (event) {common.triggerAction('{$image_action}', event.target)});");
				}

				if($image_action == 'document') {
					//echo '<pre>'; print_r($this->tmpData);

					$selected_document_category = array();
					$document_category = array();
					$document_family = '';

					$document_category_list = GetListList::getDocumentCategory($this->config['field_btn_document_family']);
					//echo '<pre>'; print_r($document_category_list);

					if(!empty($this->config['field_btn_document_category'])) {
						//echo '<pre>'; print_r($this->config['field_btn_document_category'][0]);

						foreach($document_category_list as $key => $val) {
							$document_family = $val['family_name'];

							array_push($document_category, $val['category']);

							if(in_array($val['category'], $this->config['field_btn_document_category'][0])) {
								//print_r(array_keys($val, $val['key_part_1']));

								if($val['key_part_1'] != '')
									$selected_document_category['KP1'][] = $this->tmpData[$val['key_part_1']];
									//$selected_document_category['KP1'][] = $val['key_part_1'];

								if($val['key_part_2'] != '')
									$selected_document_category['KP2'][] = $this->tmpData[$val['key_part_2']];
									//$selected_document_category['KP2'][] = $val['key_part_2'];

								if($val['key_part_3'] != '')
									$selected_document_category['KP3'][] = $this->tmpData[$val['key_part_3']];

								if($val['key_part_4'] != '')
									$selected_document_category['KP4'][] = $this->tmpData[$val['key_part_4']];

								if($val['key_part_5'] != '')
									$selected_document_category['KP5'][] = $this->tmpData[$val['key_part_5']];
							}
						}

						//echo '<pre>'; print_r($selected_document_category);

						//$field_btn_document_category = implode(',', $this->config['field_btn_document_category'][0]);
						$link_options['data-document-kp'] = json_encode($selected_document_category);
						$link_options['data-document-category'] = json_encode($document_category);
						$link_options['data-document-family'] = $document_family;
						$link_options['data-id'] = $image_identifier;
						$link_options['data-document-download-url'] = Url::to(['/file/document-download'], true);
					}
				}
			}

			if (isset($this->internationalization['dimage_value'][Yii::$app->language])) {
				$this->config['dimage_value'] = $this->internationalization['dimage_value'][Yii::$app->language];
			}

			if(isset($this->config['dimage_identifier']) && $this->config['dimage_identifier'] != '') {
				Html::addCssStyle($options, ['width' => '100%']);
				Html::addCssStyle($options, ['padding' => '6px 12px']);

				if($this->value != '') {
					$image_name = $this->value;

					if(isset($this->config['allow_full_size_image']) && $this->config['allow_full_size_image'] == 'Y')
						return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", ["class" => "common_image_button_class"]);
					else
						return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", $link_options);
				} else {
					return Html::a(Html::img("", ['class' => 'glyphicon glyphicon-picture']), "javascript:void(0);", $link_options);
				}
			} else if(isset($this->config['tbl_image_identifier']) && $this->config['tbl_image_identifier'] != '') {
				//echo 'tbl_image_identifier';

				Html::addCssStyle($options, ['width' => $this->config['field_width_value']]);
				Html::addCssStyle($options, ['height' => $this->config['field_height_value']]);

				$image_name = $this->value;

				if(isset($this->config['tbl_allow_full_size_image']) && $this->config['tbl_allow_full_size_image'] == 'Y')
					return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", ["class" => "common_image_button_class"]);
				else
					return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", $link_options);
			}
		}
	}

    /**
     * @return string - HTML code of field
     * @throws \Exception
     */
    private function getField()
    {
		//echo 'in getField';
		//echo '<pre>'; print_r($this->config);

        $options = $this->getFieldOptions();
        if ($this->mode === RenderTabHelper::MODE_COPY &&
            $this->config['copyable_field'] === self::PROPERTY_FALSE &&
            !in_array($this->config['field_type'], [
                RenderTabHelper::SECTION_TYPE_CHART_BAR_HORIZONTAL,
                RenderTabHelper::SECTION_TYPE_CHART_BAR_VERTICAL,
                RenderTabHelper::SECTION_TYPE_CHART_DOUGHNUT,
                RenderTabHelper::SECTION_TYPE_CHART_LINE,
                RenderTabHelper::SECTION_TYPE_CHART_PIE
            ])
        ) {
            $this->value = '';
        }
        if ($this->config['field_type'] == self::TYPE_MULTI_SELECT || $this->config['field_type'] == self::TYPE_SELECT || $this->config['field_type'] == self::TYPE_SELECT_WITH_EXTENSION_FUNCTION) {
            return $this->getSelectField($options, $this->config['field_type']);
        } elseif ($this->config['field_type'] == self::TYPE_CHECKBOX) {
            return $this->getCheckboxField($options);
        } elseif ($this->config['field_type'] == self::TYPE_RADIO) {
            return $this->getRadioField($options);	
        } elseif ($this->config['field_type'] == self::TYPE_NUMERIC && $this->config['format_type'] == _FormattedHelper::CURRENCY_NUMERIC_FORMAT) {
            return $this->getCurrencyField($options);
        } elseif ($this->config['field_type'] == self::TYPE_NUMERIC && $this->config['format_type'] == _FormattedHelper::DECIMAL_NUMERIC_FORMAT) {
            return $this->getDecimalField($options);
		} else if($this->config['field_type'] == self::TYPE_NUMERIC) {
			return $this->getNumericField($options);
        } elseif (($this->config['field_type'] == self::TYPE_TEXT || $this->config['field_type'] == self::TYPE_WITH_DEPENDENT_FIELD) && $this->config['format_type'] == _FormattedHelper::DATE_TEXT_FORMAT) {
            return $this->getDatePicker($options);
        } elseif (($this->config['field_type'] == self::TYPE_TEXT || $this->config['field_type'] == self::TYPE_WITH_DEPENDENT_FIELD) && $this->config['format_type'] == _FormattedHelper::DATE_TIME_TEXT_FORMAT) {
            return $this->getDateTimePicker($options);
        } elseif ($this->config['field_type'] == self::TYPE_DOCUMENT) {
            return $this->getDocumentField($options);
        } elseif ($this->config['field_type'] == self::TYPE_INLINE_SEARCH) {
            return $this->getInlineSearchField($options);
        } elseif ($this->config['field_type'] == self::TYPE_DATALIST) {
            return $this->getDatalistField($options);
        } elseif ($this->config['field_type'] == self::TYPE_ALERT) {
            return $this->getAlertField($options);
        } elseif ($this->config['field_type'] == self::TYPE_BUTTON) {
            return $this->getButtonField($options);
		} elseif ($this->config['field_type'] == self::TYPE_IMAGE) {
            return $this->getImageField($options);
		} elseif ($this->config['field_type'] == self::TYPE_IMAGE_DYNAMIC) {
            return $this->getDImageField($options);
        } elseif ($this->config['field_type'] == self::TYPE_TEXTAREA) {
            return $this->getTextAreaField($options);
        } elseif ($this->config['field_type'] == self::TYPE_DATALIST_RELATION) {
            return $this->getDataListRelationField($options);
        } else {
			if(strpos($this->dataField, 'image') !== false) {
				Html::removeCssClass($options, 'form-control');

				//echo '<pre>'; print_r($options);

				$options = ['style' => ['max-width' => 'none']];

				if (isset($this->internationalization['value'][Yii::$app->language])) {
					$this->config['value'] = $this->internationalization['value'][Yii::$app->language];
				}

				if(isset($this->layout_config->textarea[$this->dataField])) {
					$image_data_json = $this->layout_config->textarea[$this->dataField];
					$image_data = json_decode($image_data_json);

					//echo '<pre>'; print_r($image_data);

					$options['title'] = $this->value;
					$options['alt'] = $this->value;

					$options['width'] = $image_data->image_width.'px';
					$options['height'] = $image_data->image_height.'px';

					$image_identifier = $image_data->image_custom_id;
					$image_action = $image_data->image_action;

					$image_allow_full_size = $image_data->image_allow_full_size;

					$options['id'] = $image_identifier;
					$link_options['id'] = $image_identifier;

					if (in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]) && $this->mode == $image_action) {
						Html::removeCssClass($options, 'screen-btn-custom-action');

						$buttons[] = Html::button(\yii\bootstrap\Html::icon('floppy-disk'), ['class' => 'btn btn-success', 'id' => "{$image_identifier}_{$image_action}_save"]);
						$buttons[] = Html::button(\yii\bootstrap\Html::icon('remove-circle'), ['class' => 'btn btn-danger', 'id' => "{$image_identifier}_{$image_action}_cancel"]);

						$this->view->registerJs("$('#{$image_identifier}_{$image_action}_save').on('click', function () {common.triggerSpecialAction('{$image_action}', 'save')});");
						$this->view->registerJs("$('#{$image_identifier}_{$image_action}_cancel').on('click', function () {common.triggerSpecialAction('{$image_action}', 'cancel')});");

						return Html::tag('div', implode('', $buttons), ['class' => 'btn-group']);
					}

					if (isset($image_action) && $image_action) {
						if ($image_action == 'report') {
							$reportTemplate = Report::getModel($this->config['report_template']);
							if ($reportTemplate) {
								if (!empty($reportTemplate['simple_search'])) {
									$link_options['data-simple-search'] = $reportTemplate['simple_search'];
								} elseif (!empty($reportTemplate['multi_search'])) {
									$link_options['data-multi-search'] = $reportTemplate['multi_search'];
								}

								if ($reportTemplate['batch_query_name']) {
									$customQuery = CommandData::getModel($reportTemplate['batch_query_name'], [
										'lib_name' => 'CodiacSDK.CommonArea',
										'func_name' => 'GetCustomQueryList'
									]);
									if ($customQuery) {
										$link_options['data-batch-query'] = $customQuery;
									}
								}
							}

							$link_options['data-report-template'] = $this->config['report_template'];
							$link_options['data-report-name'] = isset($reportTemplate['description']) ? $reportTemplate['description'] : null;
						}

						if ($image_action == 'execute' && !empty($this->config['execute_function_custom'])) {
							if ($customFunctions = Json::decode($this->config['execute_function_custom'])) {
								$customFunctions = explode(';', $customFunctions);
								if (count($customFunctions) > 1) {
									$preFunction = !empty($this->config['execute_function_pre']) ? $this->config['execute_function_pre'] : '{}';
									$postFunction = !empty($this->config['execute_function_post']) ? $this->config['execute_function_post'] : '{}';
									$subId = ($this->isGridField) ? "{id: '$this->dataId'}" : "null";

									$this->view->registerJs("
										$('#{$image_identifier}').on('click', function () {
											common.triggerExecute({$this->config['execute_function_get']}, ['$customFunctions[0]', '$customFunctions[1]'], $preFunction, $postFunction, $subId);
										});
									");
								}
							}
						} else {
							$this->view->registerJs("$('#{$image_identifier}').on('click', function (event) {common.triggerAction('{$image_action}', event.target)});");
						}

						if($image_action == 'document') {
							//echo '<pre>'; print_r($this->tmpData);

							$selected_document_category = array();
							$document_category = array();
							$document_family = '';

							$document_category_list = GetListList::getDocumentCategory($this->config['field_btn_document_family']);
							//echo '<pre>'; print_r($document_category_list);

							if(!empty($this->config['field_btn_document_category'])) {
								//echo '<pre>'; print_r($this->config['field_btn_document_category'][0]);

								foreach($document_category_list as $key => $val) {
									$document_family = $val['family_name'];

									array_push($document_category, $val['category']);

									if(in_array($val['category'], $this->config['field_btn_document_category'][0])) {
										//print_r(array_keys($val, $val['key_part_1']));

										if($val['key_part_1'] != '')
											$selected_document_category['KP1'][] = $this->tmpData[$val['key_part_1']];
											//$selected_document_category['KP1'][] = $val['key_part_1'];

										if($val['key_part_2'] != '')
											$selected_document_category['KP2'][] = $this->tmpData[$val['key_part_2']];
											//$selected_document_category['KP2'][] = $val['key_part_2'];

										if($val['key_part_3'] != '')
											$selected_document_category['KP3'][] = $this->tmpData[$val['key_part_3']];

										if($val['key_part_4'] != '')
											$selected_document_category['KP4'][] = $this->tmpData[$val['key_part_4']];

										if($val['key_part_5'] != '')
											$selected_document_category['KP5'][] = $this->tmpData[$val['key_part_5']];
									}
								}

								//echo '<pre>'; print_r($selected_document_category);

								//$field_btn_document_category = implode(',', $this->config['field_btn_document_category'][0]);
								$link_options['data-document-kp'] = json_encode($selected_document_category);
								$link_options['data-document-category'] = json_encode($document_category);
								$link_options['data-document-family'] = $document_family;
								$link_options['data-id'] = $image_identifier;
								$link_options['data-document-download-url'] = Url::to(['/file/document-download'], true);
							}
						}
					}

					if($image_data->image_type == 'image') {
						$image = $image_data->image_info;

						if($image_allow_full_size == 'Y')
							return Html::a(Html::img("data:image/jpeg;base64,$image", $options), "javascript:void(0);", ["class" => "common_image_button_class"]);
						else
							return Html::a(Html::img("data:image/jpeg;base64,$image", $options), "javascript:void(0);", $link_options);
					} else {
						//echo 'test'.$this->value;

						$field_name = $this->getFieldName();

						$options['id'] = $image_identifier.$this->cnt;

						if($this->mode === RenderTabHelper::MODE_EDIT || $this->mode === RenderTabHelper::MODE_INSERT) {
							$inputType = 'hidden';
							$input = '<input type="file" class="common_field_upload_image_class" id="file_'.$options['id'].'" data-input-id="'.$options['id'].'" style="display: none;" /><a href="javascript: void(0);" onclick="$(\'#file_'.$options['id'].'\').trigger(\'click\'); return false;"><img src="" class="glyphicon glyphicon-picture"></a>&nbsp;&nbsp;&nbsp;&nbsp;<img src="" id="image_'.$options['id'].'" style="width: auto; height: 60px; display: none;">';
							$input .= Html::input($inputType, $field_name.$this->cnt, $this->value, $options);

							return Html::tag('div', $input);
						} else {
							if($this->value != '') {
								Html::addCssStyle($options, ['width' => '100%']);
								Html::addCssStyle($options, ['padding' => '6px 12px']);

								$image_name = $this->value;

								if(isset($this->config['allow_full_size_image']) && $this->config['allow_full_size_image'] == 'Y')
									return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", ["class" => "common_image_button_class"]);
								else
									return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", $link_options);

									//return Html::tag('div', '<img src="data:image/jpeg;base64,'.$this->value.'" class="img" style="width: '.$img_width.';">');
									//return Html::img("data:image/jpeg;base64,".$this->value, $options);
									//return Html::tag('div', '<img src="data:image/jpeg;base64,'.$this->value.'" class="img" style="width: auto; height: 60px;">');
							} else {
								return Html::a(Html::img("", ['class' => 'glyphicon glyphicon-picture']), "javascript:void(0);", $link_options);

								//return Html::tag('div', '<img src="" class="glyphicon glyphicon-picture">');
							}
						}
					}
				}
			} else {
				return $this->getTextField($options, $this->config['format_type']);
			}
		}
    }

    private function getFileData()
    {
        if (empty($this->config['is_viewer'])) {
            if (empty($this->config['field_document_family']) || empty($this->config['field_document_category'])) {
                return Html::tag('div', 'Access denied', ['style' => 'color: red; padding: 7px 0 0 0;']);
            }

            $accessRight = DocumentGroup::getAccessPermission($this->config['field_document_family'], $this->config['field_document_category']);
            if ($accessRight != DocumentGroup::ACCESS_RIGHT_FULL && $accessRight != DocumentGroup::ACCESS_RIGHT_READ) {
                return Html::tag('div', 'Access denied', ['style' => 'color: red; padding: 7px 0 0 0;']);
            }
        }

        $link = null;
        $options = ['class' => 'btn btn-default download-file-link'];

        if ($fileContainer = FileModel::getFileContainer($this->value)) {
            $options['data-pk'] = $fileContainer['id'];
            if (!empty($this->config['related_frame_class'])) {
                $options['data-related-frame-class'] = $this->config['related_frame_class'];
            }

            $fileHashHex = bin2hex(base64_decode($fileContainer['original_file_hash']));
            $fileInfo = pathinfo($fileContainer['original_file_name']);
            $fileRoot = DIRECTORY_SEPARATOR . $fileHashHex . '.' . $fileInfo['extension'];
            $filePath = FileModel::getDirectory('@webroot') . $fileRoot;

            if (file_exists($filePath)) {
                $link = FileModel::getDirectory('@web') . $fileRoot;
                $options['download'] = $fileContainer['original_file_name'];
                Html::addCssClass($options, 'is-cached');
            }
        } else {
            return Html::tag('div', 'Can\'t find file container', ['style' => 'color: red; padding: 7px 0 0 0;']);
        }

        $btnText = ($link) ? 'Download file' : 'Download from API server';

        $progress = Html::tag('div', null, ['class' => 'progress-inner']);
        $icon = Html::tag('span', null, ['class' => 'glyphicon glyphicon-arrow-down']);
        $text = Html::tag('span', $btnText, ['class' => 'container-text-inner']);
        $iconContainer = Html::tag('div', $icon . ' ' . $text, ['class' => 'container-icon-inner']);

        return Html::a($iconContainer . $progress, $link, $options);
    }

    private function getAlertData() {
        if (($data = Json::decode($this->value)) && !empty($data['type'])) {
            $options = [];
            if (!empty($data['message'])) {
                $options['title'] = $data['message'];
            }

            switch ($data['type']) {
                case self::TYPE_ALERT_SUCCESS:
                    Html::addCssClass($options, ['glyphicon', 'glyphicon-ok-circle', 'grid-alert grid-alert-success']);
                    break;
                case self::TYPE_ALERT_WARNING:
                    Html::addCssClass($options, ['glyphicon', 'glyphicon-warning-sign', 'grid-alert grid-alert-warning']);
                    break;
                case self::TYPE_ALERT_ERROR:
                    Html::addCssClass($options, ['glyphicon', 'glyphicon-ban-circle', 'grid-alert grid-alert-danger']);
                    break;
            }

            return  Html::tag('span', null, $options);
        }

        return null;
    }

    /**
     * @return null|string - HTML code of formatted data
     */
    private function getFormattedData()
    {
		//echo 'in getFormattedData';
		//echo '<pre>'; print_r($this->config);

		//echo '<pre>'; print_r($this->layout_config);
		//echo '<pre>'; print_r($this->dataField);
		//echo $this->config['field_type'];

        $data = '';
        $options = $this->getBasicStyles();

        if ($this->config['field_type'] == self::TYPE_SELECT) {
            if ($listRow = GetListList::getByListName($this->value, $this->config['list_name'])) {
                $data = $listRow['description'];
            } else {
                $data = $this->value;
            }
        } elseif (($this->config['field_type'] == self::TYPE_MULTI_SELECT) || (isset($this->config['multi-select-type']) && $this->config['multi-select-type'] == self::PROPERTY_TRUE)) {
            $valuesList = explode(self::MULTI_SELECT_DELIMITER, $this->value);
            if (!empty($valuesList)) {
                foreach ($valuesList as $value) {
                    $dataFromList = GetListList::getByListName($value, $this->config['list_name']);
                    $data .= empty($data) ? $dataFromList['description'] : ('<br/>' . $dataFromList['description']);
                }
            } else {
                $data = $this->value;
            }
        } elseif ($this->config['field_type'] == self::TYPE_CHECKBOX) {
            $data = Html::input('checkbox', null, (int)!empty($this->value), [
                'checked' => !empty($this->value),
                'disabled' => true
            ]);
        } elseif ($this->config['field_type'] == self::TYPE_RADIO) {
            $data = Html::input('radio', null, (int)!empty($this->value), [
                'checked' => !empty($this->value),
                'disabled' => true
            ]);
        } elseif ($this->config['field_type'] == self::TYPE_DOCUMENT) {
            $data = self::getFileData();
        } elseif ($this->config['field_type'] == self::TYPE_ALERT) {
            $data = self::getAlertData();
        } elseif ($this->config['field_type'] == self::TYPE_LINK) {
            if ($this->config['format_type'] == self::TYPE_FORMAT_LINK_LOCAL) {
                $url = ['/screen/index',
                    'menu' => $this->config['field_link_menu'],
                    'screen' => $this->config['field_group_screen_link'],
                    '#' => 'tab=' . $this->config['field_screen_link'] . '&search[' . $this->config['field_settings_link'] . ']=' . $this->value,
                    'isFrame' => $this->config['type-link'] == '_modal'
                ];
            } else {
                $url = $this->value;
            }

            $data =  Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, [
                'target' => $this->config['type-link'],
                'class' => 'field-custom-link'
            ]);

			$new_options = array();
			$new_options = ['class' => '', 'id' => (is_null($this->dataId)) ? $this->getFieldID() : null];

			$new_options['class'] .= ($this->isGridField) ? 'form-control-grid' : '';
			$new_options['class'] .= (isset($this->config['key_field']) && ($this->config['key_field'] == self::PROPERTY_TRUE)) ? ' form-control-key' : '';

			$new_options = array_merge($new_options, $this->getFieldFuncData());
			$data .= Html::input('hidden', $this->getFieldName(), $this->value, $new_options);
        } elseif ($this->config['field_type'] == self::TYPE_BUTTON) {
			if(strpos($this->dataField, 'image') !== false) {
				if (isset($this->internationalization['value'][Yii::$app->language])) {
					$this->config['value'] = $this->internationalization['value'][Yii::$app->language];
				}

				$image_data_json = $this->layout_config->textarea[$this->dataField];
				$image_data = json_decode($image_data_json);

				//echo '<pre>'; print_r($image_data);

				$options['title'] = $this->value;
				$options['alt'] = $this->value;
				$options['width'] = $image_data->image_width;
				$options['height'] = $image_data->image_height;

				$image = $image_data->image_info;

				return Html::img("data:image/jpeg;base64,$image", $options);

				//Html::img("data:image/jpeg;base64,$image", ['width' => 'auto', 'height' => '60px']);
				//echo '<pre>'; print_r($this->layout_config);
			} else {
				return $this->getButtonField($this->getFieldOptions());
			}
		} elseif ($this->config['field_type'] == self::TYPE_IMAGE) {
            return $this->getImageField($options);
		} elseif ($this->config['field_type'] == self::TYPE_IMAGE_DYNAMIC) {
            return $this->getDImageField($options);
        } elseif (!empty($this->config['format_type'])) {
            if (empty($this->value) && !empty($this->config['allow_empty'])) {
                $data = '';
            } else {
                $additionalParam = null;

                if($this->config['link_type'] == 'Y' && $this->mode == '') {
                    if($this->value != '') {
                        if($this->config['field_pass_through_link'] != '') {
                            //echo "<pre>"; print_r($this->tmpData);
    
                            //explode original dataField with . to get last element
                            $explode_dataField = explode('.', $this->dataField);
        
                            //explode last element with _ so we can attach new field name to it
                            $explode_fieldName = explode('_', end($explode_dataField));
        
                            //Create new field name to push in original array
                            $new_fieldName = $this->config['field_pass_through_link'];
    
                            //$new_fieldName = $explode_fieldName[0].'_'.$this->config['field_pass_through_link'];

                            //remove last element from original dataField
                            array_pop($explode_dataField);
        
                            //push new value to array at end to original dataField
                            array_push($explode_dataField, $new_fieldName);
        
                            //Join all array values with . again to make new dataField.
                            $implode_dataField = implode('.', $explode_dataField);
        
                            if($this->layout_type == 'grid')
                                $value = $this->tmpData[$this->cnt][$implode_dataField];
                            else
                                $value = $this->tmpData[$implode_dataField];
                        } else {
                            $value = $this->value;
                        }

                        $url = ['/screen/index',
                            'menu' => $this->config['field_link_menu'],
                            'screen' => $this->config['field_group_screen_link'],
                            '#' => 'tab=' . $this->config['field_screen_link'] . '&search[' . $this->config['field_settings_link'] . ']=' . $value,
                            'isFrame' => $this->config['type-link'] == '_modal'
                        ];
        
                        $input = Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, ['target' => $this->config['type-link'], 'class' => 'field-custom-link']);
        
                        if(isset($this->config['data_field']) && $this->config['data_field'] != '')
                            $data = Html::tag('div', $input, ['style' => 'background-color: #eee; padding: 6px; border-radius: 4px;']);
                        else
                            $data = Html::tag('div', $input, ['style' => 'padding: 6px; border-radius: 4px;']);
                    } else {
                        $data = '';
                    }
                } else {
                    if (($this->config['format_type'] == _FormattedHelper::DECIMAL_NUMERIC_FORMAT && !empty($this->config['numeric_field_decimal']))) {
                        $additionalParam = $this->config['numeric_field_decimal'];

						$data = $this->Formatted->run($this->value, $this->config['format_type'], $additionalParam);
                    }

                    if($this->config['link_type'] == 'N' && $this->mode == '') {
						if(strpos($this->dataField, 'image') !== false) {
							//echo '<pre>'; print_r($this->layout_config);

							if (isset($this->internationalization['value'][Yii::$app->language])) {
								$this->config['value'] = $this->internationalization['value'][Yii::$app->language];
							}

							$image_data_json = $this->layout_config->textarea[$this->dataField];
							$image_data = json_decode($image_data_json);

							//echo '<pre>'; print_r($image_data);

							$options['title'] = $this->value;
							$options['alt'] = $this->value;

							$options['width'] = $image_data->image_width.'px';
							$options['height'] = $image_data->image_height.'px';

							$image_identifier = $image_data->image_custom_id;
							$image_action = $image_data->image_action;

							$image_allow_full_size = $image_data->image_allow_full_size;

							$options['id'] = $image_identifier;
							$link_options['id'] = $image_identifier;

							if (in_array($this->mode, [RenderTabHelper::MODE_EDIT, RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_COPY]) && $this->mode == $image_action) {
								Html::removeCssClass($options, 'screen-btn-custom-action');

								$buttons[] = Html::button(\yii\bootstrap\Html::icon('floppy-disk'), ['class' => 'btn btn-success', 'id' => "{$image_identifier}_{$image_action}_save"]);
								$buttons[] = Html::button(\yii\bootstrap\Html::icon('remove-circle'), ['class' => 'btn btn-danger', 'id' => "{$image_identifier}_{$image_action}_cancel"]);

								$this->view->registerJs("$('#{$image_identifier}_{$image_action}_save').on('click', function () {common.triggerSpecialAction('{$image_action}', 'save')});");
								$this->view->registerJs("$('#{$image_identifier}_{$image_action}_cancel').on('click', function () {common.triggerSpecialAction('{$image_action}', 'cancel')});");

								return Html::tag('div', implode('', $buttons), ['class' => 'btn-group']);
							}

							if (isset($image_action) && $image_action) {
								if ($image_action == 'report') {
									$reportTemplate = Report::getModel($this->config['report_template']);
									if ($reportTemplate) {
										if (!empty($reportTemplate['simple_search'])) {
											$link_options['data-simple-search'] = $reportTemplate['simple_search'];
										} elseif (!empty($reportTemplate['multi_search'])) {
											$link_options['data-multi-search'] = $reportTemplate['multi_search'];
										}

										if ($reportTemplate['batch_query_name']) {
											$customQuery = CommandData::getModel($reportTemplate['batch_query_name'], [
												'lib_name' => 'CodiacSDK.CommonArea',
												'func_name' => 'GetCustomQueryList'
											]);
											if ($customQuery) {
												$link_options['data-batch-query'] = $customQuery;
											}
										}
									}

									$link_options['data-report-template'] = $this->config['report_template'];
									$link_options['data-report-name'] = isset($reportTemplate['description']) ? $reportTemplate['description'] : null;
								}

								if ($image_action == 'execute' && !empty($this->config['execute_function_custom'])) {
									if ($customFunctions = Json::decode($this->config['execute_function_custom'])) {
										$customFunctions = explode(';', $customFunctions);
										if (count($customFunctions) > 1) {
											$preFunction = !empty($this->config['execute_function_pre']) ? $this->config['execute_function_pre'] : '{}';
											$postFunction = !empty($this->config['execute_function_post']) ? $this->config['execute_function_post'] : '{}';
											$subId = ($this->isGridField) ? "{id: '$this->dataId'}" : "null";

											$this->view->registerJs("
												$('#{$image_identifier}').on('click', function () {
													common.triggerExecute({$this->config['execute_function_get']}, ['$customFunctions[0]', '$customFunctions[1]'], $preFunction, $postFunction, $subId);
												});
											");
										}
									}
								} else {
									$this->view->registerJs("$('#{$image_identifier}').on('click', function (event) {common.triggerAction('{$image_action}', event.target)});");
								}

								if($image_action == 'document') {
									//echo '<pre>'; print_r($this->tmpData);

									$selected_document_category = array();
									$document_category = array();
									$document_family = '';

									$document_category_list = GetListList::getDocumentCategory($this->config['field_btn_document_family']);
									//echo '<pre>'; print_r($document_category_list);

									if(!empty($this->config['field_btn_document_category'])) {
										//echo '<pre>'; print_r($this->config['field_btn_document_category'][0]);

										foreach($document_category_list as $key => $val) {
											$document_family = $val['family_name'];

											array_push($document_category, $val['category']);

											if(in_array($val['category'], $this->config['field_btn_document_category'][0])) {
												//print_r(array_keys($val, $val['key_part_1']));

												if($val['key_part_1'] != '')
													$selected_document_category['KP1'][] = $this->tmpData[$val['key_part_1']];
													//$selected_document_category['KP1'][] = $val['key_part_1'];

												if($val['key_part_2'] != '')
													$selected_document_category['KP2'][] = $this->tmpData[$val['key_part_2']];
													//$selected_document_category['KP2'][] = $val['key_part_2'];

												if($val['key_part_3'] != '')
													$selected_document_category['KP3'][] = $this->tmpData[$val['key_part_3']];

												if($val['key_part_4'] != '')
													$selected_document_category['KP4'][] = $this->tmpData[$val['key_part_4']];

												if($val['key_part_5'] != '')
													$selected_document_category['KP5'][] = $this->tmpData[$val['key_part_5']];
											}
										}

										//echo '<pre>'; print_r($selected_document_category);

										//$field_btn_document_category = implode(',', $this->config['field_btn_document_category'][0]);
										$link_options['data-document-kp'] = json_encode($selected_document_category);
										$link_options['data-document-category'] = json_encode($document_category);
										$link_options['data-document-family'] = $document_family;
										$link_options['data-id'] = $image_identifier;
										$link_options['data-document-download-url'] = Url::to(['/file/document-download'], true);
									}
								}
							}

							//echo $image_data->image_type;

							if($image_data->image_type == 'image') {
								$image = $image_data->image_info;

								if($image_allow_full_size == 'Y')
									return Html::a(Html::img("data:image/jpeg;base64,$image", $options), "javascript:void(0);", ["class" => "common_image_button_class"]);
								else
									return Html::a(Html::img("data:image/jpeg;base64,$image", $options), "javascript:void(0);", $link_options);
							} else {
								//echo $this->cnt.'test'.$this->value;

								$options['id'] = $image_identifier.$this->cnt;

								//echo '<pre>'; print_r($this->config);

								if($this->mode === RenderTabHelper::MODE_EDIT || $this->mode === RenderTabHelper::MODE_INSERT) {
									$field_name = $this->getFieldName();

									$inputType = 'hidden';
									$input = '<input type="file" class="common_field_upload_image_class" id="file_'.$options['id'].'" data-input-id="'.$options['id'].'" style="display: none;" /><a href="javascript: void(0);" onclick="$(\'#file_'.$options['id'].'\').trigger(\'click\'); return false;"><img src="" class="glyphicon glyphicon-picture"></a>&nbsp;&nbsp;&nbsp;&nbsp;<img src="" id="image_'.$options['id'].'" style="width: auto; height: 60px; display: none;">';
									$input .= Html::input($inputType, $field_name.$this->cnt, $this->value, $options);

									return Html::tag('div', $input);
								} else {
									if($this->value != '') {
										Html::addCssStyle($options, ['width' => '100%']);
										Html::addCssStyle($options, ['padding' => '6px 12px']);

										$image_name = $this->value;

										if(isset($this->config['allow_full_size_image']) && $this->config['allow_full_size_image'] == 'Y')
											return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", ["class" => "common_image_button_class"]);
										else
											return Html::a(Html::img("data:image/jpeg;base64,$image_name", $options), "javascript:void(0);", $link_options);

											//return Html::tag('div', '<img src="data:image/jpeg;base64,'.$this->value.'" class="img" style="width: '.$img_width.';">');
											//return Html::img("data:image/jpeg;base64,".$this->value, $options);
											//return Html::tag('div', '<img src="data:image/jpeg;base64,'.$this->value.'" class="img" style="width: auto; height: 60px;">');
									} else {
										return Html::a(Html::img("", ['class' => 'glyphicon glyphicon-picture']), "javascript:void(0);", $link_options);

										//return Html::tag('div', '<img src="" class="glyphicon glyphicon-picture">');
									}
								}
							}
						} else {
							if($this->dataField == 'Array.c_Task.CreatedDate') {
								//$data = date('d-m-Y h:i:s', strtotime($this->value));
								$data = $this->Formatted->run($this->value, _FormattedHelper::DATE_TIME_TEXT_FORMAT);
							} else if($this->dataField == 'Array.c_Task.TaskDescription') {
								$value = $this->value;
								$col_name = key($this->tmpData[$this->cnt]['Array.c_Task.TaskKey']);
								$col_val = $this->tmpData[$this->cnt]['Array.c_Task.TaskKey']["$col_name"];
								$menu_name = $this->tmpData[$this->cnt]['menu_name'][0];
								$screen_name = $this->tmpData[$this->cnt]['screen_name'][0];
								$tabID = $this->tmpData[$this->cnt]['screen_id'][0];

								$url = ['/screen/index',
									'menu' => $menu_name,
									'screen' => $screen_name,
									'#' => 'tab=' . $tabID . '&search[' . $col_name . ']=' . $col_val,
									'isFrame' => $this->config['type-link'] == '_modal'
								];

								$input = Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, ['target' => $this->config['type-link'], 'class' => 'field-custom-link']);
				
								if(isset($this->config['data_field']) && $this->config['data_field'] != '')
									$data = Html::tag('div', $input, ['style' => 'background-color: #eee; padding: 6px; border-radius: 4px;']);
								else
									$data = Html::tag('div', $input, ['style' => 'padding: 6px; border-radius: 4px;']);
							} else {
								$data = $this->value;
							}

							$new_options = array();
							$new_options = ['class' => '', 'id' => (is_null($this->dataId)) ? $this->getFieldID() : null];

							$new_options['class'] .= ($this->isGridField) ? 'form-control-grid' : '';
							$new_options['class'] .= (isset($this->config['key_field']) && ($this->config['key_field'] == self::PROPERTY_TRUE)) ? ' form-control-key' : '';

							$new_options = array_merge($new_options, $this->getFieldFuncData());
							$data .= Html::input('hidden', $this->getFieldName(), $this->value, $new_options);
						}
					}
                }
            }
        } else {
            if($this->config['link_type'] == 'Y' && $this->mode == '') {
                if($this->value != '') {
                    if($this->config['field_pass_through_link'] != '') {
                        //echo "<pre>"; print_r($this->tmpData);

                        //explode original dataField with . to get last element
                        $explode_dataField = explode('.', $this->dataField);
    
                        //explode last element with _ so we can attach new field name to it
                        $explode_fieldName = explode('_', end($explode_dataField));
    
                        //Create new field name to push in original array
                        $new_fieldName = $this->config['field_pass_through_link'];

                        //$new_fieldName = $explode_fieldName[0].'_'.$this->config['field_pass_through_link'];
    
                        //remove last element from original dataField
                        array_pop($explode_dataField);
    
                        //push new value to array at end to original dataField
                        array_push($explode_dataField, $new_fieldName);
    
                        //Join all array values with . again to make new dataField.
                        $implode_dataField = implode('.', $explode_dataField);
    
                        if($this->layout_type == 'grid')
                            $value = $this->tmpData[$this->cnt][$implode_dataField];
                        else
                            $value = $this->tmpData[$implode_dataField];

						$url = ['/screen/index',
							'menu' => $this->config['field_link_menu'],
							'screen' => $this->config['field_group_screen_link'],
							'#' => 'tab=' . $this->config['field_screen_link'] . '&search[' . $this->config['field_settings_link'] . ']=' . $value,
							'isFrame' => $this->config['type-link'] == '_modal'
						];
                    } else {
                        $value = $this->value;
						$col_name = key($this->tmpData[$this->cnt]['Array.c_Task.TaskKey']);
						$col_val = $this->tmpData[$this->cnt]['Array.c_Task.TaskKey']["$col_name"];
						$menu_name = $this->tmpData[$this->cnt]['menu_name'][0];
						$screen_name = $this->tmpData[$this->cnt]['screen_name'][0];
						$tabID = $this->tmpData[$this->cnt]['screen_id'][0];

						$url = ['/screen/index',
							'menu' => $menu_name,
							'screen' => $screen_name,
							'#' => 'tab=' . $tabID . '&search[' . $col_name . ']=' . $col_val,
							'isFrame' => $this->config['type-link'] == '_modal'
						];
                    }
    
                    $input = Html::a(\yii\bootstrap\Html::icon('link') . ' ' . $this->value, $url, ['target' => $this->config['type-link'], 'class' => 'field-custom-link']);
    
                    if(isset($this->config['data_field']) && $this->config['data_field'] != '')
                        $data = Html::tag('div', $input, ['style' => 'background-color: #eee; padding: 6px; border-radius: 4px;']);
                    else
                        $data = Html::tag('div', $input, ['style' => 'padding: 6px; border-radius: 4px;']);
                } else {
                    $data = '';
                }
            } else {
                $data = $this->value;

				$new_options = array();
				$new_options = ['class' => '', 'id' => (is_null($this->dataId)) ? $this->getFieldID() : null];

				$new_options['class'] .= ($this->isGridField) ? 'form-control-grid' : '';
				$new_options['class'] .= (isset($this->config['key_field']) && ($this->config['key_field'] == self::PROPERTY_TRUE)) ? ' form-control-key' : '';

				$new_options = array_merge($new_options, $this->getFieldFuncData());
				$new_options = array_merge($new_options, ['style' => 'display: none;']);
				$data .= Html::textarea($this->getFieldName(), $this->value, $new_options);;
            }
        }

        if ($this->config['field_type'] == self::TYPE_DATALIST_RELATION) {
            $options['data']['init-value'] = $this->value;
            $options['data']['relation-field--block'] = true;
            $options['data']['custom-query'] = $this->config['custom_query_pk'];
        }

        return Html::tag('div', $data, $options);
    }

    /**
     * @param string $listName
     *
     * @return array - HTML code of input select type
     */
    public function getOptionsList($listName)
    {
        $listData = [];
        $list = GetListList::getData(['list_name' => [$listName]]);

        if (!empty($list->list)) {
            $listData = $list->list;
        }

        return $listData;
    }
}