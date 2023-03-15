<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;


interface RecordDataInterface
{
    public function __construct(RecordManagerInterface $recordManager, array $data);

    public function getData();
}