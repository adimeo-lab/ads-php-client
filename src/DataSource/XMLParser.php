<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Exception\DataSourceExecutionException;
use Adimeo\DataSuite\Model\DataSource;
use GuzzleHttp\Client;

class XMLParser extends DataSource
{
    public function getDisplayName(): string
    {
        return 'XML Parser';
    }

    public function getOutputFields(): array
    {
        return [
            'global_doc',
            'doc',
        ];
    }

    public function getSettingFields(): array
    {
        return [
            'url' => [
                'label' => 'XML File url',
                'type' => 'string',
                'required' => true
            ],
            'xpath' => [
                'label' => 'XPath',
                'type' => 'string',
                'required' => true
            ],
            'xpathNamespaces' => [
                'label' => 'XPath Namespaces to register',
                'type' => 'string',
                'required' => false
            ],
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [
            'url' => [
                'label' => 'File (if not set in settings)',
                'type' => 'file',
                'required' => false
            ]
        ];
    }

    public function execute(array $args = [])
    {
        $url = $this->getSettings()['url'];
        if (isset($args['url']) && !empty($args['url'])) {
            if (strpos($args['url'], '/') === 0) {
                $url = "file://".$args['url'];
            } else {
                $url = $args['url'];
            }
        }
        if (strpos($url, 'file://') === 0) {
            $content = file_get_contents($url);
        } else {
            $client = new Client();
            $response = $client->request('GET', $url);
            $content = $response->getBody();
        }
        $xml = simplexml_load_string($content);
        $count = 0;
        if ($xml) {
            if (isset($this->getSettings()['xpathNamespaces']) && !empty($this->getSettings()['xpathNamespaces'])) {
                $nss = explode(',', $this->getSettings()['xpathNamespaces']);
                foreach ($nss as $ns) {
                    $prefix = substr($ns, 0, strpos($ns, ':'));
                    $url = substr($ns, strpos($ns, ':') + 1);
                    $xml->registerXpathNamespace($prefix, $url);
                }
            }
            $docs = $xml->xpath($this->getSettings()['xpath']);

            $this->getOutputManager()->writeln('Found '.count($docs).' documents');

            foreach ($docs as $doc) {
                foreach ($xml->getNamespaces(true) as $prefix => $ns) {
                    if (!empty($prefix)) {
                        $doc->addAttribute($prefix.':ads', 'ads', $prefix);
                    }
                }
                $this->index([
                    'global_doc' => $xml,
                    'doc' => simplexml_load_string($doc->asXML())
                ]);
                $count++;
            }
        } else {
            throw new DataSourceExecutionException('Cannot load XML from '.$url);
        }
        $this->getOutputManager()->writeln('Processed '.$count.' documents');
    }
}
