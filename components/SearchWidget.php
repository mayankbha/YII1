<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use app\models\CommandData;
use kartik\typeahead\Typeahead;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\JsExpression;

use Yii;

class SearchWidget extends Widget
{
    const TYPE_DEFAULT = 1;
    const TYPE_CUSTOM_QUERY = 2;

    public $config = [];

    public function init()
    {
        parent::init();
        if (empty($this->config)) {
            throw new InvalidConfigException("You must define the 'config' property for SearchWidget which must be an array.");
        }
    }

    public function run()
    {
        //echo '<pre>'; print_r($this->config); die;

		$width = '250px';

        $result = [];
        foreach($this->config as $libName => $data) {
            if (!empty($data['custom'])) {
                $input = $this->getCustomBlock($data['custom'], $libName);

				if(!empty($data['custom']->query_params)) {
					if(sizeof($data['custom']->query_params) == 2)
						$width = '416px';
					else if(sizeof($data['custom']->query_params) >= 3)
						$width = '498px';
				}	
            } elseif (!empty($data['default'])) {
                $input = $this->getDefaultBlock($data['default'], $libName);

				if(!empty($data['default']->func_inparam_configuration)) {
					if(sizeof($data['default']->func_inparam_configuration) == 2)
						$width = '416px';
					else if(sizeof($data['default']->func_inparam_configuration) >= 3)
						$width = '498px';
				}
            } else {
                continue;
            }

            $result[] = Html::tag('div', $input, [
                'class' => 'search-group ' . (empty($result) ? 'active' : ''),
                'data-srch-lib' => $libName,
				'style' => 'width: '.$width.' !important;'
            ]);
        }

        return implode('', $result);
    }

    public function getCustomBlockResultTemplate($config)
    {
        $result = [];
        foreach ($config->query_params as $param) {
            $result[] = Html::tag('div', "{{{$param['name']}}}", ['class' => 'row-item']);
        }

        return Html::tag('div', implode('', $result));
    }

    public function getCustomBlockResultHeaderTemplate($config)
    {
        $result = [];
        foreach ($config->query_params as $param) {
            $result[] = Html::tag('div', "{$param['value']}", ['class' => 'row-item']);
        }

        return Html::tag('div', implode('', $result), ['class' => 'tt-dataset-header']);
    }

    public function getCustomBlock($config, $libName)
    {
        if (empty($config->query_params) || empty($config->query_pk)) {
            return null;
        }

		$width = '250px';

		if(!empty($config->query_params)) {
			if(sizeof($config->query_params) == 2)
				$width = '416px';
			else if(sizeof($config->query_params) >= 3)
				$width = '498px';
		}

        $inputs = [];
        $template = [
            'suggestion' => $this->getCustomBlockResultTemplate($config),
            'header' => $this->getCustomBlockResultHeaderTemplate($config),
        ];

        foreach ($config->query_params as $param) {
            $options = [
                'class' => 'search-field-' . md5($libName),
                'data-library' => $libName,
                'data-search' => json_encode($config),
                'placeholder' => $param['value'],
                'autocomplete' => 'off'
            ];

            $inputs[] = $this->getInput($param['name'], $options, $template);
        }

        return Html::tag('div', implode('', $inputs), [
            'class' => 'search-input-wrapper form-control',
            'tabindex' => -1,
			'style' => 'width: '.$width.' !important;'
        ]);
    }

	public function getDefaultBlockResultTemplate($config)
    {
        $result = [];
        foreach ($config->func_inparam_configuration as $param) {
            $result[] = Html::tag('div', "{{{$param}}}", ['class' => 'row-item']);
        }

        return Html::tag('div', implode('', $result));
    }

    public function getDefaultBlockResultHeaderTemplate($config)
    {
        $result = [];
        foreach ($config->func_inparam_configuration as $param) {
            $result[] = Html::tag('div', "{$param}", ['class' => 'row-item']);
        }

        return Html::tag('div', implode('', $result), ['class' => 'tt-dataset-header']);
    }

    public function getDefaultBlock($config, $libName)
    {
        if (empty($config)) {
            return null;
        }

		$width = '250px';

		if(!empty($config->func_inparam_configuration)) {
			if(sizeof($config->func_inparam_configuration) == 2)
				$width = '416px';
			else if(sizeof($config->func_inparam_configuration) >= 3)
				$width = '498px';
		}

        $userData = Yii::$app->getUser()->getIdentity();

        $lastFoundData = '';

        if($config->data_source_get == 'Search_c_user_accounts') {
            $pk_configuration = array();

            foreach($config->pk_configuration as $pk) {
                $pk_configuration[$pk] = $userData->$pk;
            }

            $temp = $config->func_inparam_configuration[0];
            $func_inparam_configuration = array();

            if(isset($config->pass_logged_user))
                $lastFoundData = ['id' => $pk_configuration, $config->func_inparam_configuration[0] => $userData->$temp, 'pass_logged_user' => $config->pass_logged_user];
        }

		if(!empty($config->func_inparam_configuration) && sizeof($config->func_inparam_configuration) == 1) {
			$name = CommandData::fixedApiResult($config->func_inparam_configuration[0]);
			$placeholder = (!empty($config->field_label)) ? $config->field_label : $name;

			return $this->getInput($name, [
				'class' => 'search-field-' . md5($libName),
				'data-library' => $libName,
				'data-search' => json_encode($lastFoundData),
				//'data-pass-logged-user' => true,
				'placeholder' => $placeholder,
				'autocomplete' => 'off'
			]);
		} else {
			$inputs = [];
			$template = [
				'suggestion' => $this->getDefaultBlockResultTemplate($config),
				'header' => $this->getDefaultBlockResultHeaderTemplate($config),
			];

			foreach ($config->func_inparam_configuration as $param) {
				$name = CommandData::fixedApiResult($param);
				$placeholder = $name;

				$options = [
					'class' => 'search-field-' . md5($libName),
					'data-library' => $libName,
					'data-search' => json_encode($config),
					'placeholder' => $placeholder,
					'autocomplete' => 'off'
				];

				$inputs[] = $this->getInput($param, $options, $template);
			}

			return Html::tag('div', implode('', $inputs), [
				'class' => 'search-input-wrapper form-control',
				'tabindex' => -1,
				'style' => 'width: '.$width.' !important;'
			]);
		}
    }

    public function getInput($name, $options, array $template = [])
    {
        $id = 'search-' . str_replace(".", "", microtime(true));
        $class = $options['class'];

        Html::addCssClass($options, 'search-field');
        return Typeahead::widget([
            'id' => "$id",
            'name' => $name,
            'options' => $options,
            'pluginOptions' => ['highlight' => true],
            'container' => ['class' => 'search-input-inner-wrapper'],
            'dataset' => [
                [
                    'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('$name')",
                    'display' => $name,
                    'limit' => CommandData::SEARCH_LIMIT,
                    'remote' => ['url' => '#'],
                    'source' => new JsExpression("
                        function (query, syncResults, asyncResults) {
                            setTimeout(function()  {
                                console.log('query');
                                console.log(query);
                                console.log('#$id');
                                console.log($('#$id').val());

                                if(query == $('#$id').val()) {
                                    var queries = $('.$class').serializeArray();
                                    common.searchResults('{$options['data-library']}', queries, asyncResults);
                                }
                            }, 1000);
                        }
                    "),
                    'templates' => [
                        'notFound' => Html::tag('div', 'No search result', ['class' => 'text-danger', 'style' => ['padding' => '0 8px']]),
                        'suggestion' => (!empty($template['suggestion'])) ? new JsExpression("Handlebars.compile('{$template['suggestion']}')") : null,
                        'header' => (!empty($template['header'])) ? new JsExpression("Handlebars.compile('{$template['header']}')") : null,
                    ]
                ]
            ],
            'pluginEvents' => [
                "typeahead:select" => "function(e, data) {
					console.log('data');
					console.log(data);

                    if (data['$name']) {
                        common.setActiveId(data.id, data);
                    }
                }"
            ]
        ]);
    }
}