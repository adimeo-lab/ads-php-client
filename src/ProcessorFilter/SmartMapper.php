<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class SmartMapper extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Smart mapper";
    }

    public function getSettingFields()
    {
        return [
            'force_index' => [
                'label' => 'Force indexing all fields',
                'type' => 'boolean',
                'required' => false
            ]
        ];
    }

    public function getFields()
    {
        return ['smart_array'];
    }

    public function getArguments()
    {
        return ['source_array' => 'Source array'];
    }

    public function execute(&$document, DataSource $datasource)
    {
        return ['smart_array' => $this->getArgumentValue('source_array', $document)];
    }
}
