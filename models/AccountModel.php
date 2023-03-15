<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use app\models\services\ExtendedInfo;
use phpseclib\Crypt\RSA;
use Yii;

abstract class AccountModel
{
    protected static $_defaultAttributes = array();
    public static $dataAction = '';

    //Change controller of API server
    protected static function getSourceLink()
    {
        if (!empty(Yii::$app->session['apiEndpoint'])) {
            return Yii::$app->session['apiEndpoint'];
        }
        return (YII_ENV == 'dev') ? Yii::$app->params['apiEndpointDev'] : Yii::$app->params['apiEndpoint'];
    }

    protected static function checkSourceLink() {
        if (YII_ENV == 'dev' && !empty(Yii::$app->params['apiBalancerDev'])) {
            $balancerUrl = Yii::$app->params['apiBalancerDev'];
        } else if (!empty(Yii::$app->params['apiBalancer'])) {
            $balancerUrl = Yii::$app->params['apiBalancer'];
        }

        if (!empty($balancerUrl) && (empty(Yii::$app->session['apiEndpoint']) || empty(Yii::$app->session['apiEndpointCustom']))) {
            $curl = curl_init($balancerUrl);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt( $curl, CURLOPT_POSTFIELDS, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            if (($json = curl_exec($curl)) && ($response = json_decode($json, true))) {
                if (!empty($response['resultbody']['server_info']['address']) && !empty($response['resultbody']['server_info']['port'])) {
                    $url = 'http://' . $response['resultbody']['server_info']['address'] . ':' . $response['resultbody']['server_info']['port'];
                    Yii::$app->session['apiEndpoint'] = $url . '/' . Yii::$app->params['apiControllersRoute']['common'];
                    Yii::$app->session['apiEndpointCustom'] = $url . '/' . Yii::$app->params['apiControllersRoute']['custom'];
                }
            }

            curl_close($curl);
        }
    }

    /**
     * AES decryption
     * @param array|string $sValue - Value for decryption
     * @param string $sSecretKey - Secret key for decryption
     * @param string $sIv - Iv key
     * @return array
     */
    public static function AesDecrypt($sValue, $sSecretKey, $sIv)
    {
        if (isset($sSecretKey) && isset($sIv)) {
            $sValue = base64_decode($sValue);
            $sValue = openssl_decrypt($sValue, 'AES-256-CBC', $sSecretKey, OPENSSL_RAW_DATA, $sIv);
            $sValue = rtrim($sValue, "\0");
            $sValue = json_decode($sValue, true);
        }

        return $sValue;
    }

    /**
     * AES encryption
     * @param array|string $sValue - Value for encryption
     * @param string $sSecretKey - Secret key for encryption
     * @param string $sIv - Iv key
     * @return array
     */
    public static function AesEncrypt($sValue, $sSecretKey, $sIv)
    {
        if (isset($sSecretKey) && isset($sIv)) {
            $sValue = json_encode($sValue);
            $sValue = openssl_encrypt($sValue, 'AES-256-CBC', $sSecretKey, OPENSSL_RAW_DATA, $sIv);
            $sValue = base64_encode($sValue);
        }

        return $sValue;
    }

    /**
     * Getting key pair
     * @return array
     */
    public static function generatePair()
    {
        defined('CRYPT_RSA_PRIVATE_FORMAT_PKCS1') or define('CRYPT_RSA_PRIVATE_FORMAT_PKCS1', 'CRYPT_RSA_PRIVATE_FORMAT_PKCS1');
        defined('CRYPT_RSA_PUBLIC_FORMAT_PKCS8') or define('CRYPT_RSA_PUBLIC_FORMAT_PKCS8', 'CRYPT_RSA_PUBLIC_FORMAT_PKCS8');

        $rsa = new RSA();
        $rsa->setPrivateKeyFormat(CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
        $rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_PKCS8);

        return $rsa->createKey(2048);
    }

    /**
     * Decode RSA data
     * @param string $data - Data for decode
     * @param string $privateKey - Key for decode
     * @return string
     */
    public static function decodeRSAData($data, $privateKey)
    {
        defined('CRYPT_RSA_ENCRYPTION_OAEP') or define('CRYPT_RSA_ENCRYPTION_OAEP', 'CRYPT_RSA_ENCRYPTION_OAEP');

        $privateKey = str_replace("\r\n", '', $privateKey);
        $rsa = new RSA();
        $rsa->loadKey($privateKey);
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_OAEP);

        return  base64_decode($rsa->decrypt(base64_decode($data)));
    }

    /**
     * Getting static model of API result
     * @param null|string $subKey - Name of model (if is NULL, then data is not saved in session)
     * @param array $postData - Data, for getting API result
     * @return null|static
     */
    public static function getModelInstance($subKey = null, $postData = array())
    {
        $session = Yii::$app->session;
        $screenData = $session['screenData'];
        $calledClass = get_called_class();

        if (empty($subKey)) {
            if (!isset($screenData[$calledClass])) {
				$userInfo = static::getData($postData);
				unset($userInfo->account_password);

                self::addToSession(array($calledClass => $userInfo));
            }
        } else {
            if (!isset($screenData[$calledClass]) || !isset($screenData[$calledClass][$subKey])) {
                $subData = isset(\Yii::$app->session['screenData'][$calledClass]) ? \Yii::$app->session['screenData'][$calledClass] : [];
                $subDataResult = static::getData($postData);
                if (!is_null($subDataResult)) {
                    $subData[$subKey] = $subDataResult;
                    self::addToSession(array($calledClass => $subData));
                }
            }
        }

        $result = !empty($subKey) ? Yii::$app->session['screenData'][$calledClass][$subKey] : Yii::$app->session['screenData'][$calledClass];
        if ($calledClass != 'app\models\UserAccount' && $calledClass != 'app\models\Menu') {
            $sessionData = Yii::$app->session['screenData'];
            if (!empty($subKey) && isset($sessionData[$calledClass][$subKey])) {
                unset($sessionData[$calledClass][$subKey]);
            } elseif (isset($sessionData[$calledClass])) {
                unset($sessionData[$calledClass]);
            }
            Yii::$app->session['screenData'] = $sessionData;
        }
        return $result;
    }

    private function __clone()
    {
    }

    /**
     * Returned information about current session with API server, for getting result from API
     * @return array
     */
    public static function processSessionData()
    {
        $encryptionPair = AccountModel::generatePair();
        $secretKey = null;
        $secretIv = null;
        $sessionhandle = null;

        $serverResult = self::requestToApi([
            'publickey' => $encryptionPair['publickey'],
            'requestbody' => [
                'func_name' => 'createsession'
            ]
        ], true);

		//die;

        if (isset($serverResult['sessionkey']) && $serverResult['sessioniv']) {
            $secretKey = self::decodeRSAData($serverResult['sessionkey'], $encryptionPair['privatekey']);
            $secretIv = self::decodeRSAData($serverResult['sessioniv'], $encryptionPair['privatekey']);

            $result = self::AesDecrypt($serverResult['resultbody'], $secretKey, $secretIv);
            $sessionhandle = $result['sessionhandle'];
        } else if (isset($serverResult['resultbody']['sessionhandle'])) {
            $sessionhandle = $serverResult['resultbody']['sessionhandle'];
        }

        return compact('secretKey', 'secretIv', 'sessionhandle');
    }

    public static function getSessionData()
    {
        if (empty(Yii::$app->session['screenData']['sessionData']['sessionhandle'])) {
            self::addToSession(['sessionData' => self::processSessionData()]);
        }

        return Yii::$app->session['screenData']['sessionData'];
    }

    /**
     * Getting result from API server
     * @param array $postData - Data, for getting API result
     * @param bool $skipEncryption - Set TRUE, if you want skip Encryption
     * @return bool|mixed|string
     */
    public static function requestToApi($postData, $skipEncryption = false)
    {
		//echo "<pre>"; print_r($postData);
		//echo json_encode($postData);

        $curl = curl_init(static::getSourceLink());

        $httpHeader = [
            'Content-Type: application/json',
            'Accept-Encoding: gzip,deflate',
        ];
        if(!empty(Yii::$app->session['access_token'])) {
            $httpHeader[] = 'access_token:' . Yii::$app->session['access_token'];
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));

        if (!($response = curl_exec($curl))) {
            unset(Yii::$app->session['screenData']);
            Yii::$app->user->logout();

            return null;
        }

		//echo 'Response :: ' . $response;
        $response = json_decode($response, true);
        curl_close($curl);

		if (isset($response['requestresult']) && strtolower($response['requestresult']) == 'client_message') {
            return $response['client_message'];
        }

        $body = isset($response['resultbody']) ? $response['resultbody'] : null;
        $session = Yii::$app->session;
        if (!empty($session['screenData']['sessionData']['sessionhandle'])) {
            $sessionData = $session['screenData']['sessionData'];

            if (!$skipEncryption && isset($body) && is_string($body)) {
                $secretKey = $sessionData['secretKey'];
                $secretIv = $sessionData['secretIv'];
                if ((!is_null($secretKey) && !is_null($secretIv))) {
                    $response = self::AesDecrypt($body, $secretKey, $secretIv);
                }
            } else {
                if (!isset($sessionData['secretKey']) || empty($sessionData['secretKey'])) {
                    $response = isset($response['resultbody']) ? $response['resultbody'] : $response;
                }
            }
        }

        if (isset($body['requestresult']) && strtolower($body['requestresult']) == 'unsuccessfully') {
            if (Yii::$app->params['loggingAPIErrors']) {
                Yii::error(['request' => $postData, 'response' => $body], 'api');
            }
            if (isset($body['extendedinfo']) && ($body['extendedinfo'] == 'Session Not Found') || ($body['extendedinfo'] == 'Invalid Session Handle')) {
                unset($session['screenData']);
                Yii::$app->user->logout();
            }

            if (!empty($response['extendedinfo_ml']) && is_array($response['extendedinfo_ml'])) {
                ExtendedInfo::setExtendInfoML($response['extendedinfo_ml']);
            }
        }

        if (!empty($response['extendedinfo']) && is_array($response['extendedinfo'])) {
            ExtendedInfo::setExtendInfo($response['extendedinfo']);
        }

        if (isset($body['requestresult']) && strtolower($body['requestresult']) == 'unsuccessfully') {
            $response = null;
        }

        return $response;
    }

	public static function requestToApi2($postData, $skipEncryption = false)
    {
		//echo 'in requestToApi2'; die;

		//echo "<pre>"; print_r($postData);
		echo json_encode($postData);
		//echo static::getSourceLink(); die;

        $curl = curl_init(static::getSourceLink());

        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept-Encoding: gzip,deflate']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));

        if (!($response = curl_exec($curl))) {
            unset(Yii::$app->session['screenData']);
            Yii::$app->user->logout();

			if (curl_errno($curl)) {
				// this would be your first hint that something went wrong
				die('Couldn\'t send request: ' . curl_error($curl));
			}

            return null;
        }

		echo 'Response :: ' . $response; die;

        $response = json_decode($response, true);

		//echo '<pre>'; print_r(base64_decode($response['resultbody']['record_list'][0]['screen_tab_template']));

        curl_close($curl);

        $body = isset($response['resultbody']) ? $response['resultbody'] : null;
        $session = Yii::$app->session;
        if (!empty($session['screenData']['sessionData']['sessionhandle'])) {
            $sessionData = $session['screenData']['sessionData'];

            if (!$skipEncryption && isset($body) && is_string($body)) {
                $secretKey = $sessionData['secretKey'];
                $secretIv = $sessionData['secretIv'];
                if ((!is_null($secretKey) && !is_null($secretIv))) {
                    $response = self::AesDecrypt($body, $secretKey, $secretIv);
                }
            } else {
                if (!isset($sessionData['secretKey']) || empty($sessionData['secretKey'])) {
                    $response = isset($response['resultbody']) ? $response['resultbody'] : $response;
                }
            }
        }

        if (isset($body['requestresult']) && strtolower($body['requestresult']) == 'unsuccessfully') {
            if (Yii::$app->params['loggingAPIErrors']) {
                Yii::error(['request' => $postData, 'response' => $body], 'api');
            }
            if (isset($body['extendedinfo']) && ($body['extendedinfo'] == 'Session Not Found') || ($body['extendedinfo'] == 'Invalid Session Handle')) {
                unset($session['screenData']);
                Yii::$app->user->logout();
            }

            if (!empty($response['extendedinfo_ml']) && is_array($response['extendedinfo_ml'])) {
                ExtendedInfo::setExtendInfoML($response['extendedinfo_ml']);
            }
        }

        if (!empty($response['extendedinfo']) && is_array($response['extendedinfo'])) {
            ExtendedInfo::setExtendInfo($response['extendedinfo']);
        }

        if (isset($body['requestresult']) && strtolower($body['requestresult']) == 'unsuccessfully') {
            $response = null;
        }

        return $response;
    }

    /**
     * Request to API server with sessionhandle
     * @param array $postData - Data, for getting API result
     * @return bool|mixed|string
     */
    public function processData($postData = [])
    {
        if (($sessionData = self::getSessionData()) && !empty($sessionData['sessionhandle'])) {
            if (isset($sessionData['secretKey']) && isset($sessionData['secretIv'])) {
                $postData = self::AesEncrypt($postData, $sessionData['secretKey'], $sessionData['secretIv']);
            }

            return self::requestToApi(['requestbody' => $postData, 'sessionhandle' => $sessionData['sessionhandle']]);
        }

        return null;
    }

	public function processData2($postData = [])
    {
        if (($sessionData = self::getSessionData()) && !empty($sessionData['sessionhandle'])) {
            if (isset($sessionData['secretKey']) && isset($sessionData['secretIv'])) {
                $postData = self::AesEncrypt($postData, $sessionData['secretKey'], $sessionData['secretIv']);
            }

            return self::requestToApi2(['requestbody' => $postData, 'sessionhandle' => $sessionData['sessionhandle']]);
        }

        return null;
    }

    /**
     * Request to API server with prepare data
     * @param array $postData
     * @return null|static
     */
    protected static function getData($postData = array())
    {
        $model = new static();

        $postData = array_merge(['func_name' => $model::$dataAction], $postData);

        $attributes = $model->processData($model::preparePostData($postData));
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                $model->$attribute = $value;
            }
        } else {
            $model = null;
        }

        return $model;
    }

	protected static function getData2($postData = array())
    {
		//echo 'in AccountModel getData2 :: ';
		//echo ':: '.$model::$dataAction.' ::';

        $model = new static();

        $postData = array_merge(['func_name' => $model::$dataAction], $postData);

        $attributes = $model->processData2($model::preparePostData($postData));
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                $model->$attribute = $value;
            }
        } else {
            $model = null;
        }

        return $model;
    }

    /**
     * Prepare data for send request to API server
     * @param array $additionalPostData - additional data for request
     * @param null $funcName - function fo request
     * @return array
     */
    protected static function preparePostData($additionalPostData = array(), $funcName = null)
    {
        if ($funcName == UserAccount::$dataAction) {
            /** @var UserAccount $userData */
            $userData = UserAccount::getModelInstance();
            $postData = array(
                "uid" => (string)$userData->id,
                "upassword" => $userData->account_password,
            );
        } else {
            $postData = array();
        }

        return array_merge($postData, $additionalPostData);
    }

    /**
     * Cached data
     * @param $data
     */
    protected static function addToSession($data)
    {
        $existsData = isset(\Yii::$app->session['screenData']) ? \Yii::$app->session['screenData'] : [];
        \Yii::$app->session['screenData'] = array_merge($existsData, $data);
    }
}