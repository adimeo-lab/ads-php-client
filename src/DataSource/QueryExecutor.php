<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Model\DataSource;

class QueryExecutor extends DataSource
{
    public function getDisplayName(): string
    {
        return 'Query Executor';
    }

    public function getOutputFields(): array
    {
        return ['id', 'doc'];
    }

    public function getSettingFields(): array
    {
        return [
            'mapping' => [
                'label' => 'Mapping',
                'type' => 'choice',
                'bound_to' => 'mapping',
                'required' => true
            ],
            'query' => [
                'label' => 'Query (JSON)',
                'type' => 'textarea',
                'required' => true
            ]
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [];
    }

    public function execute(array $args = [])
    {
        $settings = $this->getSettings();
        $index = strpos($settings['mapping'], '.') !== 0 ? explode('.', $settings['mapping'])[0] : '.'.explode('.', $settings['mapping'])[1];
        $mapping = strpos($settings['mapping'], '.') !== 0 ? explode('.', $settings['mapping'])[1] : explode('.', $settings['mapping'])[2];
        $this->execSearch($index, $mapping);
    }

    private function execSearch($index, $mapping, $from = 0)
    {
        $size = 100;
        $settings = $this->getSettings();
        $res = $this->getExecIndexManager()->search($index, json_decode($settings['query'], TRUE), $from, $size, $mapping);
        if (isset($res['hits']['total'])) {
            $total = $res['hits']['total'];
            if (isset($res['hits']['hits'])) {
                foreach ($res['hits']['hits'] as $hit) {
                    $id = $hit['_id'];
                    $doc = $hit['_source'];
                    $this->index([
                        'id' => $id,
                        'doc' => $doc
                    ]);
                }
            }
            if ($from < $total) {
                $this->execSearch($index, $mapping, $from + $size);
            }
        }
    }
}
