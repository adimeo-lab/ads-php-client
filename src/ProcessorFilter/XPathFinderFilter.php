<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class XPathFinderFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Xpath finder Parser";
    }

    public function getFields()
    {
        return ['output'];
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
            'xml_xpath' => 'XML xpath document',
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        try {
            $xpath = $this->getArgumentValue('xml_xpath', $document);
            $settings = $this->getSettings();
            $query = $settings['xpath'];
            $queries = array_map('trim', explode(',', $query));
            if ($xpath != null) {
                $r = [];
                foreach ($queries as $query) {
                    /** @var \DOMXPath $xpath */
                    for ($i = 0; $i < $xpath->query($query)->length; $i++) {

                        $r[] = $xpath->query($query)->item($i)->textContent;
                    }
                }
                unset($xpath);
                unset($settings);
                unset($query);
                unset($queries);

                gc_enable();
                gc_collect_cycles();
                return ['output' => $r];
            }
        } catch (\Exception $ex) {
            $datasource->getOutputManager()->writeLn('Exception ==> '.$ex->getMessage());
        }
        return ['output' => []];
    }
}
