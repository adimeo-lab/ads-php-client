<?php

namespace Adimeo\DataSuite\Index;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class BackupsManager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * BackupManager constructor.
     *
     * @param $elasticsearchServerUrl
     */
    public function __construct($elasticsearchServerUrl)
    {
        $clientBuilder = new ClientBuilder();
        if (!defined('JSON_PRESERVE_ZERO_FRACTION')) {
            $clientBuilder->allowBadJSONSerialization();
        }
        $clientBuilder->setHosts([$elasticsearchServerUrl]);
        $this->client = $clientBuilder->build();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get all repositories
     *
     * @return array
     */
    public function getBackupsRepositories()
    {
        return $this->getClient()->snapshot()->getRepository(['repository' => '_all']);
    }

    /**
     * Get a repository
     *
     * @param $repositoryName
     *
     * @return array
     */
    public function getRepository($repositoryName)
    {
        return $this->getClient()->snapshot()->getRepository(['repository' => $repositoryName]);
    }

    /**
     * Create a repository
     *
     * @param $data
     */
    public function createRepository($data)
    {
        $params = [
            'repository' => preg_replace("/[^A-Za-z0-9]/", '_', strtolower($data['name'])),
            'body' => [
                'type' => $data['type'],
                'settings' => [
                    'location' => $data['location'],
                    'compress' => $data['compress'],
                ]
            ]
        ];
        $this->getClient()->snapshot()->createRepository($params);
    }

    /**
     * Delete a repository
     *
     * @param $name
     *
     * @return array
     */
    public function deleteRepository($name)
    {
        return $this->getClient()->snapshot()->deleteRepository(['repository' => $name]);
    }

    /**
     * Get all snapshots
     *
     * @param $repoName
     *
     * @return array
     */
    public function getSnapshots($repoName)
    {
        return $this->getClient()->snapshot()->get(['repository' => $repoName, 'snapshot' => '_all']);
    }

    /**
     * Get a snapshot
     *
     * @param $repositoryName
     * @param $snapshotName
     *
     * @return string|null
     */
    public function getSnapshot($repositoryName, $snapshotName)
    {
        $repository = $this->getClient()->snapshot()->get([
            'repository' => $repositoryName,
            'snapshot' => $snapshotName
        ]);

        return (isset($repository['snapshots'][0])) ? $repository['snapshots'][0] : null;
    }

    /**
     * Create a snapshot
     *
     * @param $repositoryName
     * @param $snapshotName
     * @param $indexes
     * @param bool $ignoreUnavailable
     * @param bool $includeGlobalState
     */
    public function createSnapshot($repositoryName, $snapshotName, $indexes, $ignoreUnavailable = true, $includeGlobalState = false)
    {
        $this->getClient()->snapshot()->create([
            'repository' => $repositoryName,
            'snapshot' => preg_replace("/[^A-Za-z0-9]/", '_', strtolower($snapshotName)),
            'body' => [
                'indices' => implode(',', $indexes),
                'ignore_unavailable' => $ignoreUnavailable,
                'include_global_state' => $includeGlobalState,
            ]
        ]);
    }

    /**
     * Delete a snapshot
     *
     * @param $repositoryName
     * @param $snapshotName
     *
     * @return array
     */
    public function deleteSnapshot($repositoryName, $snapshotName)
    {
        return $this->getClient()->snapshot()->delete([
            'repository' => $repositoryName,
            'snapshot' => $snapshotName
        ]);
    }

    /**
     * Restore a snapshot
     *
     * @param $repositoryName
     * @param $snapshotName
     * @param $params
     */
    public function restoreSnapshot($repositoryName, $snapshotName, $params)
    {
        $body = [];
        if (isset($params['indexes']) && !empty($params['indexes'])) {
            $body['indices'] = $params['indexes'];
        }
        if (isset($params['ignoreUnavailable'])) {
            $body['ignore_unavailable'] = $params['ignoreUnavailable'];
        }
        if (isset($params['includeGlobalState'])) {
            $body['include_global_state'] = $params['includeGlobalState'];
        }
        if (isset($params['renamePattern']) && !empty($params['renamePattern']) && $params['renamePattern'] != null) {
            $body['rename_pattern'] = $params['renamePattern'];
        }
        if (isset($params['renameReplacement']) && !empty($params['renameReplacement']) && $params['renameReplacement'] != null) {
            $body['rename_replacement'] = $params['renameReplacement'];
        }
        $this->getClient()->snapshot()->restore([
            'repository' => $repositoryName,
            'snapshot' => $snapshotName,
            'body' => $body
        ]);
    }
}
