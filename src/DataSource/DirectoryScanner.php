<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Exception\DataSourceExecutionException;
use Adimeo\DataSuite\Model\DataSource;

class DirectoryScanner extends DataSource
{
    public function getDisplayName(): string
    {
        return 'Directory scanner';
    }

    public function getOutputFields(): array
    {
        return [
            'absolute_path',
            'info'
        ];
    }

    public function getSettingFields(): array
    {
        return [
            'path' => [
                'label' => 'Directory path',
                'type' => 'string',
                'required' => true
            ]
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [
            'path' => [
                'label' => 'Directory path',
                'type' => 'string',
                'required' => true,
                'default_from_settings' => true
            ]
        ];
    }

    public function execute(array $args = [])
    {
        $path = $args['path'] ?? null;
        if (null === $path || !file_exists($path) || !is_dir($path)) {
            throw new DataSourceExecutionException($path.' is not a valid directory');
        }

        $path = realpath($path);
        $this->scanDirectory($path, function ($file) {
            $this->index([
                'absolute_path' => $file,
                'info' => pathinfo($file)
            ]);
        });
    }

    private function scanDirectory(string $path, callable $callable)
    {
        $content = scandir($path);
        foreach ($content as $c) {
            if ($c != '.' && $c != '..') {
                if (is_dir($path.DIRECTORY_SEPARATOR.$c)) {
                    $this->scanDirectory($path.DIRECTORY_SEPARATOR.$c, $callable);
                } else {
                    if ($callable != null) {
                        $callable($path.DIRECTORY_SEPARATOR.$c);
                    }
                }
            }
        }
    }
}
