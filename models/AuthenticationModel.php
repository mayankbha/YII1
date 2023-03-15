<?php

namespace app\models;

use app\models\BaseModel;

use Yii;

class AuthenticationModel extends AccountModel
{
    const SEND_AUTH_TYPE_CODE = 'Authenticate';
    const CHECK_AUTH_TYPE_CODE = 'CheckAuthCode';
    const SEND_CODE_ACTION = 'AuthenticateNoLogin';
    const RESET_PASSWORD_ACTION = 'PasswordReset';

    const AUTH_SOURCE_EMAIL = 'AuthType.E';
    const AUTH_SOURCE_SMS = 'AuthType.S';

    private static $availableSource = [
        self::AUTH_SOURCE_EMAIL,
        self::AUTH_SOURCE_SMS
    ];

    public static function sendAuthTypeCode($type) {
        $postData = [
            'func_name' => self::SEND_AUTH_TYPE_CODE,
            'func_param' => [
                'authentication_source' => $type
            ]
        ];

        $model = new static();
        $result = $model->processData($postData);

        if (!empty($result['requestresult'])) {
            if (!empty($result['squestion'])) {
                return $result['squestion'];
            }

            return true;
        }

        return false;
    }

    public static function checkAuthTypeCode($type, $code) {
        $postData = [
            'func_name' => self::CHECK_AUTH_TYPE_CODE,
            'func_param' => [
                'authentication_code' => $code,
                'authentication_source' => $type
            ]
        ];

        $model = new static();
        $result = $model->processData($postData);

        return !empty($result['requestresult']);
    }

    public static function sendAuthCode($source, $username)
    {
        if (!in_array($source, self::$availableSource)) {
            throw new \Exception('Unknown authenticate source');
        }

        return (new static())->processData([
            'func_name' => self::SEND_CODE_ACTION,
            'func_param' => [
                'authentication_source' => $source,
                'to' => $username
            ]
        ]);
    }

    public static function sendNewPassword($username, $code)
    {
        return (new static())->processData([
            'func_name' => self::RESET_PASSWORD_ACTION,
            'func_param' => [
                'authentication_code' => $code,
                'to' => $username
            ]
        ]);
    }

    public function processData($postData = array())
    {
        $session = Yii::$app->session;
        $screenData = $session['screenData'];
        if (!isset($screenData['sessionData'])) {
            $sessionRequestResult = self::processSessionData();
            self::addToSession(array('sessionData' => $sessionRequestResult));
        }

        return parent::processData($postData);
    }

	public function resetPassword($account_name, $email, $notification_template) {
		$requestbody = [
						'func_name' => 'PasswordResetNoAuthentication',
						'func_param' => [
							'username' => $account_name,
							'email' => $email,
							'notification' => $notification_template
						]
					];

		$model = new static();
		$sessionData = $model->getSessionData();

		if ($sessionData['secretKey'] && $sessionData['secretIv']) {
            $requestbody = $model->AesEncrypt($requestbody, $sessionData['secretKey'], $sessionData['secretIv']);
        }

        $data_string = [
            "requestbody" => $requestbody,
            "sessionhandle" => $sessionData['sessionhandle']
        ];

		//echo json_encode($data_string);

        $ch = curl_init(static::getSourceLink());

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data_string)),
            'User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip,deflate'
        ));

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_string));

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        curl_close($ch);

		//echo '<pre> $response :: '; print_r($response);

		return $response;
	}

	public function registration($postData) {
		$group_membership_decode = json_decode($postData['group_membership']);
		$group_membership = implode(';', $group_membership_decode);

		$document_groups_decode = json_decode($postData['document_groups']);
		$document_groups = implode(';', $document_groups_decode);

		if($postData['account_status'] == 1)
			$account_status = 'active';
		else
			$account_status = '0';

		$user_details = array(
							'user_name' => $postData['user_name'],
							'account_name' => $postData['account_name'],
							'email' => $postData['email'],
							'mobile_number' => $postData['mobile_number'],
							'account_status' => $account_status,
							'account_type' => $postData['account_type'],
							'tenant_code' => $postData['tenant_code'],
							'user_type' => $postData['user_type'],
							'primary_group' => $postData['default_group'],
							'group_area' => $group_membership,
							'document_group' => $document_groups,
							'email_template_user' => $postData['notification_user_type_email_template'],
							'email_template_pass' => $postData['notification_password_type_email_template'],
						);

		if($postData['identify_verification'] == 1) {
			$identify_verification['primary_table'] = $postData['screen_self_registration_primary_table'];

			$identify_verification['fields'] = array();

			if($postData['field1'] != '') {
				if($postData['field1_security_filter1'] != '')
					$security_filter_field1 = $postData['field1_security_filter1'];
				else if($postData['field1_security_filter2'] != '')
					$security_filter_field1 = $postData['field1_security_filter2'];
				else if($postData['field1_security_filter3'] != '')
					$security_filter_field1 = $postData['field1_security_filter3'];

				$identify_verification['fields'][] = array('name' => $postData['field1_database_column'], 'value' => $postData['field1'], 'security_filter' => (int) $security_filter_field1);
			}

			if($postData['field2'] != '') {
				if($postData['field2_security_filter1'] != '')
					$security_filter_field2 = $postData['field2_security_filter1'];
				else if($postData['field2_security_filter2'] != '')
					$security_filter_field2 = $postData['field2_security_filter2'];
				else if($postData['field2_security_filter3'] != '')
					$security_filter_field2 = $postData['field2_security_filter3'];

				$identify_verification['fields'][] = array('name' => $postData['field2_database_column'], 'value' => $postData['field2'], 'security_filter' => (int) $security_filter_field2);
			}

			if($postData['field3'] != '') {
				if($postData['field3_security_filter1'] != '')
					$security_filter_field3 = $postData['field3_security_filter1'];
				else if($postData['field3_security_filter2'] != '')
					$security_filter_field3 = $postData['field3_security_filter2'];
				else if($postData['field3_security_filter3'] != '')
					$security_filter_field3 = $postData['field3_security_filter3'];

				$identify_verification['fields'][] = array('name' => $postData['field3_database_column'], 'value' => $postData['field3'], 'security_filter' => (int) $security_filter_field3);
			}

			if($postData['field4'] != '') {
				if($postData['field4_security_filter1'] != '')
					$security_filter_field4 = $postData['field4_security_filter1'];
				else if($postData['field4_security_filter2'] != '')
					$security_filter_field4 = $postData['field4_security_filter2'];
				else if($postData['field4_security_filter3'] != '')
					$security_filter_field4 = $postData['field4_security_filter3'];

				$identify_verification['fields'][] = array('name' => $postData['field4_database_column'], 'value' => $postData['field4'], 'security_filter' => (int) $security_filter_field4);
			}
		} else {
			$identify_verification = null;
		}

		if($postData['account_protection'] == 1) {
			$account_protection = array();

			if($postData['answer1'] != '')
				$account_protection[$postData['security_question1']] = $postData['answer1'];

			if($postData['answer2'] != '')
				$account_protection[$postData['security_question2']] = $postData['answer2'];

			if($postData['answer3'] != '')
				$account_protection[$postData['security_question3']] = $postData['answer3'];

			if($postData['answer4'] != '')
				$account_protection[$postData['security_question4']] = $postData['answer4'];
		} else {
			$account_protection = null;
		}

		$requestbody = [
						'func_name' => 'SelfRegistrationNoAuthentication',
						'func_param' => [
							'user_details' => $user_details,
							'identity_verification' => $identify_verification,
							'account_protection' => $account_protection
						]
					];

		$model = new static();
		$sessionData = $model->getSessionData();

		if ($sessionData['secretKey'] && $sessionData['secretIv']) {
            $requestbody = $model->AesEncrypt($requestbody, $sessionData['secretKey'], $sessionData['secretIv']);
        }

        $data_string = [
            "requestbody" => $requestbody,
            "sessionhandle" => $sessionData['sessionhandle']
        ];

		//echo json_encode($data_string);

        $ch = curl_init(static::getSourceLink());

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data_string)),
            'User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip,deflate'
        ));

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_string));

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        curl_close($ch);

		return $response;
	}
}