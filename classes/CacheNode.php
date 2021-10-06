<?php

/**
 * Ноды хранимые в кеше
 */
class CacheNode extends Node
{

    protected $intendedParentId = null;

    /**
     * @param string $parentId
     */
    public function setIntendedParentId(?string $parentId)
    {
        $this->intendedParentId = $parentId;
    }

    /**
     * @return mixed
     */
    public function getIntendedParentId() : ?string
    {
        return $this->intendedParentId;
    }

    /**
     * @param $json
     * @return CacheNode
     */
    public static function fromJson(&$json)
    {
        $ret = parent::fromJson($json);
        $ret->setIntendedParentId($json["parentId"]);
        return $ret;
    }

    /**
     * Приведение к массиву
     *
     * @return array
     */
    public function & toArray(): array
    {
        $ret = parent::toArray();
        $ret["parentId"] = $this->getIntendedParentId();
        return $ret;
    }


}
