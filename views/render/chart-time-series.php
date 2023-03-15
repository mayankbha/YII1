<?php

/* @var $this yii\web\View */
/* @var $dataProvider array */
/* @var $categories array */

use dosamigos\chartjs\ChartJs;
use yii\web\JsExpression;

?>
<?= ChartJs::widget([
    'type' => 'line',
    'options' => [
        'height' => '400',
        'width' => '400',
        'id' => 'breakout_line_'.rand(),
        'scales' => [
            'xAxes' => [
                [
                    'type' => 'time',
                    'distribution' => 'linear',
                ],
            ],
        ],
    ],
    'clientOptions' => [
        'scales' => [
            'xAxes' => [
                [
                    'type' => 'time',
                    'time'=> [
                        'min'=> $categories[0] ?? date('Ydm'),
                        'max'=> count($categories) ? $categories[count($categories) - 1] : date('Ydm'),
                    ],
                ],
            ],
            'yAxes' => [
                [
                    'ticks' => [
                        'beginAtZero' => true,
                    ],

                ]]
        ],
        'onResize' => new JsExpression('function (chart) { 
                                            common.resizeChart(chart);
                                        }'),
        'legend' => [
            'display' => true,
        ],
        'tooltips' => [
            'callbacks' => [
                'label' =>  new JsExpression('function(chart, data) {
                                                    return common.generateTooltipsLineChart(chart, data);
                                                }')
            ],
        ],
    ],

    'data' => [
        'labels' => $categories,
        'datasets' => $dataProvider
    ]
]);
?>

