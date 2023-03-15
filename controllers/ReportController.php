<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\controllers;

use app\models\CommandData;
use app\models\Report;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReportController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function beforeAction($action)
    {
		Yii::$app->language = (!empty($_COOKIE['language'])) ? $_COOKIE['language'] : Yii::$app->sourceLanguage;

        if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        return parent::afterAction($action, $result);
    }

    public function actionSearch()
    {
        $post = Yii::$app->request->post();
        $queries = empty($post['queries']) ? [] : ArrayHelper::map($post['queries'], 'name', 'value');

        if (!empty($post['multiSearch'])) {
            return CommandData::searchCustom($queries, json_decode(json_encode($post['multiSearch'])));
        } else if (!empty($post['simpleSearch'])) {
            return CommandData::searchDefault('CodiacSDK.Universal', $queries, json_decode(json_encode($post['simpleSearch'])));
        }

        throw new BadRequestHttpException(Yii::t('errors','Search is not configured'));
    }

    public function actionGenerate()
    {
        try {
            if (!($id = Yii::$app->request->post('reportId', false))) {
                throw new BadRequestHttpException(Yii::t('errors','Has no required params for generate template'));
            }

            if (!($report = Report::getModel($id))) {
                throw new NotFoundHttpException(Yii::t('errors','This report can\'t be found'));
            }

            $searchFunctionConfig = ($report['multi_search']) ? json_decode($report['multi_search']) : json_decode($report['simple_search']);
            $isBatch = Yii::$app->request->post('isBatch', false);
            $searchResult = Yii::$app->request->post('searchResult', null);
            $batch = Yii::$app->request->post('batch', []);

            if (empty($searchResult) && !$isBatch && empty($batch)) {
                throw new NotFoundHttpException(Yii::t('errors','This data for report can\'t be found'));
            }

            $searchFunctionInfo = [
                'config' => $searchFunctionConfig,
                'data' => $searchResult
            ];

            if ($generateFilePK = Report::generate($id, $report['primary_table'], $searchFunctionInfo, $isBatch, $batch)) {
                return [
                    'status' => 'success',
                    'response' => [
                        'PKList' => $generateFilePK
                    ]
                ];
            }

            throw new BadRequestHttpException(Yii::t('errors','Error generate'));
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
