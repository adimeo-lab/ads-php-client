<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class XPathGetterFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "XPath Getter (SimpleXml)";
    }

    public function getFields()
    {
        return ['value'];
    }

    public function getSettingFields()
    {
        return [
            'xpath' => [
                'label' => 'Xpath',
                'type' => 'string',
                'required' => true
            ]
        ];
    }

    public function getArguments()
    {
        return [
            'xml' => 'SimpleXml element',
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        try {
            $settings = $this->getSettings();
            $xml = $this->getArgumentValue('xml', $document);
            /* @var $xml \SimpleXMLElement */
            if (get_class($xml) == 'SimpleXMLElement') {
                $r = $xml->xpath($settings['xpath']);
            } else {
                $r = [];
            }

            if (count($r) == 1 && strlen(trim($this->xmlToString($r[0]))) > 0) {
                return ['value' => trim($this->xmlToString($r[0]))];
            } elseif (count($r) > 1) {
                $vals = [];
                foreach ($r as $val) {
                    if (strlen(trim($this->xmlToString($val))) > 0 && !in_array(trim($this->xmlToString($val)), $vals)) {
                        $vals[] = trim($this->xmlToString($val));
                    }
                }
                return ['value' => $vals];
            } else {
                return ['value' => null];
            }
        } catch (\Exception $ex) {
            return ['value' => null];
        }
    }

    private function xmlToString(\SimpleXMLElement $elem)
    {
        $string = $elem->asXML();
        $openTag = '<'.$elem->getName().'>';
        $closeTag = '</'.$elem->getName().'>';
        if (strpos($string, $openTag) !== FALSE) {
            $string = substr($string, strlen($openTag), strlen($string) - strlen($closeTag) - strlen($openTag));
            if (!$string) $string = "";
            return trim($string);
        } else {
            return (string) $elem;
        }
    }
}
