<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Model\DataSource;

class WebCrawler extends DataSource
{
    public function getDisplayName(): string
    {
        return 'Web crawler';
    }

    public function getOutputFields(): array
    {
        return [
            'title',
            'html',
            'url',
        ];
    }

    public function getSettingFields(): array
    {
        return [];
    }

    public function getExecutionArgumentFields(): array
    {
        return [];
    }

    public function execute(array $args = [])
    {
        //Nothing to do because it is based on call back
    }

    public function handleDataFromCallback($document)
    {
        $this->index($document);
        $this->emptyBatchStack();
    }
}
