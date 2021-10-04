<?php

/**
 * Осуществляет общение при помощи JSON протокола между клиентом и сервером
 */
class Api
{

    /**
     * Успешный ответ
     * @param Mixed[] $data
     */
    public function success($data = [])
    {
        $this->respond(1, $data);
    }

    /**
     * Ответ с ошибкой
     * @param String $text
     */
    public function error($text)
    {
        $this->respond(0, [$text]);
    }

    /**
     * Формирует ответ API
     *
     * @param Int $status Статус ответа
     * @param Mixed[] $data Любые примитивные данные в виде вложенных ассоциативных массивов
     */
    private function respond(int $status, $data)
    {
        $fullData = ['status' => $status, "data" => $data];

        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, print_r($fullData, true));
        fclose($stderr);

        $this->showJson($fullData);
    }

    /**
     * Отвечает клиенту в формате JSON.
     *
     * @param Mixed $data Данные для view
     * @param Bool $noConvert
     */
    private function showJson($data, $noConvert = false)
    {
        if (!$noConvert)
            $response = json_encode($data, JSON_PRETTY_PRINT);
        else
            $response = $data;

        header('Content-type: application/json');
        echo $response;
        die();
    }
}
