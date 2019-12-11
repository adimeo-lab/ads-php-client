<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class XMLParserFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "XML Parser";
    }

    public function getFields()
    {
        return ['xpath'];
    }

    public function getSettingFields()
    {
        return [];
    }

    public function getArguments()
    {
        return [
            'xml' => 'XML source',
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        try {
            $xml = $this->getArgumentValue('xml', $document);
            if (file_exists($xml)) {
                $xml = file_get_contents($xml);
            }
            $doc = new \DOMDocument();
            $doc->loadXML($xml);

            $xpath = new \DOMXPath($doc);
            $result = $xpath->query("//namespace::*");
            foreach ($result as $node) {
                if ($node->nodeName == 'xmlns') {
                    $xpath->registerNamespace('vendor', $node->nodeValue);
                }
            }

            return ['xpath' => $xpath];
        } catch (\Exception $ex) {
            return ['xpath' => null];
        }
    }
}
