<?php

namespace Adimeo\DataSuite\Model;

interface OutputManager
{
    /**
     * @param string $text
     */
    public function writeLn($text);

    /**
     * @param array $array
     */
    public function dumpArray($array);
}
