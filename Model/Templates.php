<?php

namespace webworks\CoreBundle\Model;

class Templates implements TemplatesInterface
{
    private $index;
    private $create;
    private $edit;
    private $delete;
    private $prefix;

    /**
     * Routes constructor.
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }


    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->prefix . $this->index;
    }

    /**
     * @param $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreate()
    {
        return $this->prefix . $this->create;
    }

    /**
     * @param mixed $create
     * @return $this
     */
    public function setCreate($create)
    {
        $this->create = $create;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEdit()
    {
        return $this->prefix . $this->edit;
    }

    /**
     * @param mixed $edit
     * @return $this
     */
    public function setEdit($edit)
    {
        $this->edit = $edit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDelete()
    {
        return $this->prefix . $this->delete;
    }

    /**
     * @param mixed $delete
     * @return $this
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;

        return $this;
    }

    /**
     * @return array
     */
    public function getRouteKeys()
    {
        $data = [];
        foreach ($this as $keyName => $value) {
            $data[] = $keyName;
        }
        return $data;
    }
}