<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class HTMLTextExtractorFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "HTML text extractor";
    }

    public function getFields()
    {
        return ['output'];
    }

    public function getSettingFields()
    {
        return [];
    }

    public function getArguments()
    {
        return ['html_source' => 'HTML source'];
    }

    public function execute(&$document, DataSource $datasource)
    {
        $html = $this->getArgumentValue('html_source', $document);
        try {
            $tidy = tidy_parse_string($html, [], 'utf8');
            $body = tidy_get_body($tidy);
            $html = $body->value;
        } catch (\Exception $ex) {

        }
        $html = html_entity_decode($html, ENT_COMPAT | ENT_HTML401, 'utf-8');
        $output = html_entity_decode(trim(str_replace('&nbsp;', ' ', htmlentities(preg_replace('!\s+!', ' ', trim(preg_replace('#<[^>]+>#', ' ', $html))), null, 'utf-8'))));
        return ['output' => $output];
    }
}
