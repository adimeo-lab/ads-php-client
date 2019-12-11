<?php

namespace Adimeo\DataSuite\Model;

class SearchPage extends PersistentObject
{
    private $id;

    private $name;

    private $mapping;

    private $definition;

    public function __construct($name, $mapping, $definition, $id = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->mapping = $mapping;
        $this->definition = $definition;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param mixed $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return mixed
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param mixed $definition
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }

    public function getType()
    {
        return 'search_page';
    }

    public function getTags()
    {
        if (strpos($this->getMapping(), '.') === 0) {
            $indexName = '.'.explode('.', $this->getMapping())[1];
        } else {
            $indexName = explode('.', $this->getMapping())[0];
        }
        return [
            'index_name='.$indexName
        ];
    }
}
