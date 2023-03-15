<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

class Tenant extends BaseModel
{
    public static $dataLib = 'CodiacSDK.AdminUsers';
    public static $dataAction = 'GetTenantList';
}