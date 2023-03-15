<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\controllers;

use app\models\AuthenticationModel;
use app\models\forms\LoginForm;
use Yii;
use yii\web\Controller;
use app\components\AuthHelper;
use app\models\forms\CheckAuthForm;

class AuthController extends Controller
{
    const ACTION_EMAIL = 'email';
    const ACTION_SMS = 'sms';
    const ACTION_SQ = 'secret-question';

    public static $actionMask = [
        AuthHelper::AUTH_TYPE_EMAIL => self::ACTION_EMAIL,
        AuthHelper::AUTH_TYPE_SMS => self::ACTION_SMS,
        AuthHelper::AUTH_TYPE_QUESTIONS => self::ACTION_SQ
    ];

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (AuthHelper::getStatus() != AuthHelper::STATUS_PROGRESS) {
            return $this->goHome();
        }

        if (($currentType = AuthHelper::getCurrentType()) && self::$actionMask[$currentType] != $action->id) {
            return $this->goHome();
        }

        return parent::beforeAction($action);
    }

    public function actionSms()
    {
        $model = new CheckAuthForm();
        $request = Yii::$app->request;
        $isSent = false;

        if ($request->isPost) {
            if ($model->load($request->post())) {
                if ($model->validate()) {
                    if (AuthenticationModel::checkAuthTypeCode(AuthHelper::AUTH_TYPE_SMS, $model->confirmation_code)) {
                        AuthHelper::completeType(AuthHelper::AUTH_TYPE_SMS);
                        return $this->checkAuth();
                    }
                }

                Yii::$app->session->setFlash('errors', Yii::t('errors','Incorrect code'));
                $isSent = true;
            } else {
                $isSent = (bool) AuthenticationModel::sendAuthTypeCode(AuthHelper::AUTH_TYPE_SMS);
                if (!$isSent) {
                    Yii::$app->session->setFlash('danger', Yii::t('errors','Error send'));
                }
            }
        }

        return $this->render('sms', [
            'model' => $model,
            'isSent' => $isSent
        ]);
    }

    public function actionEmail()
    {
        $model = new CheckAuthForm();
        $request = Yii::$app->request;
        $isSent = false;

        if ($request->isPost) {
            if ($model->load($request->post())) {
                if ($model->validate()) {
                    if (AuthenticationModel::checkAuthTypeCode(AuthHelper::AUTH_TYPE_EMAIL, $model->confirmation_code)) {
                        AuthHelper::completeType(AuthHelper::AUTH_TYPE_EMAIL);
                        return $this->checkAuth();
                    }
                }

                Yii::$app->session->setFlash('danger', Yii::t('errors','Incorrect code'));
                $isSent = true;
            } else {
                $isSent = (bool) AuthenticationModel::sendAuthTypeCode(AuthHelper::AUTH_TYPE_EMAIL);
                if (!$isSent) {
                    Yii::$app->session->setFlash('danger', Yii::t('errors','Error send'));
                }
            }
        }

        return $this->render('email', [
            'model' => $model,
            'isSent' => $isSent
        ]);
    }

    public function actionSecretQuestion()
    {
        $model = new CheckAuthForm();
        $request = Yii::$app->request;

        if ($model->load($request->post())) {
            if ($model->validate()) {
                if (AuthenticationModel::checkAuthTypeCode(AuthHelper::AUTH_TYPE_QUESTIONS, $model->confirmation_code)) {
                    AuthHelper::completeType(AuthHelper::AUTH_TYPE_QUESTIONS);
                    return $this->checkAuth();
                }
            }

            Yii::$app->session->setFlash('danger', Yii::t('errors','Incorrect answer'));
        }

        $secretQuestion = AuthenticationModel::sendAuthTypeCode(AuthHelper::AUTH_TYPE_QUESTIONS);
        if (!$secretQuestion) {
            Yii::$app->session->setFlash('danger', Yii::t('errors','Error getting secret question'));
        }

        return $this->render('secret-question', [
            'model' => $model,
            'question' => $secretQuestion
        ]);
    }

    private function checkAuth() {
        if (AuthHelper::getStatus() == AuthHelper::STATUS_COMPLETED) {
            $model = new LoginForm();
            $model->login();

            return $this->goHome();
        }

        return $this->redirect(['auth/' . self::$actionMask[AuthHelper::getCurrentType()]]);
    }
}
