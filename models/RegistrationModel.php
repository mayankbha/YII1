<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

class RegistrationModel extends AccountModel
{
    const PREPARE_DATA_ACTION = 'PrepareForRegistration';
    const GET_DATA_ACTION = 'GetRegistrationData';
    const SET_DATA_ACTION = 'SetRegistrationData';

    public static function setData($data)
    {
        return (new static())->processData([
            'func_name' => self::SET_DATA_ACTION,
            'func_param' => [
                'patch_json' => $data,
            ]
        ]);
    }

    public static function prepareData()
    {
        return (new static())->processData([
            'func_name' => self::PREPARE_DATA_ACTION,
            'func_param' => null
        ]);
    }

    public static function getInfo()
    {
        return (new static())->processData([
            'func_name' => self::GET_DATA_ACTION,
            'func_param' => null
        ]);
    }

    public static function getInfoByParams($accountType, $tenant, $userType)
    {
        return (new static())->processData([
            'func_name' => self::GET_DATA_ACTION,
            'func_param' => [
                'account_type' => $accountType,
                'tenant' => $tenant,
                'user_type' => $userType
            ]
        ]);
    }

    public static function prepareSetData(array $postData, array $prepareData = [])
    {
        $postArray = [];
        foreach ($postData as $key => $value) {
            $key = explode(';', $key);
            $key = empty($key[1]) ? $key[0] : $key[1];

            $postArray[$key] = $value;
        }

        foreach ($prepareData as $key => $item) {
            $postArray[$key] = $item;
        }

        $postArray['account_status'] = 'inactive';

        if (isset($postArray['account_password'])) {
            $postArray['account_password'] = UserAccount::encodePassword($postArray['account_password']);
        }

        if (isset($postArray['tenant_code']) && $tenantInfo = Tenant::getModel($postArray['tenant_code'])) {
            if (!empty($tenantInfo['StyleTemplate'])) {
                $postArray['style_template'] = $tenantInfo['StyleTemplate'];
            }
        }

        return $postArray;
    }
}