<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class TikaFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Tika filter";
    }

    public function getSettingFields()
    {
        return [
            'java_path' => [
                'type' => 'text',
                'label' => 'Path to java (E.g.: /usr/bin/java)',
                'required' => true
            ],
            'tika_path' => [
                'type' => 'text',
                'label' => 'Path to Tika JAR file',
                'required' => true
            ],
            'output_format' => [
                'type' => 'choice',
                'label' => 'Output format',
                'required' => true,
                'choices' => [
                    'Select >' => '',
                    'HTML' => 'h',
                    'Plain text' => 't',
                ]
            ]
        ];
    }

    public function getFields()
    {
        return ['output'];
    }

    public function getArguments()
    {
        return [
            'filePath' => 'File path'
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        $output = [];
        exec('"'.$this->getSettings()['java_path'].'" -jar "'.$this->getSettings()['tika_path'].'" -'.$this->getSettings()['output_format'].' "'.$this->getArgumentValue('filePath', $document).'" 2>/dev/null', $output);

        return [
            'output' => implode("\n", $output)
        ];
    }
}
