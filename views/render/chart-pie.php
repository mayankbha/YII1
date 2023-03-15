<?php

/* @var $this yii\web\View */
/* @var $dataProvider array */

use dosamigos\chartjs\ChartJs;
use yii\web\JsExpression;

?>

<?=
ChartJs::widget([
    'type' => 'pie',
    'options' => [
        'height' => '400',
        'width' => '400',
        'id' => 'breakout_pie_'.rand()

    ],
    'clientOptions' => [
        'onResize' => new JsExpression('function (chart) { 
                                            common.resizeChart(chart);
                                        }'),
        'legend' => [
            'display' => true,
            'labels' => [
                'filter' => new JsExpression('function(chart, data) {
                                                return common.generateLegendPieChart(chart, data);
                                              }'),
            ]
        ],
        'tooltips' => [
              'callbacks' => [
                  'label' =>  new JsExpression('function(chart, data) {
                                                  return common.generateTooltipsPieChart(chart, data);
                                                }')
              ],
        ],
    ],
    'data' => [
        'labels' => $dataProvider['name'],
        'datasets' => [
            [
                'backgroundColor' => $dataProvider['backgroundColor'],
                'borderColor' => "rgba(179,181,198,1)",
                'pointBackgroundColor' => "rgba(179,181,198,1)",
                'pointBorderColor' => "#fff",
                'pointHoverBackgroundColor' => "#fff",
                'pointHoverBorderColor' => "rgba(179,181,198,1)",
                'data' => $dataProvider['data'],
                'formatData' => $dataProvider['label'],
            ]
        ]
    ]
]);
?>

