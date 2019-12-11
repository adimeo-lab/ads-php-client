<?php

namespace Adimeo\DataSuite\Model;

use Adimeo\DataSuite\Index\IndexManager;

interface Exportable
{
    /**
     * @param IndexManager $indexManager
     *
     * @return string
     */
    public function export(IndexManager $indexManager);
}
