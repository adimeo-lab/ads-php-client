<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class PHPFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "PHP filter";
    }

    public function getSettingFields()
    {
        return [
            'php_code' => [
                'label' => 'PHP Code',
                'type' => 'textarea',
                'required' => true
            ]
        ];
    }

    public function getFields()
    {
        return ['return'];
    }

    public function getArguments()
    {
        return [];
    }

    private function evalCode(&$document, $code)
    {
        return eval($code);
    }

    public function execute(&$document, DataSource $datasource)
    {
        $settings = $this->getSettings();
        $return = NULL;
        if (isset($settings['php_code'])) {
            try {
                $return = $this->evalCode($document, $settings['php_code']);
            } catch (\Exception $ex) {
                $return = '';
            }
        }
        return ['return' => $return];
    }
}
