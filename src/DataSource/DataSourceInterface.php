<?php

namespace Adimeo\DataSuite\DataSource;

interface DataSourceInterface
{
    public function getDisplayName(): string;

    public function getOutputFields(): array;

    /**
     * @return array
     */
    public function getSettingFields(): array;

    /**
     * @return array
     */
    public function getExecutionArgumentFields(): array;

    /**
     * @param array $args
     */
    public function execute(array $args = []);
}
