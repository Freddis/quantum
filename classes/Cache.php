<?php

/**
 * Класс кеша для базы данных. Организует общение с базой данных, получение и обновление данных в ней.
 */
class Cache
{
    /**
     * Дерево кеша
     */
    protected CacheNode $tree;

    /**
     * Подключение к безе данных
     */
    protected Database $database;

    /**
     * Путь к базе данных
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
        if (!$cacheWritable && !$dirWritable) {
            throw new Exception("Файл кеша недоступен для записи");
        }
        $this->path = $path;
        $this->database = $database;
        if (!file_exists($path)) {
            $this->tree = new CacheNode(uniqid(), "root");
            return;
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);
        $this->tree = CacheNode::fromJson($data);
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
        $node = $this->tree->searchNode($id);
        if (!$node) {
            return false;
        }
        $node->setValue($name);
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
        $node = $this->tree->searchNode($id);
        if (!$node) {
            return false;
        }
        $node->delete();
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
        $updated = $this->database->update($this->tree->getChildren());
        if ($updated) {
//            $this->clear();
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
    public function createNode(string $parentId, string $name)
    {
        $node = $this->tree->searchNode($parentId);
        if (!$node) {
            return false;
        }
        $id = $this->generateNodeId();
        $child = new CacheNode($id, $name);
        $node->addChild($child);
        $saved = $this->saveCache();
        return $saved;
    }

    /**
     * Получение всех деревьев нод
     *
     * @return Mixed[]
     */
    public function getNodes() : array
    {
        return $this->tree->childrenToArray();
    }

    /**
     * Получение ноды в кеш из базы данных
     *
     * @param string $id Идентификатор ноды
     * @return bool
     */
    public function getNode(string $id)
    {
        $existing = $this->getCachedNode($id);
        if ($existing) {
            return true;
        }

        $node = $this->database->getNode($id);

        if (!$node) {
            return false;
        }
        $node = CacheNode::fromJson($node);
        $cachedNodes = $this->tree->getChildren();
        foreach ($cachedNodes as $child) {
            if ($child->getIntendedParentId() === $node->getId()) {
                $node->addChild($child);
            }
        }

        $parent = $node->getIntendedParentId() ? $this->tree->searchNode($node->getIntendedParentId()) : null;
        if ($parent) {
            $parent->addChild($node);
        } else {
            $this->tree->addChild($node);
        }

        return $this->saveCache();
    }

    /**
     * @param string $id Идентификатор ноды
     * @return CacheNode|null
     */
    public function getCachedNode(string $id) : ?CacheNode
    {
        return $this->tree->searchNode($id);
    }

    /**
     * Очистка кеша
     */
    public function clear()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    /**
     * Сохранение кеша
     *
     * @return bool
     */
    protected function saveCache()
    {
        $arr = $this->tree->toArray();
        $data = json_encode($arr, JSON_PRETTY_PRINT);
        $write = file_put_contents($this->path, $data);
        $result = $write !== false;
        return $result;
    }

    /**
     * Генерация нового идентификатора
     *
     * @return string
     */
    private function generateNodeId() : string
    {
        return uniqid();
    }

}
