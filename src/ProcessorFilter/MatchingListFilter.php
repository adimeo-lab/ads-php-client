<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class MatchingListFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Matching list filter";
    }

    public function getSettingFields()
    {
        return [
            'matching_list_id' => [
                'label' => 'Matching list',
                'type' => 'choice',
                'bound_to' => 'matching_list',
                'required' => true
            ],
            'case_insensitive' => [
                'label' => 'Case insensitive input',
                'type' => 'boolean',
                'required' => false
            ],
            'default_value' => [
                'label' => 'Default value',
                'type' => 'string',
                'trim' => false,
                'required' => false
            ]
        ];
    }

    public function getFields()
    {
        return ['output'];
    }

    public function getArguments()
    {
        return ['input' => 'Input'];
    }

    public function execute(&$document, DataSource $datasource)
    {
        $settings = $this->getSettings();
        $input = $this->getArgumentValue('input', $document);
        $output = null;
        if (!empty($input)) {
            if (is_array($input))
                $data = $input;
            else
                $data = [$input];
            $output = [];
            $matchingList = $datasource->getMatchingList($settings['matching_list_id']);
            $list = json_decode($matchingList->getList(), true);
            foreach ($data as $in) {
                $found = false;
                $out = '';
                if (is_string($in) && !empty($in)) {
                    foreach ($list as $k => $v) {
                        if ($settings['case_insensitive']) {
                            if (strtolower($k) == strtolower($in)) {
                                $found = true;
                                $out = $v;
                            }
                        } else {
                            if ($k == $in) {
                                $found = true;
                                $out = $v;
                            }
                        }
                    }
                }
                if ($found) {
                    if (!empty($out) && !in_array($out, $output)) {
                        $output[] = $out;
                    }
                } else {
                    if (!empty($settings['default_value'])) {
                        if (strtolower($settings['default_value']) != 'null' && !in_array($settings['default_value'], $output)) {
                            $output[] = $settings['default_value'];
                        }
                    } else {
                        if (!in_array($in, $output)) {
                            $output[] = $in;
                        }
                    }
                }
            }
            unset($list);
            if (count($output) == 0) {
                $output = null;
            } elseif (count($output) == 1) {
                $output = $output[0];
            }
        }
        return ['output' => $output];
    }
}
