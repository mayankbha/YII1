<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

class DocumentGroup extends BaseModel
{
    const CACHE_KEY = 'documentGroups';
    const CACHE_KEY_FAMILY = 'documentFamilies';

    const ACCESS_RIGHT_FULL = 'U';
    const ACCESS_RIGHT_READ = 'R';
    const ACCESS_RIGHT_DENIED = 'N';
	const ACCESS_RIGHT_ADMIN = 'A';

    public static $dataLib = 'CodiacSDK.AdminGroups';
    public static $dataAction = 'GetDocumentGroupList';

    public static function getAccessPermission($family, $category)
    {
        if (($groupsData = self::getGroups()) && !empty($groupsData[$family][$category])) {
            return $groupsData[$family][$category]['access_right'];
        }

        return self::ACCESS_RIGHT_DENIED;
    }

    public static function getGroups()
    {
        $session = Yii::$app->session;
        if (empty($session[self::CACHE_KEY])) {
            if (($settings = UserAccount::getSettings()) && !empty($settings->document_group)) {
                $userGroups = explode(';', $settings->document_group);
                if (($serverGroups = self::getData(['group_name' => $userGroups])) && !empty($serverGroups->list)) {
                    $session[self::CACHE_KEY] = ArrayHelper::index($serverGroups->list, 'document_category', ['document_family']);
                }
            }
        }

        return !empty($session[self::CACHE_KEY]) ? $session[self::CACHE_KEY] : null;
    }
}