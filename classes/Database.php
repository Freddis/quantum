<?php

/**
 * Класс базы данных
 */
class Database
{
    /**
     * Путь к файлу с данными
     */
    protected $path;

    /**
     * Дерево данных
     * @var Node
     */
    protected $tree;

    /**
     * Database constructor.
     *
     * @param string $path Путь к файлу базы данных
     * @throws Exception
     */
    public function __construct(string $path)
    {
        if (!is_writable($path)) {
            throw new Exception("Файл бд недоступен для записи");
        }
        $this->path = $path;
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        $this->tree = Node::fromJson($data);
    }

    /**
     * Получение всего дерева данных
     *
     * @return mixed[]
     */
    public function getTree()
    {
        $arr = $this->tree->toArray();
        return $arr;
    }

    /**
     * Получение ноды дерева
     *
     * @param string $id Идентификатор ноды
     * @return array|null Нода
     */
    public function getNode($id)
    {
        $node = $this->tree->searchNode($id);
        if ($node) {
            $arr = ["id" => $node->getId(), "value" => $node->getValue()];
            $parent = $node->getParent();
            $arr['parentId'] = $parent ? $parent->getId() : null;
            return $arr;
        }
        return null;
    }

    /**
     * Обновляет данные в базе данных
     *
     * @param Node[] $nodes Массив нод, которые необходимо добавить или обновить
     * @return bool
     */
    public function update(array $nodes)
    {

        $db = $this->tree;
        $traverseInterrupted = false;
        foreach ($nodes as $node) {

            $traverseInterrupted |= $node->traverse(function (Node $child) use ($db) {
                $existingNode = $db->searchNode($child->getId());
                //update
                if ($existingNode) {
                    $existingNode->setValue($child->getValue());
                    if ($child->isDeleted()) {
                        $existingNode->delete();
                    }

                    return false;
                }
                //create
                $node = new Node($child->getId(), $child->getValue(), $child->isDeleted());
                $parent = $child->getParent();
                $existingParent = $db->searchNode($parent->getId());
                if ($parent->getId() === $existingParent->getId()) {
                    $existingParent->addChild($node);

                    return false;
                } else {
                    return true;
                }

            });

        }

        //обратная связь
        $traverseInterrupted = false;
        foreach ($nodes as $node) {
            $traverseInterrupted |= $node->traverse(function (Node $child) use ($db) {
                $existing = $db->searchNode($child->getId());
                if ($existing->isDeleted()) {
                    $child->delete();
                }
            });
        }

        if ($traverseInterrupted) {
            return false;
        }

        return $this->save();
    }

    /**
     * Сохранение базы данных в файл
     * @return bool
     */
    private function save()
    {
        $json = $this->tree->toArray();
        $data = json_encode($json, JSON_PRETTY_PRINT);
        $write = file_put_contents($this->path, $data);
        $result = $write !== false;
        return $result;
    }
}
