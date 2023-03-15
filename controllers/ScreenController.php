<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\controllers;

use app\assets\ScreenAsset;
use app\models\Menu;
use app\models\Screen;
use app\models\ScreenList;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class ScreenController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'search-data' => ['get']
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function beforeAction($action)
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

		$this->view->registerAssetBundle(ScreenAsset::class);

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

    public function actionIndex($menu, $screen, $isFrame = false)
    {
        if ($isFrame) {
            $this->layout = 'without_header';
            $this->view->params['layout_without_params'] = true;
        }

        $screenGroup = ScreenList::getData([
            'menu_name' => [$menu],
            'group_name' => Menu::getGroupNames(),
            'screen_name' => [$screen]
        ]);

        if ($screenGroup && $screenGroup->isSuccess()) {
            $screenProperty = $screenGroup->list[0];
        } else {
            if(!Yii::$app->user->isGuest) {
                throw new NotFoundHttpException('Screen not found');
            } else {
                $this->redirect(Url::toRoute(['/login']));
                return false;
            }
		}

        if (Yii::$app->request->isAjax) {
            $tabModel = isset(\Yii::$app->session['tabData']) ? \Yii::$app->session['tabData'] : [];
            if (empty($tabModel)) {
                $tabModel = Screen::getData([
                    'screen_name' => [$screen]
                ]);
                Yii::$app->session['tabData'] = $tabModel;
            }

        } else {
            $tabModel = Screen::getData([
                'screen_name' => [$screen]
            ]);

            if (!empty($tabModel->list)) {
                foreach ($tabModel->list as $key => $tab) {
                    $devices = explode(';', $tab['screen_tab_devices']);
                    if (!in_array('W', $devices)) unset($tabModel->list[$key]);
                }
            }

            Yii::$app->session['tabData'] = $tabModel;
        }

        return $this->render('index', [
            'menu' => $menu,
            'screen' => $screen,
            'screenProperty' => $screenProperty,
            'tabModel' => $tabModel,
        ]);
    }
}
