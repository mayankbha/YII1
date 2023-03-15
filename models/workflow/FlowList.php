<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\workflow;

use app\models\BaseModel;

class FlowList extends BaseModel
{
    public static $dataLib = 'CodiacSDK.AdminScreens';
    public static $dataAction = 'GetFlowList';
}