<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

class SecretAnswers extends BaseModel
{
    public static $dataLib = 'CodiacSDK.CommonArea';
    public static $dataAction = 'GetSAnswerList';
    public static $dataCreateAction = 'CreateSAnswer';

    public static function setData($userPk, array $answers)
    {
        $result = false;
        foreach($answers as $sqPk => $answer) {
            $result[] = (new static())->processData([
                'func_name' => self::$dataCreateAction,
                'func_param' => [
                    'patch_json' => [
                        'user_pk' => (string) $userPk,
                        'squestions_pk' => $sqPk,
                        'answer' => $answer
                    ],
                ],
                'lib_name' => 'CodiacSDK.CommonArea'
            ]);
        }

        return $result;
    }
}