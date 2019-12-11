<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class DebugFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Debug filter";
    }

    public function getFields()
    {
        return [];
    }

    public function getSettingFields()
    {
        return [
            'fields_to_dump' => [
                'label' => 'Fields to dump',
                'type' => 'string',
                'required' => false
            ],
            'no_index' => [
                'label' => 'Prevent indexing',
                'type' => 'boolean',
                'required' => false
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

        if (isset($settings['fields_to_dump'])) {
            $fields = explode(',', $settings['fields_to_dump']);
            $datasource->getOutputManager()->writeLn('');
            $datasource->getOutputManager()->writeLn('####################################################');
            foreach ($fields as $field) {
                if (isset($document[$field])) {
                    $datasource->getOutputManager()->writeLn('FIELD: '.$field);
                    if (is_array($document[$field]))
                        $datasource->getOutputManager()->dumpArray($document[$field]);
                    else
                        $datasource->getOutputManager()->writeLn($document[$field]);
                    $datasource->getOutputManager()->writeLn('');
                    $datasource->getOutputManager()->writeLn('----------------------------------------------------');
                }
            }
            $datasource->getOutputManager()->writeLn('####################################################');
            $datasource->getOutputManager()->writeLn('');
        }

        if (isset($settings['no_index']) && $settings['no_index']) {
            $document = [];
        }
        return [];
    }
}
