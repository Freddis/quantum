<?php

/**
 * Нода древесной структуры
 */
class Node
{
    /**
     * Идентификатор
     */
    protected $id;

    /**
     * Родитель
     */
    protected $parent = null;

    /**
     * Значение
     */
    protected $value;

    /**
     * Удалена ли нода
     */
    protected $deleted;

    /**
     * @var Node[] Ноды
     */
    protected $children = [];

    /**
     * @param string $id Идентификатор
     * @param string $value Значение
     * @param bool $deleted Удалена ли нода
     */
    public function __construct(string $id, string $value, bool $deleted = false)
    {
        $this->id = $id;
        $this->value = $value;
        $this->deleted = $deleted;
    }

    /**
     * Получение идентификатора ноды
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Добавление дочерней ноды
     *
     * @param Node $node
     */
    public function addChild(Node $node)
    {
        $this->children[] = $node;
        $node->setParent($this);
        if($this->deleted)
        {
            $node->delete();
        }
    }

    /**
     * Установка родителя ноды (внутренняя функция)
     * @param Node $parent
     */
    public function setParent(Node $parent)
    {
        if($this->getParent()) {
            $children = &$this->parent->getChildren();
            foreach ($children as $key => $child) {
                if ($child->getId() == $this->getId()) {
                    unset($children[$key]);
                }
            }
        }
        $this->parent = $parent;

    }

    /**
     * Проход по нодам
     *
     * @param callable $callback фунция обратного вызова, если возвращает что-то кроме false, то проход останавливается
     * @return mixed
     */
    public function traverse(Callable $callback)
    {
        $result = $callback($this);
        if ($result) {
            return $result;
        }

        return $this->traverseChildren($callback);
    }

    /**
     * Проход по дочерним нодам
     *
     * @param callable $callback фунция обратного вызова, если возвращает что-то кроме false, то проход останавливается
     * @return mixed
     */
    public function traverseChildren(Callable $callback)
    {
        foreach ($this->children as $child) {
            $result = $child->traverse($callback);
            if ($result) {
                return $result;
            }
        }
        return false;
    }

    /**
     * Получение родительского нода
     *
     * @return Node|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Получение дочерних нодов
     * @return self[]
     */
    public function & getChildren() : array
    {

        return $this->children;
    }

    /**
     * Получение значения нода
     *
     * @return string
     */
    public function getValue() : String
    {
        return $this->value;
    }

    /**
     * Приведение к массиву
     *
     * @return array
     */
    public function & toArray(): array
    {
        $arr = ["id" => $this->getId(), "value" => $this->value];
        if($this->deleted)
        {
            $arr["deleted"] = true;
        }

        $arr["children"] = & $this->childrenToArray();
        return $arr;

    }

    /**
     * Приведение к массиву нод детей
     *
     * @return array
     */
    public function & childrenToArray() : array
    {
        $children = [];
        foreach ($this->children as $child) {
            $children[] = &$child->toArray();
        }
        return $children;
    }

    /**
     * Задаем значение
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * Отмечает нод как удаленный
     */
    public function delete()
    {
        $this->deleted = true;
        $children = $this->getChildren();
        foreach ($children as $child)
        {
            $child->delete();
        }
    }

    /**
     * Поиск нода в дереве
     *
     * @param string $id Идентификатор нода
     * @return Node|null
     */
    public function searchNode(string $id)
    {
        $result = null;
        $this->traverse(function (Node $node) use ($id, &$result) {
            if ($node->getId() == $id) {
                $result = $node;
                return true;
            }
            return false;
        });
        return $result;
    }

    /**
     * Удалена ли нода
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Создание дерева из массива
     *
     * @param array $json
     * @return mixed
     */
    public static function fromJson(array &$json)
    {
        $class = get_called_class();
        $deleted = isset($json["deleted"]);
        $node = new $class($json["id"], $json["value"],$deleted);

        if (isset($json["children"]) && is_array($json["children"])) {
            foreach ($json["children"] as &$child) {
                $childNode = static::fromJson($child);
                $node->addChild($childNode);
            }
        }
        return $node;
    }
}
