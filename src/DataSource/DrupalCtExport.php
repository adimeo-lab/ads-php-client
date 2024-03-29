<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Exception\DataSourceExecutionException;
use Adimeo\DataSuite\Model\DataSource;
use GuzzleHttp\Client;

class DrupalCtExport extends DataSource
{
    public function getDisplayName(): string
    {
        return 'Drupal Ct Export';
    }

    public function getOutputFields(): array
    {
        return [
            'id',
            'export_id',
            'xml',
        ];
    }

    public function getSettingFields(): array
    {
        return [
            'drupalHost' => [
                'label' => 'Drupal Host',
                'type' => 'string',
                'required' => true
            ],
            'https' => [
                'label' => 'HTTPS ?',
                'type' => 'boolean',
                'required' => false
            ],
            'contentType' => [
                'label' => 'Content type restriction (E.g.: node|*)',
                'type' => 'string',
                'required' => false
            ]
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [];
    }

    public function execute(array $args = [])
    {
        $count = 0;
        try {
            if (isset($args['xml'])) {
                $xml = simplexml_load_string($args['xml']);
                $this->processXML($xml, $count);
                return;
            }

            $settings = $this->getSettings();

            $url = '//'.$settings['drupalHost'].'/ct/export';
            if (isset($settings['contentType']) && !empty($settings['contentType'])) {
                $url .= '?types='.$settings['contentType'];
                $url_sep = '&';
            } else {
                $url_sep = '?';
            }
            if (isset($settings['https']) && $settings['https']) {
                $url = 'https:'.$url;
            } else {
                $url = 'http:'.$url;
            }

            $this->getOutputManager()->writeln('Harvesting url '.$url);

            $client = new Client();
            $response = $client->request('GET', $url);

            $body = $response->getBody();
            if ($response->getStatusCode() == 200 && !empty($body)) {
                $xml = simplexml_load_string($body);
            } else {
                $xml = false;
            }

            $page = 1;
            while (count($xml->xpath('/entities/entity')) > 0) {
                $this->processXML($xml, $count);
                $page++;

                $client = new Client();
                $response = $client->request('GET', $url.$url_sep.'page='.$page);

                $body = $response->getBody();
                if ($response->getStatusCode() == 200 && !empty($body)) {
                    $xml = simplexml_load_string($body);
                } else {
                    $xml = false;
                }

                $this->getOutputManager()->writeln('Harvesting url '.$url.$url_sep.'page='.$page);
            }
        } catch (\Exception $ex) {
            throw new DataSourceExecutionException('Exception ==> '.$ex->getMessage());
        }

        $this->getOutputManager()->writeln('Found '.$count.' documents');
    }

    private function processXML(\SimpleXMLElement $xml, int &$count): void
    {
        foreach ($xml->xpath('/entities/entity') as $entity) {
            /* @var $node \SimpleXMLElement */
            $id = count($entity->xpath('@id')) > 0 ? (string) $entity->xpath('@id')[0] : null;
            $export_id = count($entity->xpath('export-id')) > 0 ? (string) $entity->xpath('export-id')[0] : null;
            $entity_type = count($entity->xpath('entity-type')) > 0 ? (string) $entity->xpath('entity-type')[0] : null;
            $bundle = count($entity->xpath('bundle')) > 0 ? (string) $entity->xpath('bundle')[0] : null;

            $settings = $this->getSettings();

            $types = isset($settings['contentType']) ? $settings['contentType'] : '';
            $criteria = [];
            foreach (explode('||', $types) as $et) {
                if (count(explode('|', $et)) == 2) {
                    $ett = explode('|', $et)[0];
                    $bundles = explode(',', explode('|', $et)[1]);
                    $criteria[$ett] = $bundles;
                }
            }

            $match = false;
            foreach ($criteria as $et => $bundles) {
                if ($et == $entity_type) {
                    foreach ($bundles as $b) {
                        if ($b == '*' || $b == $bundle) {
                            $match = true;
                        }
                    }
                }
            }

            if ($match && $id != null && $export_id != null) {
                $this->getOutputManager()->writeln(($count + 1).'/ Indexing '.$export_id.' ==> Type = '.(string) $entity->xpath('bundle')[0]);
                $this->index([
                    'id' => $id,
                    'export_id' => $export_id,
                    'xml' => simplexml_load_string($entity->asXML()),
                ]);
                $count++;
            }
        }
    }
}
