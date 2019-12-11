<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class ConcatenateFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Concatenate";
    }

    public function getFields()
    {
        return ['result'];
    }

    public function getSettingFields()
    {
        return [
            'separator' => [
                'label' => 'Separator',
                'type' => 'string',
                'trim' => false,
                'required' => true
            ]
        ];
    }

    public function getArguments()
    {
        return [
            'field_1' => 'Field 1',
            'field_2' => 'Field 2'
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        $field1 = $this->getArgumentValue('field_1', $document);
        $field2 = $this->getArgumentValue('field_2', $document);
        $settings = $this->getSettings();
        $separator = isset($settings['separator']) ? $settings['separator'] : '';
        return ['result' => $field1.$separator.$field2];
    }
}
