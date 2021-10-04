<?php
require __DIR__."/../core/bootstrap.php";

$db = new Database(PATH_DB);
$cache = new Cache(PATH_CACHE,$db);
$data = $cache->getNodes();

$manager = new Api();
$manager->success($data);
