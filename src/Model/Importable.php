<?php

namespace Adimeo\DataSuite\Model;

use Adimeo\DataSuite\Index\IndexManager;

interface Importable
{
    /**
     * @param string $data
     * @param IndexManager $indexManager
     * @param boolean $override
     *
     * @return PersistentObject
     */
    static function import($data, IndexManager $indexManager, $override = false);
}
