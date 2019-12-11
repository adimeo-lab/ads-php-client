<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class XMLParserToArrayFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "XML Parser to array";
    }

    public function getFields()
    {
        return ['array'];
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
            $xml = simplexml_load_string($this->getArgumentValue('xml', $document));

            if ($xml) {
                $array = $this->serializeXml($xml);
            } else {
                return ['array' => []];
            }

            return ['array' => $array];
        } catch (\Exception $ex) {
            return ['array' => []];
        }
    }

    private function serializeXml(\SimpleXMLElement $xml)
    {
        $r = [];
        foreach ($xml->attributes() as $attr) {
            /** @var \SimpleXMLElement $attr */
            $r['@attributes'][$attr->getName()] = (string) $attr;
        }
        if ($xml->children()->count() === 0) {
            $r['@value'] = (string) $xml;
        }
        foreach ($xml->children() as $child) {
            /** @var \SimpleXMLElement $child */
            foreach ($child->attributes() as $attr) {
                $val = [];
                $val['@attributes'][$attr->getName()] = (string) $attr;
            }
            if ($child->children()->count() === 0) {
                $val['@value'] = (string) $child;
            } else {
                $val = $this->serializeXml($child);
            }
            if (isset($r[$child->getName()])) {
                if (array_keys($r[$child->getName()])[0] !== 0) {
                    $r[$child->getName()] = [$r[$child->getName()]];
                }
                $r[$child->getName()][] = $val;
            } else {
                $r[$child->getName()] = $val;
            }
        }

        return $r;
    }
}
