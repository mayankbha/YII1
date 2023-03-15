<?php
namespace app\models;

class LogoModel extends BaseModel
{
    public static $dataLib = 'CodiacSDK.AdminUsers';
    public static $dataAction = 'GetLogoList';

    public static function getModel($pk, $delimiter = '.')
    {
        if (!empty($pk)) {
            $fieldList = [];
            $pk = explode($delimiter, $pk);
            $pkList = CustomLibs::getPK(static::$dataLib, static::$dataAction);
            if (!empty($pkList)) {
                foreach ($pkList as $key => $item) {
                    if (empty($pk[$key])) continue;
                    $fieldList[$item] = [$pk[$key]];
                }

                if (($selfModel = static::getData($fieldList)) && !empty($selfModel->list[0])) {
                    return $selfModel->list[0];
                }
            }
        }

        return null;
    }
}