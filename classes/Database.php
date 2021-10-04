<?php

/**
 * Класс базы данных
 */
class Database
{
    /**
     * @var String Путь к файлу с данными
     */
    protected $path;

    /**
     * @var Mixed[] Дерево данных
     */
    protected $tree;

    /**
     * Database constructor.
     *
     * @param string $path Путь к файлу базы данных
     * @throws Exception
     */
    public function __construct($path)
    {
        if(!is_writable($path))
        {
            throw new Exception("Файл бд недоступен для записи");
        }
        $this->path = $path;
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        $this->tree = &$data;
    }

    /**
     * Получение всего дерева данных
     *
     * @return mixed
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Получение ноды дерева
     *
     * @param string $id Идентификатор ноды
     * @return array|null Нода
     */
    public function getNode($id)
    {
        return $this->searchNode($id, $this->tree);
    }

    /**
     * Обновляет данные в базе данных
     *
     * @param Mixed[] $nodes Массив нод, которые необходимо добавить или обновить
     * @return bool
     */
    public function update(array $nodes)
    {
        $result = true;
        foreach($nodes as $node)
        {
            $result &= $this->updateNode($node);
        }
        if(!$result)
        {
            return false;
        }

        return $this->save();
    }

    /**
     * Поиск ноды
     *
     * @param string $id Идентификатор искомой ноды
     * @param Mixed[] $node Нода в которой осуществляется поиск
     *
     * @return array|null Нода
     */
    private function searchNode(string $id, &$node)
    {
        if ($node["id"] == $id) {
            $row = ["id" => $id, "value" => $node["value"], "parentId" => null, "children" => []];
            return $row;
        }

        foreach ($node["children"] as $child) {
            $res = $this->searchNode($id, $child);
            if ($res) {
                $res["parentId"] = $res["parentId"] ?? $node["id"];
                return $res;
            }
        }
        return null;

    }

    /**
     * Обновление ноды
     *
     * @param Mixed[] $node Нода (массив с данными и идентификатором ноды)
     * @return bool
     */
    private function updateNode($node)
    {

        $children = [&$this->tree];
        $ref = &$this->searchRef($children,$node["id"]);
        if(!$ref)
        {
            return false;
        }
        $ref["value"] = $node["value"];

        if(isset($node["deleted"]))
        {
            $this->deleteRef($ref);
            return true;
        }

        $result = true;
        foreach($node["children"] as $child)
        {
            $isNew = substr($child["id"],0,1) == "_";
            if($isNew)
            {
                $this->createRef($ref,$child);
                continue;
            }
            $result &= $this->updateNode($child);
        }
        return $result;
    }

    /**
     * Поиск ссылки на ноду
     *
     * @param Mixed[] $nodes Массив нод для поиска в нем
     * @param integer $id Идентификатор ноды
     * @return mixed|null
     */
    private function & searchRef(&$nodes,int $id)
    {
        foreach($nodes as &$node)
        {
            if($node["id"] === $id)
            {
                return $node;
            }
            $ref = & $this->searchRef($node["children"],$id);
            if($ref)
            {
                return $ref;
            }
        }
        $ret = null;
        return $ret;
    }

    /**
     * Удаление ноды
     *
     * @param Mixed[] $ref Ссылка на ноду
     */
    private function deleteRef(array &$ref)
    {

        $ref["deleted"] = true;
        foreach ($ref["children"] as &$child)
        {
            $this->deleteRef($child);
        }
    }

    /**
     * Создание ноды
     *
     * @param Mixed[] $parent Ссылка на родителя
     * @param Mixed[] $child Нода
     */
    private function createRef(array &$parent, $child)
    {
        $row = ["id" => $this->createId(),"value" => $child["value"],"children" =>[]];
        if(isset($child["deleted"]))
        {
            $row["deleted"] = true;
        }
        $parent["children"][] = &$row;
        foreach($child["children"] as $child)
        {
            $this->createRef($row,$child);
        }
    }

    /**
     * Создание уникального идентификатора для новой ноды
     *
     * @return int Идентификатор
     */
    private function createId()
    {
        $nodes = [&$this->tree];
        $num = $this->countNodes($nodes) +1;
        return $num;
    }

    /**
     * Подсчет количества нод
     *
     * @param Mixed[] $nodes Массив нод
     * @param int $counter Счетчик для рекурсивного подсчета
     * @return int Число нод
     */
    private function countNodes(array &$nodes,$counter = 0)
    {
        foreach($nodes as $node)
        {
            $counter++;
            $counter += $this->countNodes($node["children"],$counter);
        }
        return $counter;
    }

    /**
     * Сохранение базы данных в файл
     * @return bool
     */
    private function save()
    {
        $data = json_encode($this->tree, JSON_PRETTY_PRINT);
        $write = file_put_contents($this->path, $data);
        $result = $write !== false;
        return $result;
    }
}
