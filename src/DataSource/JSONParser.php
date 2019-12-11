<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Exception\DataSourceExecutionException;
use Adimeo\DataSuite\Model\DataSource;

class JSONParser extends DataSource
{
    public function getDisplayName(): string
    {
        return 'JSON parser';
    }

    public function getOutputFields(): array
    {
        return array_map('trim', explode(',', $this->getSettings()['jsonFields']));
    }

    public function getSettingFields(): array
    {
        return [
            'jsonFields' => [
                'label' => 'JSON fields (comma separated)',
                'type' => 'string',
                'required' => true
            ]
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [
            'filePath' => [
                'label' => 'File path (can be an URL)',
                'type' => 'string',
                'required' => true
            ]
        ];
    }

    public function execute(array $args = [])
    {
        if (isset($args['filePath'])) {
            $filePath = $args['filePath'];
        } else {
            throw new DataSourceExecutionException('Missing file path!');
        }
        $arrContextOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        $json = file_get_contents($filePath, false, stream_context_create($arrContextOptions));
        if (!isset($json))
            throw new DataSourceExecutionException('Could not parse JSON file');
        $data = json_decode($json, true);
        if ($data == null) {
            $data = [];
        }
        $r = [];
        $fields = array_map('trim', explode(',', $this->getSettings()['jsonFields']));
        foreach ($data as $doc) {
            $tmp = [];
            foreach ($fields as $field) {
                if (isset($doc[$field]))
                    $tmp[$field] = $doc[$field];
            }
            if (!empty($tmp))
                $r[] = $tmp;
        }

        foreach ($r as $doc) {
            $this->index($doc);
        }
    }
}
