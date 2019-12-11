<?php

namespace Adimeo\DataSuite\Model;

class MatchingList extends PersistentObject
{
    private $id;

    private $name;

    private $list;

    public function __construct($name, $list = '{}', $id = null)
    {
        $this->name = $name;
        $this->list = $list;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType()
    {
        return 'matching_list';
    }

    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param mixed $list
     */
    public function setList($list)
    {
        $this->list = $list;
    }
}
