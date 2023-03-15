<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;


interface RecordManagerInterface
{
    const ITEM_ATTR_GET_FUNC = 'getFunc';
    const ITEM_ATTR_CREATE_FUNC = 'createFunc';
    const ITEM_ATTR_UPDATE_FUNC = 'updateFunc';
    const ITEM_ATTR_DELETE_FUNC = 'deleteFunc';
    const ITEM_ATTR_AF_PK = 'afPkPart';

    const PK_DELIMITER = ';';

    public function __construct($library);

    public function setPK(array $pk);

    public function setGroupingFuncType($type);

    public function setAliasFrameworkInfo(array $info);

    public function setAliasFrameworkPK(array $pk);


    public function getLibrary();

    public function getPK();

    public function getGroupingFuncType();

    public function getAliasFrameworkInfo();

    public function getAliasFrameworkPK();


    public function isUseAliasFramework();

    public function prepareItem($item);
}