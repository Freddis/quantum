<?php

/**
 * Класс кеша для базы данных. Организует общение с базой данных, получение и обновление данных в ней.
 */
class Cache
{
    /**
     * @var Mixed[] Массив деревьев кеша
     */
    protected $tree = [];

    /**
     * @var Database Подключение к безе данных
     */
    protected $database;

    /**
     * @var string Путь к базе данных
     */
    protected $path;

    /**
     * @param string $path Пусть к базе данных кеша
     * @param Database $database Подключение к базе данных
     * @throws Exception Файл базы данных должен быть доступен
     */
    public function __construct(string $path, Database $database)
    {
        $cacheWritable = file_exists($path) && is_writable($path);
        $dirWritable = !file_exists($path) && is_writable(dirname($path));
        if(!$cacheWritable && !$dirWritable)
        {
            throw new Exception("Файл кеша недоступен для записи");
        }
        $this->path = $path;
        $this->database = $database;
        if (!file_exists($path)) {
            return;
        }
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        $this->tree = &$data;
    }

    /**
     * Переименование ноды
     *
     * @param string $id Идентификатор ноды
     * @param string $name Новое имя
     * @return bool
     */
    public function renameNode(string $id, string $name)
    {
        $node = &$this->searchCachedNode($id, $this->tree);
        if (!$node) {
            return false;
        }
        $node["value"] = $name;
        $saved = $this->saveCache();
        return $saved;
    }

    /**
     * Удаление ноды кеша
     *
     * @param string $id Идентификатор ноды
     * @return bool
     */
    public function deleteNode(string $id)
    {
        $node = &$this->searchCachedNode($id, $this->tree);
        if (!$node) {
            return false;
        }
        $this->deleteRecursively($node);
        $saved = $this->saveCache();
        return $saved;
    }

    /**
     * Сохранение данных из кеша в базу данных
     *
     * @return bool
     */
    public function save()
    {
        $updated = $this->database->update($this->tree);
        if ($updated) {
            $this->clear();
            return true;
        }
        return false;
    }

    /**
     * Создание новой ноды
     *
     * @param String $parentId Идентификтор родителя
     * @param String $name Имя ноды
     * @return bool
     */
    public function createNode(String $parentId, String $name)
    {
        $node = &$this->searchCachedNode($parentId, $this->tree);
        if (!$node) {
            return false;
        }
        $row = ["id" => "_" . uniqid(), "parentId" => $parentId, "value" => $name, "children" => []];
        $node["children"][] = $row;
        $saved = $this->saveCache();
        return $saved;
    }

    /**
     * Получение всех деревьев нод
     *
     * @return Mixed[]
     */
    public function getNodes()
    {
        return $this->tree;
    }

    /**
     * Получение ноды в кеш из базы данных
     *
     * @param int $id Идентификатор ноды
     * @return bool
     */
    public function getNode(int $id)
    {
        $existing = $this->getCachedNode($id);
        if ($existing) {
            return true;
        }

        $node = $this->database->getNode($id);
        if (!$node) {
            return false;
        }
        $children = $this->searchCachedChildNodes($id);
        if ($children) {
            $this->moveChildren($node, $children);
        }

        $this->tree[] = $node;

        if($node["parentId"])
        {
            $parent = &$this->searchCachedNode($node["parentId"],$this->tree);
            if ($parent) {
                $this->moveChildren($parent,[$node]);
            }
        }

        return $this->saveCache();
    }

    /**
     * @param string $id Идентификатор ноды
     * @return mixed|null
     */
    public function getCachedNode(string $id)
    {
        $result =  $this->searchCachedNode($id, $this->tree);
        return $result;
    }

    /**
     * Поиск кешированной ноды
     *
     * @param string $id Идентификатор ноды
     * @param Mixed[] $nodes Ноды в которых осуществляется поиск
     * @return mixed|null Нода
     */
    protected function &searchCachedNode(string $id, &$nodes)
    {
        foreach ($nodes as &$node) {
            if ($node["id"] == $id) {
                return $node;
            }
            $result = &$this->searchCachedNode($id, $node["children"]);
            if ($result) {
                return $result;
            }
        }
        $result = null;
        return $result;
    }

    /**
     * Поиск кешированных нод-детей без родителей
     *
     * @param string $id идентификатор ноды
     * @return Mixed[] Найденные ноды-дети
     */
    protected function searchCachedChildNodes(string $id)
    {
        $result = [];
        foreach ($this->tree as $node) {
            if ($node["parentId"] == $id) {
                $result[] = $node;
            }
        }
        return $result;
    }

    /**
     * Очистка кеша
     *
     * @return bool
     */
    public function clear()
    {
        if(file_exists($this->path))
        {
            unlink($this->path);
        }
        return true;
    }

    /**
     * Сохранение кеша
     *
     * @return bool
     */
    protected function saveCache()
    {
        $data = json_encode($this->tree, JSON_PRETTY_PRINT);
        $write = file_put_contents($this->path, $data);
        $result = $write !== false;
        return $result;
    }

    /**
     * Перемешещение детей в родителя.
     *
     * @param Mixed $parent Ссылка на родителя
     * @param Mixed[] $children Ноды-дети
     */
    private function moveChildren(array &$parent, array $children)
    {

        if(isset($parent["deleted"]))
        {
            foreach ($children as &$child)
            {
                $this->deleteRecursively($child);
            }
        }

        foreach($children as $child)
        {
            $parent["children"][] = $child;
        }
        $childrenIds = [];
        array_map(function ($el) use (&$childrenIds) {
            $childrenIds[] = $el["id"];
        }, $children);

        //Предполагается, что безхозные дети могут быть только в корне
        foreach ($this->tree as $key => $node) {
            if (in_array($node["id"], $childrenIds)) {
                unset($this->tree[$key]);
            }
        }
        $this->tree = array_values($this->tree);
    }

    /**
     * Удаление ноды
     * @param Mixed $ref Нода, которую необходимо удалить
     */
    private function deleteRecursively(&$ref)
    {
        $ref["deleted"] = true;
        foreach ($ref["children"] as &$child) {
            $this->deleteRecursively($child);
        }
    }

}
