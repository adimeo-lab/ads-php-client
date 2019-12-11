<?php

namespace Adimeo\DataSuite\Model;

abstract class ProcessorFilter
{
    private $settings;

    private $inStackName;

    private $autoImplode;

    private $autoImplodeSeparator;

    private $autoStriptags;

    private $isHTML;

    public function __construct($data = [])
    {
        $this->inStackName = '';
        $this->autoImplode = false;
        $this->autoImplodeSeparator = '';
        $this->autoStriptags = false;
        $this->isHTML = false;
        $this->setData($data);
    }

    /**
     * @return string
     */
    abstract function getDisplayName();

    public function getArgumentsAndSettings()
    {
        $r = [];
        foreach ($this->getSettings() as $k => $setting) {
            $r['setting_'.$k] = $setting;
        }
        foreach ($this->argumentsData as $arg) {
            $r['arg_'.$arg['key']] = $arg['value'];
        }
        $r['in_stack_name'] = $this->inStackName;
        $r['autoImplode'] = $this->autoImplode;
        $r['autoImplodeSeparator'] = $this->autoImplodeSeparator;
        $r['autoStriptags'] = $this->autoStriptags;
        $r['isHTML'] = $this->isHTML;
        return $r;
    }

    private $argumentsData;

    public function getArgumentsData()
    {
        return $this->argumentsData;
    }

    abstract function getSettingFields();

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param string[] $settings
     */
    public function setData($data)
    {
        $settings = [];
        $arguments = [];
        foreach ($data as $k => $v) {
            if (strpos($k, 'setting_') === 0) {
                $settings[substr($k, strlen('setting_'))] = $v;
            }
            if (strpos($k, 'arg_') === 0) {
                $arguments[] = ['key' => substr($k, strlen('arg_')), 'value' => $v];
            }
            if ($k == 'in_stack_name')
                $this->inStackName = $v;
            if ($k == 'autoImplode')
                $this->autoImplode = $v;
            if ($k == 'autoImplodeSeparator')
                $this->autoImplodeSeparator = $v;
            if ($k == 'autoStriptags')
                $this->autoStriptags = $v;
            if ($k == 'isHTML')
                $this->isHTML = $v;
        }
        $this->argumentsData = $arguments;
        $this->settings = $settings;
    }

    /**
     * @return string[]
     */
    abstract function getFields();

    /**
     * Must return an associative array arg_key => arg_label
     */
    abstract function getArguments();

    abstract function execute(&$document, DataSource $datasource);

    protected function getArgumentValue($argName, $document)
    {
        foreach ($this->getArgumentsData() as $arg) {
            if ($arg['key'] == $argName && isset($document[$arg['value']]))
                return $document[$arg['value']];
        }
        return '';
    }

    public function getInStackName()
    {
        return $this->inStackName;
    }

    public function setInStackName($inStackName)
    {
        $this->inStackName = $inStackName;
    }

    public function getAutoImplode()
    {
        return $this->autoImplode;
    }

    public function getAutoImplodeSeparator()
    {
        return $this->autoImplodeSeparator;
    }

    public function getAutoStriptags()
    {
        return $this->autoStriptags;
    }

    public function setAutoImplode($autoImplode)
    {
        $this->autoImplode = $autoImplode;
    }

    public function setAutoImplodeSeparator($autoImplodeSeparator)
    {
        $this->autoImplodeSeparator = $autoImplodeSeparator;
    }

    public function setAutoStriptags($autoStriptags)
    {
        $this->autoStriptags = $autoStriptags;
    }

    public function getIsHTML()
    {
        return $this->isHTML;
    }

    public function setIsHTML($isHTML)
    {
        $this->isHTML = $isHTML;
    }
}
