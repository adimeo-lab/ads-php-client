<?php

namespace Adimeo\DataSuite\Model;

class Parameter extends PersistentObject
{
    /** @var  string */
    private $name;

    /** @var  string */
    private $value;

    /**
     * Parameter constructor.
     *
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getId()
    {
        return $this->name;
    }

    public function setId($id)
    {
        $this->name = $id;
    }

    public function getType()
    {
        return 'parameter';
    }
}
