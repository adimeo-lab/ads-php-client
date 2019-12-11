<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class DefineConstantFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Define constant value";
    }

    public function getFields()
    {
        return ['value'];
    }

    public function getSettingFields()
    {
        return [
            'value' => [
                'label' => 'Value',
                'type' => 'string',
                'required' => true
            ]
        ];
    }

    public function getArguments()
    {
        return [];
    }

    public function execute(&$document, DataSource $datasource)
    {
        $settings = $this->getSettings();
        return ['value' => $settings['value']];
    }
}
