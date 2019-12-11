<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class SimpleXMLParserFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Simple XML Parser";
    }

    public function getFields()
    {
        return ['doc'];
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
            $xmlDoc = simplexml_load_string(str_replace('xmlns=', 'ns=', $xml));
            if ($xmlDoc) {
                foreach ($xmlDoc->getDocNamespaces() as $strPrefix => $strNamespace) {
                    $xmlDoc->registerXPathNamespace($strPrefix, $strNamespace);
                }

                return ['doc' => $xmlDoc];
            }
        } catch (\Exception $ex) {

        }
        return ['doc' => null];
    }
}
