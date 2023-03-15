<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\controllers;

use app\components\_FormattedHelper;
use app\components\AuthHelper;
use app\components\RenderGridWidget;
use app\components\RenderTabHelper;
use app\models\forms\CheckCodeForm;
use app\models\CommandData;
use app\models\AuthenticationModel;
use app\models\forms\CheckAuthForm;
use app\models\forms\ChangePasswordForm;
use app\models\forms\ForgotForm;
use app\models\forms\ImageForm;
use app\models\forms\UserStyleTemplateForm;
use app\models\forms\LoginForm;
use app\models\GetListList;
use app\models\services\ExtendedInfo;
use app\models\services\RecordAccess;
use app\models\services\RecordAccessAliasFramework;
use app\models\services\RecordData;
use app\models\services\RecordManager;
use app\models\RegistrationModel;
use app\models\Screen;
use app\models\SecretAnswers;
use app\models\services\RecordSubData;
use app\models\User;
use app\models\UserAccount;
use app\models\UserForm;
use app\models\Image;
use app\models\Workflow;
use stdClass;
use Yii;
use yii\base\ErrorException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

use app\models\CustomLibs;

class SiteController extends BaseController
{
    const STATUS_ERROR = 'error';
    const STATUS_SUCCESS = 'success';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post', 'get'],
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

    public function afterAction($action, $result)
    {
        $noAuthAction = [
            'login',
            'logout',
            'forgot-password',
            'check-code',
            'sign-up',
            'get-user-type',
            'error',
            'guest-login',
			'check-login',
			'check-email',
			'check-sms',
			'check-secret-question'
        ];

        if (!Yii::$app->user->isGuest && $action->id === 'login' && Yii::$app->user->getIdentity()->isNeedToChangePassword()) {
            $this->redirect(Url::toRoute(['change-password']));
        }

        if (Yii::$app->user->isGuest && !in_array($action->id, $noAuthAction)) {
            if (Yii::$app->request->isAjax && $action->id !== 'check-login') {
				Yii::$app->response->redirect(Url::toRoute(['login']));
            } else {
                $this->redirect(Url::toRoute(['login']));
            }
            return false;
        }

        return parent::afterAction($action, $result);
    }

    public function actionIndex()
    {
		$identity = Yii::$app->getUser()->getIdentity();

		if($identity) {
			$session = Yii::$app->session;

			if(Yii::$app->params['guestUserMode'] == 'auto' && isset($session['screenData']) && isset($session['screenData']['app\models\UserAccount'])) {
				$existModel = $session['screenData']['app\models\UserAccount'];

				if($existModel->user_name == Yii::$app->params['guestUserCredentials']['username']) {
					unset($session['screenData']);

					return $this->redirect(Url::toRoute(['/login']));
				} else if($existModel->user_name != Yii::$app->params['guestUserCredentials']['username']) {
					if($identity->getStartScreen() == '/codiac/web/site/index') {
						unset($session['screenData']);

						return $this->redirect(Url::toRoute(['/login']));
					} else {
						return $this->redirect($identity->getStartScreen());
					}
				}
			} else {
				return $this->render('index');
			}
		} else {
			return $this->render('index');
		}
	}

    public function actionMain()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        $this->view->params['showBear'] = true;

		return $this->render('main');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

		//code to auto redirect the user to main page if Guest User mode is auto
		if (isset(Yii::$app->params['guestUserMode']) && Yii::$app->params['guestUserMode'] == 'auto') {
			$session = Yii::$app->session;
            unset($session['screenData']);

			$post_param = array('_csrf_render' => Yii::$app->request->csrfToken, 'LoginForm' => array('username' => Yii::$app->params['guestUserCredentials']['username'], 'password' => Yii::$app->params['guestUserCredentials']['password']));
		} else {
			$post_param = Yii::$app->request->post();
		}

        if ($model->load($post_param) && $model->validate()) {
            /*AuthHelper::completeType(AuthHelper::AUTH_TYPE_LOGIN);

            if (AuthHelper::getStatus() == AuthHelper::STATUS_PROGRESS) {
                $nextType = AuthHelper::getCurrentType();
                return $this->redirect(['auth/' . AuthController::$actionMask[$nextType]]);
            }*/

            $model->login();

			return ($identity = Yii::$app->getUser()->getIdentity()) ? $this->redirect($identity->getStartScreen()) : null;
			//return ($identity = Yii::$app->getUser()->getIdentity()) ? $this->redirect($identity->getStartScreen()) : $this->goBack();
        } else {
            $session = Yii::$app->session;
            unset($session['screenData']);

            if (isset(Yii::$app->params['guestUserMode']) && Yii::$app->params['guestUserMode'] == 'auto') {
                return $this->redirect(['/guest-login']);
            }
        }

        $this->view->params['isLoginForm'] = true;
        return $this->render(Yii::$app->params['loginConfig']['loginStyle'], [
            'model' => $model,
        ]);
    }

	public function actionCheckLogin() {
		//echo 'in actionCheckLogin';

		$session = Yii::$app->session;
		unset($session['screenData']);

		//AuthenticationModel::getTest(); die;

		$model = new LoginForm();

		$post_param = array('_csrf_render' => Yii::$app->request->csrfToken, 'LoginForm' => array('username' => Yii::$app->request->post('username'), 'password' => Yii::$app->request->post('password')));

		//echo '<pre> $post_param :: '; print_r($post_param);

		//if(!empty($post_param)) {
			//echo 'in !empty $post_param';

		if ($model->load($post_param) && $model->validate() && AuthHelper::init()) {
			/*$checkLogin = AuthenticationModel::guestLogin(Yii::$app->request->post('username'), Yii::$app->request->post('password'));

			if(!empty($checkLogin) && $checkLogin['requestresult'] == 'successfully') {
				echo '<pre> $result :: '; print_r($checkLogin);
			} else {
				echo 'incorrect_username_and_password';
			}*/

            AuthHelper::completeType(AuthHelper::AUTH_TYPE_LOGIN);

            if (AuthHelper::getStatus() == AuthHelper::STATUS_PROGRESS) {
                $nextType = AuthHelper::getCurrentType();

				//$model->login();

				echo $nextType;
			} else if (AuthHelper::getStatus() == AuthHelper::STATUS_COMPLETED) {
				$model->login();

				$identity = Yii::$app->getUser()->getIdentity();

				if($identity)
					$this->redirect($identity->getStartScreen());
				else
					echo 'incorrect_username_and_password';
            } else {
				echo 'incorrect_username_and_password';
			}
		} else {
			echo 0;
		}
	}

	public function actionCheckEmail($action = 'get_code', $confirmation_code = '')
    {
		if ($action == 'check_code') {
			if (AuthenticationModel::checkAuthTypeCode(AuthHelper::AUTH_TYPE_EMAIL, $confirmation_code)) {
				AuthHelper::completeType(AuthHelper::AUTH_TYPE_EMAIL);

				if (AuthHelper::getStatus() == AuthHelper::STATUS_COMPLETED) {
					$model = new LoginForm();
					$model->login();

					$identity = Yii::$app->getUser()->getIdentity();

					if($identity)
						$this->redirect($identity->getStartScreen());
				} else {
					echo AuthHelper::getCurrentType();
				}
			} else {
				echo 'Incorrect code';   
			}
		} else {
			$isSent = (bool) AuthenticationModel::sendAuthTypeCode(AuthHelper::AUTH_TYPE_EMAIL);

            if (!$isSent)
				echo 0;
			else
				echo 1;
		}
    }

	public function actionCheckSms($action = 'get_code', $confirmation_code = '')
    {
		if ($action == 'check_code') {
			if (AuthenticationModel::checkAuthTypeCode(AuthHelper::AUTH_TYPE_EMAIL, $confirmation_code)) {
				AuthHelper::completeType(AuthHelper::AUTH_TYPE_SMS);

				if (AuthHelper::getStatus() == AuthHelper::STATUS_COMPLETED) {
					$model = new LoginForm();
					$model->login();

					$identity = Yii::$app->getUser()->getIdentity();

					if($identity)
						echo $identity->getStartScreen();
						//$this->redirect($identity->getStartScreen());
				} else {
					echo AuthHelper::getCurrentType();
				}
			} else {
				echo 'Incorrect code';
			}
		} else {
			$isSent = (bool) AuthenticationModel::sendAuthTypeCode(AuthHelper::AUTH_TYPE_EMAIL);

            if (!$isSent)
				echo 0;
			else
				echo 1;
		}
    }

    public function actionCheckSecretQuestion($action = 'get_code', $confirmation_code = '')
    {
		if ($action == 'check_code') {
			if (AuthenticationModel::checkAuthTypeCode(AuthHelper::AUTH_TYPE_QUESTIONS, $confirmation_code)) {
				AuthHelper::completeType(AuthHelper::AUTH_TYPE_QUESTIONS);

				if (AuthHelper::getStatus() == AuthHelper::STATUS_COMPLETED) {
					$model = new LoginForm();
					$model->login();

					$identity = Yii::$app->getUser()->getIdentity();

					if($identity)
						$this->redirect($identity->getStartScreen());
				}
			} else {
				echo 'Incorrect answer';
			}
		} else {
			$secretQuestion = AuthenticationModel::sendAuthTypeCode(AuthHelper::AUTH_TYPE_QUESTIONS);

			if (!$secretQuestion) {
				echo 0;
			} else {
				echo $secretQuestion;
			}
		}
	}

    public function actionSignUp() {
		$sign_up_style = User::getDefaultSettings();
        $postData = Yii::$app->request->post();
        if (isset($postData['_registration_data'])) {
            ArrayHelper::remove($postData, Yii::$app->request->csrfParam);
            ArrayHelper::remove($postData, '_registration_data');

            $secretQuestions = ArrayHelper::remove($postData, '_secretQuestions');
            $postData = RegistrationModel::prepareSetData($postData);

            if (($setResult = RegistrationModel::setData($postData)) && !empty($setResult['record_list']['PK'])) {
                SecretAnswers::setData($setResult['record_list']['PK'], $secretQuestions);

                Yii::$app->session->setFlash('success', Yii::t('app', 'Success registration'));
                return $this->redirect('login');
            } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
                $message = $errorMessage;
            } else {
                $message = Yii::t('errors', 'Error registration');
            }

            throw new BadRequestHttpException($message);
        }

        if (isset($postData['tenant']) && isset($postData['account_type']) && isset($postData['user_type'])) {
            $info = RegistrationModel::getInfoByParams($postData['account_type'], $postData['tenant'], $postData['user_type']);
            if (!empty($info['screen_record_list'][0])) {
                if (!empty($info['security_spec_record_list'][0]['secret_questions'])) {
                    $PKs = str_replace(GetListList::BASE_NAME_SECURITY_QUESTIONS . '.', '', $info['security_spec_record_list'][0]['secret_questions']);
                    $PKs = explode(';', $PKs);

                    $securityQuestions = GetListList::getData([
                        'list_name' => [GetListList::BASE_NAME_SECURITY_QUESTIONS],
                        'entry_name' => $PKs
                    ]);
                }
                $securityQuestions = (!empty($securityQuestions->list)) ? $securityQuestions->list : [];

                return $this->render('signup/second-step'.Yii::$app->params['loginConfig']['sign_up_style'], [
                    'screen' => $info['screen_record_list'][0],
                    'tenantCode' => $postData['tenant'],
                    'accountType' => $postData['account_type'],
                    'userType' => $postData['user_type'],
                    'secretQuestions' => $securityQuestions,
					'sign_up_style' => $sign_up_style
                ]);
            } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
                $message = $errorMessage;
            } else {
                $message = Yii::t('errors', 'Invalid request');
            }

            throw new BadRequestHttpException($message);
        }

        unset(Yii::$app->session['screenData']);
        RegistrationModel::prepareData();
        if ($registrationData = RegistrationModel::getInfo()) {
            if ($tenantList = (!empty($registrationData['tenant_record_list'])) ? $registrationData['tenant_record_list'] : []) {
                $tenantList = ArrayHelper::map($tenantList, 'Tenant', 'Name');
            }

            if ($accountTypeList = (!empty($registrationData['acc_type_record_list'])) ? $registrationData['acc_type_record_list'] : []) {
                $accountTypeList = ArrayHelper::map($accountTypeList, function ($data) {return "{$data['list_name']}.{$data['entry_name']}";}, 'entry_name');
            }

            if ($userTypeList = (!empty($registrationData['security_spec_record_list'])) ? $registrationData['security_spec_record_list'] : []) {
                $userTypeList = ArrayHelper::map($userTypeList, 'user_type', 'user_type');
            }

            return $this->render('signup/first-step'.Yii::$app->params['loginConfig']['sign_up_style'], [
                'tenantList' => $tenantList,
                'accountTypeList' => $accountTypeList,
                'userTypeList' => $userTypeList,
				'sign_up_style' => $sign_up_style
            ]);
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        } else {
            $message = Yii::t('errors', 'Error getting parameters');
        }

        throw new ErrorException($message);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

		Yii::$app->session['tabData'] = '';
		Yii::$app->user->returnUrl = '';

        return $this->goHome();
    }

    public function actionSearchData()
    {
		if (Yii::$app->user->isGuest) {
			if (Yii::$app->request->isAjax) {
				Yii::$app->response->redirect(Url::toRoute(['/login']));
			} else {
				$this->redirect(Url::toRoute(['/login']));
			}
            return false;
        }

        $post = Yii::$app->request->post();

        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($post['library']) || empty($post['queries']) || !is_array($post['queries'])) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        $aliasFrameworkInfo = isset($post['aliasFrameworkInfo']) ? $post['aliasFrameworkInfo'] : null;

        $queries = ArrayHelper::map($post['queries'], 'name', 'value');
        return CommandData::search($post['library'], $queries, $aliasFrameworkInfo);
    }

    public function actionSearchInlineData()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        $post = Yii::$app->request->post();
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($post['pk'])) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        $config = (object)['query_pk' => $post['pk']];
        $queries = empty($post['queries']) ? [] : ArrayHelper::map($post['queries'], 'name', 'value');
        $query_param = empty($post['queries']) ? [] : $post['queries'];

        return CommandData::searchCustom($queries, $config, true, $query_param);
    }

	public function actionSearchLinkedListCustomQuery() {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        $post = Yii::$app->request->post();

        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($post['custom_query']) && empty($post['custom_query_param'])) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        $custom_query = $post['custom_query'];
        $custom_query_param = $post['custom_query_param'];

		return CommandData::searchLinkedListCustomQuery($custom_query, $custom_query_param, array());
	}

	public function actionSearchInlineTempData()
    {
		$response = array();

		Yii::$app->response->format = Response::FORMAT_JSON;

		return $response;
	}

    public function actionSettings()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        if (!($securityFilter = UserAccount::getSecurityFilter()) || $securityFilter['allow_settings_change'] != 'Y') {
            throw new ForbiddenHttpException('Access denied');
        }

        $userModel = Yii::$app->session['screenData'][UserAccount::class];

        $model = new UserForm();
        $model->style_template = new UserStyleTemplateForm($userModel->style_template);

		//echo '<pre>'; print_r($model); die;

        if (Yii::$app->request->isPost && $model->style_template->load(Yii::$app->request->post())) {
            $arrayList = [
                'avatar_array' => UploadedFile::getInstancesByName('avatar_array'),
                'background_image_array' => UploadedFile::getInstancesByName('background_image_array'),
                'menu_background_image_array' => UploadedFile::getInstancesByName('menu_background_image_array')
            ];

            foreach($arrayList as $key => $items) {
                $pk = [];
                foreach($items as $file) {
                    $image = new ImageForm();

                    $image->list_name = Yii::$app->getUser()->getIdentity()->user_name;
                    $image->entry_name = "{$key}_" . str_replace(".", "", microtime(true));
                    $image->type = ImageForm::TYPE_IMAGE;
                    $image->logo_image_body = $file;

                    $result = Image::setModel($image);
                    if ($result['record_list']['PK']) {
                        $pk[] = $result['record_list']['PK'];
                    }
                }

                $model->style_template->$key = array_merge(ArrayHelper::getColumn($model->style_template->$key, 'pk'), $pk);
            }

            $settings = (array)$userModel;

            if (User::updateModel($settings, $model->style_template)) {
                $settings =  Yii::$app->session['screenData'][UserAccount::class];
                $settingsData = (array)$settings;
                $model->style_template = new UserStyleTemplateForm($settingsData['style_template']);
                UserAccount::generateTemplateImages($settings);

                Yii::$app->session->setFlash('success', Yii::t('app', 'Settings has been successfully update'));
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('errors', 'Error update'));
            }
        }

        $default = User::getDefaultSettings();

        return $this->render('settings', [
            'model' => $model,
            'default' => $default
        ]);
    }

    public function actionRenderTab()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        if (!Yii::$app->request->isAjax || empty(Yii::$app->session['tabData'])) {
            return null;
        }

        $id = Yii::$app->request->post('id', false);
        $tid = Yii::$app->request->post('activeTab', false);
        $mode = Yii::$app->request->post('mode', false);
        $cache = Yii::$app->request->post('cache', false);
        $lastFoundData = Yii::$app->request->post('lastFoundData', []);
        $field_val = Yii::$app->request->post('field_val', false);
        $field_list_json = Yii::$app->request->post('field_list_json', false);
        $section_to_refresh = Yii::$app->request->post('section_to_refresh', false);
        $section_depth_value = Yii::$app->request->post('section_depth_value', false);
        $button_action = Yii::$app->request->post('button_action', false);
        $header_fields = Yii::$app->request->post('header_fields', false);

		//ALEX G CHANGES FOR CHART 08/11/2020
		$url = Yii::$app->user->returnUrl;

		if(!empty($lastFoundData)) {
			foreach($lastFoundData as $key => $val) {
				if(!is_array($val)) {
                    if(!is_array($val)) {
                        if (strpos($url, 'search') === false) {
                            $url .= '#search['.$key.']='.$val;
                        }
                    }
				}
			}

			Yii::$app->session['beforelogin'] = $url;
			Yii::$app->user->returnUrl = $url;
		}

		//echo Yii::$app->user->returnUrl;

		//echo "<pre>"; print_r(Yii::$app->session['tabData']);

        if ($selfTab = Yii::$app->session['tabData']->getSelectScreen($tid)) {
            $workflow = Workflow::getInfo($tid, $id);
            $this->view->registerJs("common.setEditModeInfo('$tid', " . json_encode($workflow) . ');');

            if (!$workflow['head'] && (($workflow['locked'] && $workflow['workflow']) || ($workflow['locked'] && $mode))) {
                if ($workflow['afterReleaseRequired']) {
                    $message = Yii::t('errors', 'Screen locked, confirmation required!');
                } else {
                    $message = Yii::t('errors', 'Screen locked by another user\'s workflow!');
                }

                return $this->renderAjax('@app/views/screen/_error', compact('message'));
            }

            if ($mode == RenderTabHelper::MODE_EXECUTE) {
                $response = Yii::$app->response;
                $response->format = Response::FORMAT_JSON;
                $response->data = [
                    'data' => Screen::execute($selfTab, $id, $field_val, $field_list_json, $lastFoundData),
                    'messagePool' => ExtendedInfo::getInfoList()
                ];
                return $response;
            } else {
                if ($errorMessage = ExtendedInfo::getInfoList()) {
                    $this->view->registerJs("common.addToMessagePool(" . json_encode($errorMessage) . ");");
                }

                return $this->renderAjax('element_tab', [
                    'selfTab' => $selfTab,
                    'mode' => $mode,
                    'id' => $id,
                    'cache' => (bool)$cache,
                    'lastFoundData' => $lastFoundData,
					'section_to_refresh' => $section_to_refresh,
					'section_depth_value' => $section_depth_value,
					'button_action' => $button_action,
					'header_fields' => $header_fields
                ]);
            }
        }

        return null;
    }

    public function actionCustomExecute()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        if (!Yii::$app->request->isAjax) {
            return null;
        }

        $id = Yii::$app->request->post('id', false);
        $tid = Yii::$app->request->post('activeTab', false);
        $getFunction = Yii::$app->request->post('getFunction', false);
        $customData = Yii::$app->request->post('customData', []);
        $pre = Yii::$app->request->post('pre', []);
        $post = Yii::$app->request->post('post', []);

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$id || !$getFunction || !$customData) {
            return null;
        }

        if ($selfTab = Yii::$app->session['tabData']->getSelectScreen($tid)) {
            return [
                'data' => (boolean) Screen::executeCustom($id, $selfTab, $getFunction, $customData, $pre, $post),
                'messagePool' => ExtendedInfo::getInfoList()
            ];
        }

        return [
            'data' => false,
            'messagePool' => ExtendedInfo::getInfoList()
        ];
    }

    public function actionGetLoadData()
    {
		if (Yii::$app->user->isGuest) {
			if (Yii::$app->request->isAjax) {
				Yii::$app->response->redirect(Url::toRoute(['/login']));
			} else {
				$this->redirect(Url::toRoute(['/login']));
			}
            return false;
        }

        $post = Yii::$app->request->post();

        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($post['library']) || empty($post['queries']) || !is_array($post['queries'])) {
            throw new BadRequestHttpException(Yii::t('errors', 'Invalid request'));
        }

        $queries = [];
        foreach ($post['queries'] as $item) {
            if (!empty($item['saveFormat'])) {
                $item['value'] = (new _FormattedHelper())->revertDateTime($item['value'], $item['saveFormat']);
            }

            $queries[$item['name']] = $item['value'];
        }

        $aliasFrameworkInfo = (isset($post['aliasFrameworkInfo']) && is_array($post['aliasFrameworkInfo'])) ? $post['aliasFrameworkInfo'] : ['enable' => false];
        $aliasFrameworkInfo['enable'] = filter_var($aliasFrameworkInfo['enable'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $session = Yii::$app->session;
        $configCache = $session[CommandData::SEARCH_CONFIG_CACHE_NAME];
        if (!empty($configCache[$post['library']]['default']) || !empty($configCache[$post['library']]['custom'])) {
            $list = [];
            if (!empty($configCache[$post['library']]['custom'])) {
                $list = CommandData::searchCustom($queries, $configCache[$post['library']]['custom']);
            } elseif (!empty($configCache[$post['library']]['default'])) {
                $tabData = $session['tabData']->getSelectTpl();
                $additionalParam = [];

                if (!empty($tabData['tpl']->screen_extensions['inquire']['pre'][$configCache[$post['library']]['default']->data_source_get])) {
                    $additionalParam['func_extensions_pre'] = [$tabData['tpl']->screen_extensions['inquire']['pre'][$configCache[$post['library']]['default']->data_source_get]];
                }
                if (!empty($tabData['tpl']->screen_extensions['inquire']['post'][$configCache[$post['library']]['default']->data_source_get])) {
                    $additionalParam['func_extensions_post'] = [$tabData['tpl']->screen_extensions['inquire']['post'][$configCache[$post['library']]['default']->data_source_get]];
                }

                $list = CommandData::searchDefault($post['library'], $queries, $configCache[$post['library']]['default'], $additionalParam, $aliasFrameworkInfo);
            }

            return [
                'list' => $list,
                'messagePool' => ExtendedInfo::getInfoList(),
                'errorMessage' => ExtendedInfo::getErrorMessage()
            ];
        } else {
			$inparam_configuration = array();

			foreach($queries as $key => $query) {
				$explode_key = explode('.', $key);

				if(is_array($explode_key) && sizeof($explode_key) > 1)
					$inparam_configuration[] = $explode_key[2];
				else
					$inparam_configuration[] = $key;
			}

			$config = new \stdClass();
			$config->func_inparam_configuration = $inparam_configuration;
			$config->data_source_get = 'Search_'.$post['aliasFrameworkInfo']['request_primary_table'];

			//echo "queries <pre>"; print_r($queries); die;
			//echo "queries <pre>"; print_r($config->func_inparam_configuration);
			//echo "inparam_configuration <pre>"; print_r($inparam_configuration); die;
			//echo key($queries); die;
			//echo "config <pre>"; print_r($config);
			//echo "aliasFrameworkInfo <pre>"; print_r($aliasFrameworkInfo);

			$list = CommandData::searchDefault($post['library'], $queries, $config, [], $aliasFrameworkInfo);

			//return $list;

			return [
                'list' => $list,
                'messagePool' => ExtendedInfo::getInfoList(),
                'errorMessage' => ExtendedInfo::getErrorMessage()
            ];
		}

        return [];
    }

    public function actionCreateData()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $subDataDefault = [
            'insert' => [],
            'update' => [],
            'delete' => []
        ];

        $library = Yii::$app->request->post('lib', false);
        $data = Yii::$app->request->post('data', []);

        $subData = Yii::$app->request->post('subData', $subDataDefault);
        $subData = array_merge($subDataDefault, $subData);

        $aliasFrameworkInfo = (array)Yii::$app->request->post('aliasFrameworkInfo', []);

        $message = Yii::t('message-area','Couldn\'t create record, screen is configured incorrectly');
        $status = self::STATUS_ERROR;

        if (!$library || !$data) {
            return compact('message', 'status');
        }

        $recordManager = new RecordManager($library);
        $recordManager->setAliasFrameworkInfo($aliasFrameworkInfo)->setGroupingFuncType(RecordManager::ITEM_ATTR_CREATE_FUNC);

        $recordData = new RecordData($recordManager, $data);
        $recordSubData = new RecordSubData($recordManager, $subData['insert']);

        $message = Yii::t('message-area','Couldn\'t create record, please try again in a few minutes');
        if ($id = CommandData::insert($recordData, $recordSubData)) {
            Workflow::createRecords($id);
            $message = Yii::t('message-area','The record was created successfully');
            $status = self::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        $messagePool = ExtendedInfo::getInfoList();
        return compact('messagePool', 'message', 'status', 'id');
    }

    public function actionSaveData()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $subDataDefault = [
            'insert' => [],
            'update' => [],
            'delete' => []
        ];

        $library = Yii::$app->request->post('lib', false);
        $id = Yii::$app->request->post('id', false);
        $data = Yii::$app->request->post('data', []);

        $subData = Yii::$app->request->post('subData', $subDataDefault);
        $subData = array_merge($subDataDefault, $subData);

        $aliasFrameworkPK = (array)Yii::$app->request->post('pkForAliasFramework', []);
        $aliasFrameworkInfo = (array)Yii::$app->request->post('aliasFrameworkInfo', []);

        $message = Yii::t('message-area','Couldn\'t update record, screen is configured incorrectly');
        $status = self::STATUS_ERROR;

        if (!$library || !$id || !$data) {
            return compact('message', 'status');
        }

        $recordManager = new RecordManager($library);
        $recordManager->setAliasFrameworkInfo($aliasFrameworkInfo)
                      ->setGroupingFuncType(RecordManager::ITEM_ATTR_UPDATE_FUNC)
                      ->setPK($id)
                      ->setAliasFrameworkPK($aliasFrameworkPK);

        $recordData = new RecordData($recordManager, $data);
        $updateSubData = new RecordSubData($recordManager, $subData['update']);

        $insertSubData = new RecordSubData($recordManager, $subData['insert']);
        $insertSubData->recordManager->setGroupingFuncType(RecordManager::ITEM_ATTR_CREATE_FUNC);

        $deleteSubData = new RecordSubData($recordManager, $subData['delete']);
        $deleteSubData->recordManager->setGroupingFuncType(RecordManager::ITEM_ATTR_DELETE_FUNC);

        $message = Yii::t('message-area','Couldn\'t update record, please try again in a few minutes');
        if (CommandData::update($recordData, $updateSubData, $insertSubData, $deleteSubData)) {
            $message = Yii::t('message-area','The record was updated successfully');
            $status = self::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        $messagePool = ExtendedInfo::getInfoList();
        return compact('messagePool', 'message', 'status', 'id');
    }

	public function actionExecuteData()
    {
		/*if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

		$id = Yii::$app->request->post('id', false);
        $tid = Yii::$app->request->post('activeTab', false);

		$selfTab = Yii::$app->session['tabData']->getSelectScreen($tid);

		//echo '<pre>'; print_r($selfTab); die;

        if ($selfTab = Yii::$app->session['tabData']->getSelectScreen($tid)) {
            $response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;

			$response->data = [
				'data' => Screen::execute($selfTab, $id),
				'messagePool' => ExtendedInfo::getInfoList()
			];

			return $response;
        }

		if (($screenTemplate = Screen::decodeTemplate($selfTab['screen_tab_template'], true)) && !empty($screenTemplate->screen_extensions)) {
            foreach ($screenTemplate->screen_extensions['execute'] as $functionName => $extension) {
				//echo '<pre>'; print_r($extension);

                //if (!empty($extension) && is_array($extension)) {
					//echo 'in not empty if';

                    $libName = $selfTab['screen_lib'];
                    $subDataPK = [];
                    $funcName = $screenTemplate->screen_extensions['executeFunction']['function'];

                    $postData = [
                        'lib_name' => $libName,
                        'func_name' => $funcName
                    ];
                    if ($relatedField = CustomLibs::getRelated($libName, $funcName)) {
                        $subDataPK = CustomLibs::getPK($libName, $functionName);
                    }
                    $fieldParams = CommandData::getFieldListForQuery($id, $relatedField);
                    $additionalParam = CommandData::getFieldOutListForQuery($libName, $funcName, $subDataPK);

                    $val = CommandData::getData2($fieldParams, $postData, $additionalParam);

					die;

                    $additionalParam = [];
                    $custom = explode(';', $screenTemplate->screen_extensions['executeFunction']['custom']);
                    if (!empty($custom[0]) && !empty($custom[1])) {
                        foreach ($screenTemplate->screen_extensions['execute'][$functionName] as $func => $ext) {

                            $postData = [
                                'lib_name' => $custom[0],
                                'func_name' => $custom[1]
                            ];

                            $additionalParam['PK'] = $dataId;
                            $additionalParam['patch_json'] = $val->list[0];
                            if ($functionName == 'pre') {
                                $additionalParam['func_extensions_pre'] = [$ext];
                                if (!empty($screenTemplate->screen_extensions['execute']['post'][$func])){
                                    $postResult = $screenTemplate->screen_extensions['execute']['post'][$func];
                                    $additionalParam['func_extensions_post'] = [$postResult];
                                    array_push($addedExtension, $func);
                                }
                            } else if ($functionName == 'post') {
                                if (!in_array($func, $addedExtension)) {
                                    $additionalParam['func_extensions_post'] = [$ext];
                                    if (!empty($screenTemplate->screen_extensions['execute']['pre'][$func])){
                                        $preResult = $screenTemplate->screen_extensions['execute']['pre'][$func];
                                        $additionalParam['func_extensions_pre'] = [$preResult];
                                    }
                                }
                            }
                            CommandData::getData([], $postData, $additionalParam);
                        }
                    } else {
                        return null;
                    }
                //}
                $success = true;
           }
        }*/

        /*Yii::$app->response->format = Response::FORMAT_JSON;

        $subDataDefault = [
            'insert' => [],
            'update' => [],
            'delete' => []
        ];

        $library = Yii::$app->request->post('lib', false);
        $id = Yii::$app->request->post('id', false);
        $data = Yii::$app->request->post('data', []);
        $activeTab = Yii::$app->request->post('activeTab');

		$tabData = Yii::$app->session['tabData']->getSelectScreen($activeTab);
		$screenTemplate = Screen::decodeTemplate($tabData['screen_tab_template'], true);
		$execute_extension = $screenTemplate->screen_extensions['execute'];
		//echo '<pre>'; print_r($execute_extension->screen_extensions);

        $subData = Yii::$app->request->post('subData', $subDataDefault);
        $subData = array_merge($subDataDefault, $subData);

        $aliasFrameworkPK = (array)Yii::$app->request->post('pkForAliasFramework', []);
        $aliasFrameworkInfo = (array)Yii::$app->request->post('aliasFrameworkInfo', []);

        $message = Yii::t('message-area','Couldn\'t execute extension, screen is configured incorrectly');
        $status = self::STATUS_ERROR;

        if (!$library || !$id || !$data) {
            return compact('message', 'status');
        }

        $recordManager = new RecordManager($library);
        $recordManager->setAliasFrameworkInfo($aliasFrameworkInfo)
                      ->setGroupingFuncType(RecordManager::ITEM_ATTR_UPDATE_FUNC)
                      ->setPK($id)
                      ->setAliasFrameworkPK($aliasFrameworkPK);

        $recordData = new RecordData($recordManager, $data);
        $updateSubData = new RecordSubData($recordManager, $subData['update']);

        $insertSubData = new RecordSubData($recordManager, $subData['insert']);
        $insertSubData->recordManager->setGroupingFuncType(RecordManager::ITEM_ATTR_CREATE_FUNC);

        $deleteSubData = new RecordSubData($recordManager, $subData['delete']);
        $deleteSubData->recordManager->setGroupingFuncType(RecordManager::ITEM_ATTR_DELETE_FUNC);

        $message = Yii::t('message-area','Couldn\'t execute extension, please try again in a few minutes');
        if (CommandData::execute($recordData, $updateSubData, $insertSubData, $deleteSubData, $execute_extension)) {
            $message = Yii::t('message-area','The extension ececuted successfully');
            $status = self::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        $messagePool = ExtendedInfo::getInfoList();
        return compact('messagePool', 'message', 'status', 'id');*/

		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $subDataDefault = [
            'insert' => [],
            'update' => [],
            'delete' => []
        ];

        $library = Yii::$app->request->post('lib', false);
        $id = Yii::$app->request->post('id', false);
        $data = Yii::$app->request->post('data', []);

        $subData = Yii::$app->request->post('subData', $subDataDefault);
        $subData = array_merge($subDataDefault, $subData);

        $aliasFrameworkPK = (array)Yii::$app->request->post('pkForAliasFramework', []);
        $aliasFrameworkInfo = (array)Yii::$app->request->post('aliasFrameworkInfo', []);

        $message = Yii::t('message-area','Couldn\'t execute extension, screen is configured incorrectly');
        $status = self::STATUS_ERROR;

        if (!$library || !$id || !$data) {
            return compact('message', 'status');
        }

        $recordManager = new RecordManager($library);
        $recordManager->setAliasFrameworkInfo($aliasFrameworkInfo)
                      ->setGroupingFuncType(RecordManager::ITEM_ATTR_UPDATE_FUNC)
                      ->setPK($id)
                      ->setAliasFrameworkPK($aliasFrameworkPK);

        $recordData = new RecordData($recordManager, $data);
        $updateSubData = new RecordSubData($recordManager, $subData['update']);

        $insertSubData = new RecordSubData($recordManager, $subData['insert']);
        $insertSubData->recordManager->setGroupingFuncType(RecordManager::ITEM_ATTR_CREATE_FUNC);

        $deleteSubData = new RecordSubData($recordManager, $subData['delete']);
        $deleteSubData->recordManager->setGroupingFuncType(RecordManager::ITEM_ATTR_DELETE_FUNC);

        $message = Yii::t('message-area','Couldn\'t execute extension, please try again in a few minutes');

		return CommandData::execute($recordData, $updateSubData, $insertSubData, $deleteSubData);

        /*if (CommandData::execute($recordData, $updateSubData, $insertSubData, $deleteSubData)) {
            $message = Yii::t('message-area','The record was updated successfully');
            $status = self::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        $messagePool = ExtendedInfo::getInfoList();
        return compact('messagePool', 'message', 'status', 'id');*/
    }

    public function actionDeleteData()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $functionInfoDefault = [
            'get' => false,
            'delete' => false
        ];

        $library = Yii::$app->request->post('lib', false);
        $id = Yii::$app->request->post('id', false);
        $aliasFrameworkPK = (array)Yii::$app->request->post('pkForAliasFramework', []);
        $aliasFrameworkInfo = (array)Yii::$app->request->post('aliasFrameworkInfo', []);

        $functionInfo = Yii::$app->request->post('function', $functionInfoDefault);
        $functionInfo = array_merge($functionInfoDefault, $functionInfo);

        $recordManager = new RecordManager($library);
        $recordManager->setAliasFrameworkInfo($aliasFrameworkInfo)
                      ->setAliasFrameworkPK($aliasFrameworkPK)
                      ->setPK($id);

        $message = Yii::t('message-area', 'Couldn\'t delete record, screen is configured incorrectly');
        $status = self::STATUS_ERROR;

        if (!$library || !$id || !$functionInfo) {
            return compact('message', 'status');
        }

        if (CommandData::delete($recordManager, $functionInfo)) {
            Workflow::deleteRecords($id);
            $message = Yii::t('message-area', 'The record was deleted successfully');
            $status = self::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        $messagePool = ExtendedInfo::getInfoList();
        return compact('messagePool', 'message', 'status');
    }

    public function actionLockData()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $library = Yii::$app->request->post('lib', false);
        $id = Yii::$app->request->post('id', false);
        $function = Yii::$app->request->post('function', false);

        $aliasFrameworkInfo = (array)Yii::$app->request->post('aliasFrameworkInfo', []);
        $aliasFrameworkPK = (array)Yii::$app->request->post('pkForAliasFramework', []);

        $message = Yii::t('message-area', 'Couldn\'t lock record, screen is configured incorrectly');
        $status = self::STATUS_ERROR;

        if (!$library || !$id || !$function) {
            return compact('message', 'status');
        }

        $recordManager = new RecordManager($library);
        $recordManager->setAliasFrameworkInfo($aliasFrameworkInfo)
                      ->setAliasFrameworkPK($aliasFrameworkPK)
                      ->setPK($id);

        $message = Yii::t('message-area', 'Record is locked for updating');
        if ($recordManager->isUseAliasFramework()) {
            if (RecordAccessAliasFramework::lock($recordManager)) {
                $message = Yii::t('message-area', 'Record can be updated');
                $status = self::STATUS_SUCCESS;
            }
        } else if (RecordAccess::lock($recordManager, $function)) {
            $message = Yii::t('message-area', 'Record can be updated');
            $status = self::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        return compact('message', 'status');
    }

    public function actionUnlockData()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $library = Yii::$app->request->post('lib', false);
        $id = Yii::$app->request->post('id', false);
        $function = Yii::$app->request->post('function', false);

        $aliasFrameworkInfo = (array)Yii::$app->request->post('aliasFrameworkInfo', false);
        $aliasFrameworkPK = (array)Yii::$app->request->post('pkForAliasFramework', false);

        $message = Yii::t('message-area', 'Couldn\'t unlock record, screen is configured incorrectly');
        $status = self::STATUS_ERROR;

        if (!$library || !$id || !$function) {
            return compact('message', 'status');
        }

        $recordManager = new RecordManager($library);
        $recordManager->setAliasFrameworkInfo($aliasFrameworkInfo)
            ->setAliasFrameworkPK($aliasFrameworkPK)
            ->setPK($id);

        if ($recordManager->isUseAliasFramework()) {
            if (RecordAccessAliasFramework::unlock($recordManager)) {
                $message = Yii::t('message-area', 'Record is unlocked for update');
                $status = self::STATUS_SUCCESS;
            }
        } else if (RecordAccess::unlock($recordManager, $function)) {
            $message = Yii::t('message-area', 'Record is unlocked for update');
            $status = self::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        return compact('message', 'status');
    }

    public function actionGetSubData()
    {
        $lib = Yii::$app->request->post('lib', false);
        $id = Yii::$app->request->post('id', false);
        $page = Yii::$app->request->post('page', false);

        $row = Yii::$app->request->post('row', false);
        $col = Yii::$app->request->post('col', false);

        $tid = Yii::$app->request->post('activeTab', false);
        $mode = Yii::$app->request->post('mode', false);

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = false;

        if (!empty($lib) && !empty($page)) {
            $response->data = RenderGridWidget::widget([
                'isAjax' => true,
                'lib_name' => $lib,
                'page' => $page,
                'id' => $id,
                'row' => $row,
                'col' => $col,
                'tid' => $tid,
                'mode' => $mode,
            ]);
            $response->data = json_decode($response->data, true);
        }

        return $response;
    }

    public function actionChangePassword()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        /** @var UserAccount $user */
        if (!($user = Yii::$app->user->getIdentity()) || !$user->isNeedToChangePassword()) {
            if (!($securityFilter = UserAccount::getSecurityFilter()) || $securityFilter['allow_password_change'] != 'Y') {
                throw new ForbiddenHttpException('Access denied');
            }
        }

        $userAccount = Yii::$app->session['screenData'][UserAccount::class];
        if (!is_null($userAccount)) {
            $form = new ChangePasswordForm($userAccount);
            if ($form->load(Yii::$app->request->post())) {
                if ($form->validate(Yii::$app->request->post()) && User::updateModelPassword(UserAccount::encodePassword($form->password))) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Password changed'));
                } else {
                    Yii::$app->session->setFlash('danger', Yii::t('errors', 'Error update'));
                }
            }
            return $this->render('changepass', [
                'model' => $form
            ]);
        }
    }

    public function actionForgotPassword()
    {
        $model = new ForgotForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $response = AuthenticationModel::sendAuthCode($model->source, $model->username);
            if (isset($response) && $response['requestresult'] != 'unsuccessfully') {
                $this->redirect(Url::toRoute(['check-code', 'username' => $model->username]));
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('errors', 'Error'));
            }
        }
        return $this->render('forgot', [
            'model' => $model,
        ]);
    }

    public function actionGuestLogin()
    {
        $model = new LoginForm();

        if (!Yii::$app->getUser()->isGuest) {
            $this->redirect(['/']);
        }

        if (!Yii::$app->params['guestUserMode'] || !isset(Yii::$app->params['guestUserCredentials'])) {
            $this->redirect(['/login']);
        }

        $model->username = Yii::$app->params['guestUserCredentials']['username'];
        $model->password = Yii::$app->params['guestUserCredentials']['password'];

        return $this->render('guest-login', [
            'model' => $model,
        ]);
    }

    public function actionCheckCode()
    {
        $model = new CheckCodeForm();
        $model->username = Yii::$app->getRequest()->getQueryParam('username');
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $response = AuthenticationModel::sendNewPassword($model->username, $model->code);
            if (!is_null($response)) {
                $this->redirect(Url::toRoute(['login']));
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('errors', 'Error'));
            }
        }
        return $this->render('check_code', [
            'model' => $model,
        ]);
    }

    public function actionDeleteImage()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $requestData = $request->post()) {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = false;
            $selectedImageAttr = $requestData['attribute'];
            $attribute = $requestData['attribute'] .'_array';
            $imagePK = $requestData['image'];

            $userModel = Yii::$app->session['screenData'][UserAccount::class];
            $settings = (array)$userModel;
            $settingsData = !empty($settings['style_template']) ? $settings['style_template'] : [];
            $model = new UserForm();
            $model->style_template = new UserStyleTemplateForm($settingsData);

            if (isset($model->style_template->{$attribute})) {
                foreach ($model->style_template->{$attribute} as $key => $item) {
                    if ($item['pk'] == $imagePK) {
                        if($model->style_template->{$selectedImageAttr} == $imagePK) {
                            $model->style_template->{$selectedImageAttr} = null;
                        }
                        unset($model->style_template->{$attribute}[$key]);
                        Image::deleteModel($imagePK);
                    }
                }
                $model->style_template->prepareImageAttributes();

                if (User::updateModel($settings, $model->style_template)) {
                    $response->data = true;
                }
            }


            return $response;
        }

        return $this->goHome();
    }

    public function actionGetUserList()
    {
		if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$request->isAjax || !($groups = $request->post('group'))) {
            throw new BadRequestHttpException('Parameters is not valid');
        }

        $config = new stdClass();
        $config->data_source_get = 'SearchUser';
        $config->func_inparam_configuration = ['group_area'];

        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = CommandData::searchDefault('CodiacSDK.AdminUsers', ['user_name' => '', 'group_area' => $groups], $config);
        if (!$result) {
            throw new ErrorException('Error with getting assigned user list');
        }

        return [
            'list' => $result
        ];
    }

	public function actionExportTableData() {
		$request = Yii::$app->request;
        $tableData = $request->post('table_data');

		$file="render.xls";

		//$test = "<table><tr><td>Cell 1</td><td>Cell 2</td></tr></table>";

		header('Content-Type: application/force-download');
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$file");

		echo $tableData;
	}
}
