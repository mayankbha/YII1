<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;


use app\components\_FormattedHelper;

class RecordManager implements RecordManagerInterface
{
    private $pk;
    private $library;
    private $groupingFunc = self::ITEM_ATTR_GET_FUNC;

    private $aliasFrameworkPK;
    private $aliasFramework = [
        'enable' => false,
        'request_primary_table' => null
    ];

    public function __construct($library)
    {
        $this->library = $library;
    }

    public function setPK(array $pk)
    {
        $this->pk = $pk;
        return $this;
    }

    public function setGroupingFuncType($type)
    {
        $this->groupingFunc = $type;
        return $this;
    }

    public function setAliasFrameworkInfo(array $info)
    {
        if (isset($info['enable']) && $info['enable'] = filter_var($info['enable'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
            $this->aliasFramework = $info;
        }

        return $this;
    }

    public function setAliasFrameworkPK(array $pk)
    {
        $this->aliasFrameworkPK  = $pk;
        return $this;
    }

    public function getLibrary()
    {
        return $this->library;
    }

    public function getPK()
    {
        return implode(self::PK_DELIMITER, $this->pk);
    }

    public function getGroupingFuncType()
    {
        return ($this->isUseAliasFramework()) ? $this->groupingFunc : self::ITEM_ATTR_GET_FUNC;
    }

    public function getAliasFrameworkInfo()
    {
        return $this->aliasFramework;
    }

    public function getAliasFrameworkPK()
    {
        return array_values($this->aliasFrameworkPK);
    }

    public function isUseAliasFramework()
    {
        return $this->aliasFramework['enable'];
    }

    public function prepareItem($item)
    {
        $formatted = new _FormattedHelper();

        if (isset($item['saveFormat']) && !empty($item['value'])) {
            $item['value'] = $formatted->revertDateTime($item['value'], $item['saveFormat']);
        }

        return $item;
    }
}