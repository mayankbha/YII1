<?php

namespace app\controllers;

use app\models\LoginForm;
use asasmoyo\yii2saml\actions\AcsAction;
use asasmoyo\yii2saml\actions\LoginAction;
use asasmoyo\yii2saml\actions\LogoutAction;
use asasmoyo\yii2saml\Saml;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;


class SamlController extends Controller {

    // Remove CSRF protection
    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'metadata' => [
                'class' => 'asasmoyo\yii2saml\actions\MetadataAction'
            ],
        ];
    }


    public function __construct($id, $module, $params = [])
    {
        $session = Yii::$app->session;
        $tenant = (array) $session->get('user_tenant');

        $configFileName = '@app/config/saml.php';
        $configFile = Yii::getAlias($configFileName);
        $config = require($configFile);

        if (
            !empty($tenant['SAMLIdpSignOnService'])
            && !empty($tenant['SAMLIdpLogoutService'])
            && !empty($tenant['SAMLIdpX509certSigning'])
            && !empty($tenant['SAMLIdpX509certEncryption'])
            && !empty($tenant['SAMLSpX509cert'])
            && !empty($tenant['SAMLSpPrivateKey'])
        ) {
            $config['sp']['x509cert'] = $tenant['SAMLSpX509cert'];
            $config['sp']['privateKey'] = $tenant['SAMLSpPrivateKey'];
            $config['idp']['singleSignOnService']['url'] = $tenant['SAMLIdpSignOnService'];
            $config['idp']['singleLogoutService']['url'] = $tenant['SAMLIdpLogoutService'];
            $config['idp']['x509certMulti']['signing'][0] = $tenant['SAMLIdpX509certSigning'];
            $config['idp']['x509certMulti']['encryption'][0] = $tenant['SAMLIdpX509certEncryption'];

            Yii::$app->set('saml', new Saml(['config' => $config]));
        }

        parent::__construct($id, $module, $params);
    }

    public function actionLogin()
    {
        $action = new LoginAction('login', 'saml');
        return $action->run();
    }

    public function actionAcs()
    {
        $action = new AcsAction('acs', 'saml');
        $action->successCallback = [get_class($this), 'callback'];
        $action->successUrl = Url::toRoute('/login', true);

        return $action->run();
    }

    public function actionSls()
    {
        $session = Yii::$app->session;
        //$session->destroy();

        $action = new LogoutAction('sls', 'saml');
        //$action->returnTo = '';

		$tenant = Yii::$app->getUser()->getIdentity();

        if (!empty($tenant)) {
            $tenantList = $tenant->tenant_list;
            $returnTo = $tenantList[0]['SAMLIdpLogoutService'];
        } elseif ($session->get('user_tenant')) {
            $tenantProperty = (array) $session->get('user_tenant');
            $returnTo = $tenantProperty['SAMLIdpLogoutService'];
        } else {
            return $this->goHome();
        }

        $action->returnTo = $returnTo;
        return $action->run();
    }

    public function callback($attributes) {
        $session = Yii::$app->session;
        if (!empty($attributes['uid'][0])) {
            $session->set('user_name', $attributes['uid'][0]);
            $session->set('access_token', Yii::$app->request->post('SAMLResponse'));
        }
    }
}
