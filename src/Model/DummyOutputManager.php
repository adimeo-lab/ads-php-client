<?php

namespace Adimeo\DataSuite\Model;

class DummyOutputManager implements OutputManager
{
    public function writeLn($text)
    {
        //nothing to say
    }

    public function dumpArray($array)
    {
        //nothing to say
    }
}
