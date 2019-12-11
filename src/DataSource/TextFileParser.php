<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Exception\DataSourceExecutionException;
use Adimeo\DataSuite\Model\DataSource;

class TextFileParser extends DataSource
{
    public function getDisplayName(): string
    {
        return 'Text file Parser';
    }
    public function getOutputFields(): array
    {
        return ['line'];
    }

    public function getSettingFields(): array
    {
        return [
            'filePath' => [
                'label' => 'File path (can be an URL)',
                'type' => 'string',
                'required' => true
            ],
            'linesToSkip' => [
                'label' => 'Lines to skip',
                'type' => 'integer',
                'required' => false,
                'default' => 0
            ]
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [
            'filePath' => [
                'label' => 'File path (can be an URL)',
                'type' => 'string',
                'required' => true,
                'default_from_settings' => true
            ]
        ];
    }

    public function execute(array $args = [])
    {
        $filePath = $args['filePath'];
        $count = 0;
        $fp = fopen($filePath, "r");
        if ($fp) {
            while (($line = fgets($fp)) !== false) {
                if ($count >= $this->getSettings()['linesToSkip']) {
                    $line = trim($line);
                    $this->getOutputManager()->writeln('Processing line '.($count + 1));
                    $this->index(['line' => $line]);
                }
                $count++;
            }
            fclose($fp);
        } else {
            throw new DataSourceExecutionException('Error opening file "'.$this->getSettings()['filePath'].'"');
        }
    }
}
