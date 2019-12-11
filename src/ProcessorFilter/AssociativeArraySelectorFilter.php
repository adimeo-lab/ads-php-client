<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class AssociativeArraySelectorFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Associative array selector";
    }

    public function getFields()
    {
        return ['value'];
    }

    public function getSettingFields()
    {
        return [
            'key' => [
                'label' => 'Key',
                'type' => 'string',
                'required' => true
            ]
        ];
    }

    public function getArguments()
    {
        return [
            'array' => 'Input array'
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        $settings = $this->getSettings();
        $array = $this->getArgumentValue('array', $document);
        if (strpos($settings['key'], '##') === FALSE) {
            if ($array != null && is_array($array) && isset($array[$settings['key']])) {
                return ['value' => $array[$settings['key']]];
            }
        } else {
            $keys = explode('##', $settings['key']);
            for ($i = 0; $i < count($keys); $i++) {
                if ($i == 0) {
                    if (isset($array[$keys[$i]])) {
                        $tmp = $array[$keys[$i]];
                    }
                } else {
                    if (isset($tmp[$keys[$i]])) {
                        $tmp = $tmp[$keys[$i]];
                    }
                }
            }
            if (isset($tmp))
                return ['value' => $tmp];
        }
        return ['value' => null];
    }
}
