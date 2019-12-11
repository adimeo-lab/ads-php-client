<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class ArrayImplodeFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Array implode";
    }

    public function getFields()
    {
        return ['string'];
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
            'array' => 'Array to implode',
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        $array = $this->getArgumentValue('array', $document);
        $settings = $this->getSettings();
        $separator = isset($settings['separator']) ? $settings['separator'] : '';
        return ['string' => implode($separator, $array)];
    }
}
