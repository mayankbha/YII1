<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use Yii;
use app\models\CommandData;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

use app\components\RenderTabHelper;

class RenderListWidget extends BaseRenderWidget
{
    const LABEL_ORIENTATION_LEFT = 'LEFT';
    const LABEL_ORIENTATION_TOP = 'TOP';

    public function run()
    {
        $result = null;

        //echo '<pre>'; print_r(RenderTabHelper::$template);
        //echo '<pre>'; print_r($this->configuration);

        if (!empty($this->configuration->layout_fields) && is_array($this->configuration->layout_fields)) {
			if(RenderTabHelper::$login_screen && RenderTabHelper::$user_details == 1 && $this->configuration->col_num == 5) {
				$result = Html::input('hidden', 'account_status', RenderTabHelper::$account_status, []);
				$result .= Html::input('hidden', 'account_type', RenderTabHelper::$account_type, []);
				$result .= Html::input('hidden', 'tenant_code', RenderTabHelper::$tenant_code, []);
				$result .= Html::input('hidden', 'user_type', RenderTabHelper::$user_type, []);
				$result .= Html::input('hidden', 'default_group', RenderTabHelper::$default_group, []);
				$result .= Html::input('hidden', 'group_membership', RenderTabHelper::$group_membership, []);
				$result .= Html::input('hidden', 'document_groups', RenderTabHelper::$document_groups, []);
				$result .= Html::input('hidden', 'notification_user_type_email_template', RenderTabHelper::$notification_user_type_email_template, []);
				$result .= Html::input('hidden', 'notification_password_type_email_template', RenderTabHelper::$notification_password_type_email_template, []);
			}

			if(RenderTabHelper::$login_screen && $this->configuration->col_num == 5) {
				$result .= Html::input('hidden', 'screen_self_registration_primary_table', RenderTabHelper::$screen_self_registration_primary_table, []);
				$result .= Html::input('hidden', 'screen_self_registration_identify_verification_checkbox', RenderTabHelper::$identify_verification, ['id' => 'screen_self_registration_identify_verification_checkbox']);
			}

			if(RenderTabHelper::$login_screen && $this->configuration->col_num == 5)
				$result .= Html::input('hidden', 'screen_self_registration_account_protection_checkbox', RenderTabHelper::$account_protection, ['id' => 'screen_self_registration_account_protection_checkbox']);

            foreach ($this->configuration->layout_fields as $field) {
                $result = $result . $this->buildField($field);
            }
        }

        $this->view->registerJs("$('.grid-stack').gridstack({staticGrid: true, verticalMargin: 5, cellHeight: 20, float: true})");
        return Html::tag('div', $result, ['class' => 'grid-stack fields-row-wrapper']);
    }

    /**
     * Getting HTML code of field
     *
     * @param array $field
     *
     * @return string
     * @throws \Exception
     */
    private function buildField($field)
    {
		//echo 'in buildField';
		//echo $this->primary_table;

        $config = $this->rebuildFieldConfiguration($field);
        $internationalization = ArrayHelper::map($field, 'name', 'internationalization');

        if (!empty($this->configuration->layout_formatting)) {
            $sectionFormatting = ArrayHelper::map($this->configuration->layout_formatting, 'name', 'value');
            $sectionFormatting = array_filter($sectionFormatting);

            $config = array_filter($config);
            //$config = array_merge($sectionFormatting, $config);
        }

        $template = null;

        //echo '<pre>'; print_r($config);

        $field_type = '';

        if (isset($config['data_field'])) {
            $field_id = $this->getFieldId($config, true);

            $field_type_explode = explode('-', $field_id);

            if(in_array('textarea', $field_type_explode))
                $field_type = 'textarea';
        }

        //$field_id = $this->getFieldTempId($config);

        if (!empty($config['js_event_edit'])) {
            $template = $this->generateJsTemplateField($config['js_event_edit'], $field_id.'_'.RenderTabHelper::$template_id, 'js_event_edit', $field_type);
        }

        if (!empty($config['js_event_insert'])) {
            $template .= $this->generateJsTemplateField($config['js_event_insert'], $field_id.'_'.RenderTabHelper::$template_id, 'js_event_insert', $field_type);
        }

        if (!empty($config['js_event_change'])) {
            $template .= $this->generateJsTemplateField($config['js_event_change'], $field_id.'_'.RenderTabHelper::$template_id, 'change', $field_type);
        }

        if (!empty($config['js_event_onfocus'])) {
            $template .= $this->generateJsTemplateField($config['js_event_onfocus'], $field_id.'_'.RenderTabHelper::$template_id, 'focus', $field_type);
        }

        if ($template) {
            $this->view->registerJs($template);
        }

        $dataField = !empty($config['data_field']) ? CommandData::fixedApiResult($config['data_field'], $this->_alias_framework->enable) : null;
        $value = isset($this->data[$dataField]) ? $this->data[$dataField] : null;

        $widgetConfig = [
            'mode' => $this->mode,
            'libName' => $this->lib_name,
            'value' => $value,
            'dataField' => $dataField,
            'dataAccess' => $this->dataAccess,
            'config' => $config,
            'data_source_get' => $this->configuration->data_source_get,
            'internationalization' => $internationalization,
            'layout_type' => 'list',
            'tmpData' => $this->data,
			'layout_table' => null,
			'readonly' => null,
			'primary_table' => $this->primary_table
        ];
        if ($this->_alias_framework->enable) {
            $widgetConfig['data_source_update'] = $this->_alias_framework->data_source_update;
            $widgetConfig['data_source_delete'] = $this->_alias_framework->data_source_delete;
            $widgetConfig['data_source_create'] = $this->_alias_framework->data_source_insert;
        }

        $result = _FieldsHelper::widget($widgetConfig);

        return $this->buildFieldHelper($result, $config, $internationalization);
    }

    /**
     * Getting HTML code of section fields
     * @param string $field - Field HTML code
     * @param array $config - Configuration for field
     * @param array $internationalization
     * @return string
     */
    private function buildFieldHelper($field, $config, $internationalization = [])
    {
        //if field is hidden and it is neither insert nor edit mode we hide field

		//echo '<pre> $config :: '; print_r($config);

        $style = '';

        if ($this->isHidden($config) && (in_array($this->mode, ['insert', 'edit']) || $this->mode == '')) {
            $style = 'display:none';
        }

        $label = null;
        $leftLabelFieldOptions  = [];
        $labelOptions = $this->getLabelOptions($config);

        if (isset($config['block_height']) && (int)$config['block_height'] > 0) {
            $blockHeight = (int)$config['block_height'];
        } elseif (!empty($config['label_orientation']) && $config['label_orientation'] == self::LABEL_ORIENTATION_TOP) {
            $blockHeight = 3;
        } else {
            $blockHeight = 2;
        }

		if(isset($config['field_status']) && $config['field_status'] == 'self_registration_identify_verification_section_field' && (RenderTabHelper::$identify_verification != 1 || RenderTabHelper::$identify_verification == '' || RenderTabHelper::$identify_verification == null))
			$self_registration_class = 'self_registration_section_identify_verification_common_fields_class';
		else if(isset($config['field_status']) && $config['field_status'] == 'self_registration_account_protection_section_field' && (RenderTabHelper::$account_protection != 1 || RenderTabHelper::$account_protection == '' || RenderTabHelper::$account_protection == null))
			$self_registration_class = 'self_registration_section_account_protection_common_fields_class';
		else
			$self_registration_class = '';

		if(isset($config['field_status']) && $config['field_status'] == 'self_registration_identify_verification_section_field' && RenderTabHelper::$identify_verification == 1 && $config['field_type'] != 'Label')
			if((!isset($config['security_filter1']) && !isset($config['security_filter2']) && !isset($config['security_filter3'])) || ((isset($config['security_filter1']) && $config['security_filter1'] == '') && (isset($config['security_filter2']) && $config['security_filter2'] == '') && (isset($config['security_filter3']) && $config['security_filter3'] == '')))
				$self_registration_class = 'self_registration_section_identify_verification_common_fields_class';

        $divOptions = [
            'class' => 'grid-stack-item field-wrapper '.$self_registration_class,
            'style' => $style,
            'data' => [
                'gs-x' => (isset($config['block_col'])) ? (int)$config['block_col'] : 0,
                'gs-y' => (isset($config['block_row'])) ? (int)$config['block_row'] : 0,
                'gs-width' => (isset($config['block_width'])) ? (int)$config['block_width'] : 12,
                'gs-height' => $blockHeight
            ]
        ];
        Html::addCssClass($divOptions, (in_array($this->mode, [RenderTabHelper::MODE_INSERT, RenderTabHelper::MODE_EDIT]))  ? 'is-edit' : '');

        if($config['field_type'] == 'Image' || $config['field_type'] == 'DImage') {
            $innerContentOption = ['class' => 'grid-stack-item-content', 'style' => 'padding: 7px;'];
        } else {
            $innerContentOption = ['class' => 'grid-stack-item-content', 'style' => 'position: static !important;'];
        }

        if (!empty($config['label_orientation'])) {
            if ($config['label_orientation'] == self::LABEL_ORIENTATION_LEFT) {
                if (!empty($config['label_width'])) {
                    $temp_width = str_replace('px', '', $config['label_width']);

                    Html::addCssStyle($labelOptions, ['width' => $temp_width . 'px' , 'flex' => ' 0 0 ' . $temp_width . 'px']);
                    //Html::addCssStyle($labelOptions, ['width' => $config['label_width'] . 'px' , 'flex' => ' 0 0 ' . $config['label_width'] . 'px']);
                }

                Html::addCssClass($labelOptions, ['field-left-label']);
                Html::addCssClass($innerContentOption, 'field-wrapper-left-label');
                $leftLabelFieldOptions = ['class' => 'left-label-wrapper'];
            } else {
                Html::addCssClass($labelOptions, 'top-label-wrapper');
            }
        }

        if ($field) {
                if($config['field_type'] == 'Image' || $config['field_type'] == 'DImage') {
                    $leftLabelFieldOptions = ['style' => 'overflow: hidden;'];

                    $field = Html::tag('div', $field, $leftLabelFieldOptions);
                } else {
                    $field = Html::tag('div', $field, $leftLabelFieldOptions);
                }
        }

        if (!empty($internationalization['field_label'][Yii::$app->language])) {
            $labelInternalization = $internationalization['field_label'][Yii::$app->language];
        } else if (!empty($config['field_label'])) {
            $labelInternalization = $config['field_label'];
        }

        if (!empty($labelInternalization)) {
            if (!empty($internationalization['field_tooltip'][Yii::$app->language])) {
                $tooltipInternalization = $internationalization['field_tooltip'][Yii::$app->language];
            } else if (!empty($config['field_tooltip'])) {
                $tooltipInternalization = $config['field_tooltip'];
            }

            $labelTextOptions = !empty($tooltipInternalization) ? $this->getTooltipOptions($tooltipInternalization) : [];
            $labelText = Html::tag('span',  $labelInternalization, $labelTextOptions);

			if(isset($config['label_link_type']) && $config['label_link_type'] == 'Y') {
				//echo '<pre> $config :: '; print_r($config);

				//$url =  'javascript: void(0);';
				$url =  $config['label_link_type_url'];

				if(isset($config['type-link']) && $config['type-link'] != '')
					if($config['type-link'] == '_blank')
						$link_type = '_blank';
					else
						$link_type = '';
				else
					$link_type = '_blank';

				$link = Html::a($labelText, $url, ['target' => $link_type, 'class' => 'label-custom-common-link-class', 'style' => 'color: #000;']);

				$label = Html::label($link, null, $labelOptions);
			} else {
				$label = Html::label($labelText, null, $labelOptions);
			}
		}

        if (!empty($config['field_type'])) {
            if (in_array($config['field_type'], [_FieldsHelper::TYPE_INLINE_SEARCH, _FieldsHelper::TYPE_DATALIST, _FieldsHelper::TYPE_TEXT, _FieldsHelper::TYPE_TEXTAREA])) {
                Html::addCssClass($innerContentOption, 'item-content-inline-search');
            } elseif ($config['field_type'] == _FieldsHelper::TYPE_LABEL && isset($config['label_text_align'])) {
                switch ($config['label_text_align']) {
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

                Html::addCssClass($innerContentOption, 'field-label-block');
                Html::addCssStyle($innerContentOption, [
                    'text-align' => $config['label_text_align'],
                    'justify-content' => $justifyContent
                ]);

                $field = null;
            }

			if(($config['field_type'] == _FieldsHelper::TYPE_SELECT || $config['field_type'] == _FieldsHelper::TYPE_DATALIST_RELATION) && (isset($config['multi-select-type']) && $config['multi-select-type'] == _FieldsHelper::PROPERTY_TRUE)) {
				Html::addCssClass($innerContentOption, 'item-content-inline-search');
			}
        }

		if(isset($config['security_question_pk_key1']) && $config['security_question_pk_key1'] != '' && RenderTabHelper::$account_protection == 1) {
			$sec_question = str_replace(';', '.', $config['security_question_pk_key1']);

			$field .= Html::input('hidden', 'security_question1', $sec_question, []);
		} else if(isset($config['security_question_pk_key2']) && $config['security_question_pk_key2'] != '' && RenderTabHelper::$account_protection == 1) {
			$sec_question = str_replace(';', '.', $config['security_question_pk_key2']);

			$field .= Html::input('hidden', 'security_question2', $sec_question, []);
		} else if(isset($config['security_question_pk_key3']) && $config['security_question_pk_key3'] != '' && RenderTabHelper::$account_protection == 1) {
			$sec_question = str_replace(';', '.', $config['security_question_pk_key3']);

			$field .= Html::input('hidden', 'security_question3', $sec_question, []);
		} else if(isset($config['security_question_pk_key4']) && $config['security_question_pk_key4'] != '' && RenderTabHelper::$account_protection == 1) {
			$sec_question = str_replace(';', '.', $config['security_question_pk_key4']);

			$field .= Html::input('hidden', 'security_question4', $sec_question, []);
		}

		/*if(isset($config['security_filter1']) && $config['security_filter1'] != '' && RenderTabHelper::$identify_verification == 1)
			$field .= Html::input('hidden', 'security_filter1', $config['security_filter1'], []);
		else if(isset($config['security_filter2']) && $config['security_filter2'] != '' && RenderTabHelper::$identify_verification == 1)
			$field .= Html::input('hidden', 'security_filter2', $config['security_filter2'], []);
		else if(isset($config['security_filter3']) && $config['security_filter3'] != '' && RenderTabHelper::$identify_verification == 1)
			$field .= Html::input('hidden', 'security_filter3', $config['security_filter3'], []);

		if(isset($config['self_registration_database_field']) && $config['self_registration_database_field'] != '' && RenderTabHelper::$identify_verification == 1)
			$field .= Html::input('hidden', 'security_filter1', $config['self_registration_database_field'], []);*/

        if (empty($field) && !empty($config["field_type"]) && $config["field_type"] != 'Label') {
            return '';
        }

        $innerContent = Html::tag('div', $label . $field, $innerContentOption);
        return Html::tag('div', $innerContent, $divOptions);
    }

    private function getLabelOptions($config) {
        $option = ['style' => ''];

        $textDecoration = '';
        $textDecoration .= (!empty($config['label_strike'])) ? 'line-through' : '';
        $textDecoration .= (!empty($config['label_underline'])) ? ' underline' : '';

        if (!empty($textDecoration)) {
            Html::addCssStyle($option, ['text-decoration' => $textDecoration]);
        }
        if (!empty($config['label_bold'])) {
            Html::addCssStyle($option, ['font-weight' => 'bold']);
        }
        if (!empty($config['label_italic'])) {
            Html::addCssStyle($option, ['font-style' => 'italic']);
        }

        if (!empty($config['label_text_color'])) {
            Html::addCssStyle($option, ['color' => $config['label_text_color'] . '!important']);
        }
        if (!empty($config['label_bg_color'])) {
            Html::addCssStyle($option, ['background-color' => $config['label_bg_color']]);
        }

        if (!empty($config['label_font_family'])) {
            Html::addCssStyle($option, ['font-family' => $config['label_font_family']]);
        }
        if (!empty($config['label_font_size'])) {
            Html::addCssStyle($option, ['font-size' => $config['label_font_size'] . 'px']);
        }

        return $option;
    }

    protected function getTooltipOptions($title)
    {
        return ["data-toggle" => "tooltip", "data-placement" => "top", "title" => $title];
    }

    protected function rebuildFieldConfiguration(array $field)
    {
        $config = [];
        foreach ($field as $item) {
			if(isset($item['name']) && $item['name'] != '') {
				preg_match('/(^|.*\])([\w\.\+-]+)(\[.*|$)/u', $item['name'], $matches);
				if ($matches[3] !== '') {
					$subConfig = $item['value'];
					if (strpos($matches[3], '[]') !== false) {
						$subConfig = [$subConfig];
					}
					if (($keys = trim($matches[3], '[]')) !== '') {
						foreach (array_reverse(explode('][', $keys)) as $id) {
							$subConfig = [$id => $subConfig];
						}
					}

					if (isset($config[$matches[2]])) {
						$subConfig = array_merge_recursive($config[$matches[2]], $subConfig);
					}
				} else {
					$subConfig = $item['value'];
				}

				$config[$matches[2]] = $subConfig;
			}
        }

        return $config;
    }

    /**
     * @param array $config
     * @return bool
     */
    protected function isHidden(array $config)
    {
        return (isset($config['hidden']) && $config['hidden'] == 'Y');
    }
}
