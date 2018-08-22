<?php

namespace webworks\CoreBundle\Model;

interface RoutesInterface
{
    /**
     * @return string
     */
    public function getIndex();
    /**
     * @param string $index
     * @return $this
     */
    public function setIndex($index);
    /**
     * @return string
     */
    public function getCreate();
    /**
     * @param string $create
     * @return $this
     */
    public function setCreate($create);
    /**
     * @return string
     */
    public function getEdit();
    /**
     * @param string $edit
     * @return $this
     */
    public function setEdit($edit);
    /**
     * @return string
     */
    public function getDelete();
    /**
     * @param string $delete
     * @return $this
     */
    public function setDelete($delete);
    /**
     * @return array
     */
    public function getRouteKeys();
}