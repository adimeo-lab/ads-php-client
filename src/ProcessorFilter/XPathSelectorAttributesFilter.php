<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;
use Symfony\Component\CssSelector\CssSelectorConverter;

class XPathSelectorAttributesFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "CSS selector to XML attributes";
    }

    public function getFields()
    {
        return ['value'];
    }

    public function getSettingFields()
    {
        return [
            'selector' => [
                'label' => 'CSS selector',
                'type' => 'string',
                'required' => true
            ],
            'notidy' => [
                'label' => 'No Tidy',
                'type' => 'boolean',
                'required' => false
            ]
        ];
    }

    public function getArguments()
    {
        return [
            'html' => 'HTML',
        ];
    }

    /**
     * @var \DOMXPath
     */
    private $xpath;

    public function execute(&$document, DataSource $datasource)
    {
        $html = $this->getArgumentValue('html', $document);
        $settings = $this->getSettings();
        $selector = isset($settings['selector']) ? $settings['selector'] : '';
        if ($selector == '') {
            return ['xml_parts' => [$html]];
        } else {
            $options = [
                'hide-comments' => true,
                'tidy-mark' => false,
                'indent' => true,
                'indent-spaces' => 4,
                'new-blocklevel-tags' => 'article,header,footer,section,nav,figure',
                'new-inline-tags' => 'video,audio,canvas,ruby,rt,rp,time',
                'vertical-space' => false,
                'output-xhtml' => true,
                'wrap' => 0,
                'wrap-attributes' => false,
                'break-before-br' => false,
            ];
            $dom = new \DOMDocument();
            try {
                $cleanHtml = !isset($settings['notidy']) || !$settings['notidy'] ? tidy_repair_string($html, $options, 'utf8') : $html;
                $dom->loadHTML(mb_convert_encoding($cleanHtml, 'HTML-ENTITIES', 'UTF-8'));
            } catch (\Exception $ex) {
            }
            $this->xpath = new \DOMXPath($dom);
            $elements = $this->select($selector, false);
            $attributes = [];
            foreach ($elements as $elem) {
                foreach ($elem->attributes as $name => $attrNode)
                    $attributes[$name] = $attrNode->textContent;
            }
            return ['attributes' => $attributes];
        }
    }

    public function select($selector, $as_array = true)
    {
        $cssSelector = new CssSelectorConverter();
        $elements = $this->xpath->evaluate($cssSelector->toXPath($selector));
        return $as_array ? elements_to_array($elements) : $elements;
    }

    public function selectToXML($selector)
    {
        $elements = $this->select($selector, false);
        $xmlParts = [];
        foreach ($elements as $elem) {
            $xmlParts[] = simplexml_import_dom($elem)->asXML();
        }
        return $xmlParts;
    }
}

/**
 * Convert $elements to an array.
 */

function elements_to_array($elements)
{
    $array = [];
    for ($i = 0, $length = $elements->length; $i < $length; ++$i) {
        /** @var \DOMNodeList $elements */
        if ($elements->item($i)->nodeType == XML_ELEMENT_NODE)
            array_push($array, element_to_array($elements->item($i)));
    }
    return $array;
}

/**
 * Convert $element to an array.
 */

function element_to_array($element)
{
    $array = [
        'name' => $element->nodeName,
        'attributes' => [],
        'text' => $element->textContent,
        'children' => elements_to_array($element->childNodes)
    ];
    if ($element->attributes->length)
        foreach ($element->attributes as $key => $attr)
            $array['attributes'][$key] = $attr->value;
    return $array;
}
