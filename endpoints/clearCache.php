<?php
require __DIR__."/../core/bootstrap.php";
$manager = new Api();
$db = new Database(PATH_DB);
$cache = new Cache(PATH_CACHE,$db);
$cache->clear();

$manager->success(true);
