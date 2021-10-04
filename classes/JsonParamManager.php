<?php

/**
 * Осуществляет доступ к параметрам переданным при помощи JSON XHR
 */
class JsonParamManager
{
    /**
     * Список параметров
     * @var String[]
     */
    protected $params = [];


    public function __construct()
    {
        try {
            $data = file_get_contents('php://input');
            $this->params = json_decode($data,true);
        }
        catch (Throwable $exception)
        {

        }
    }

    /**
     * Получение параметра
     *
     * @param string $key Имя параметра
     * @param Mixed $defaultValue Значение по умолчанию
     * @return String Значение
     */
    public function get(string $key, $defaultValue = null)
    {
        return $this->params[$key] ?? $defaultValue;
    }
}
