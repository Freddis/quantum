<?php
require __DIR__."/../core/bootstrap.php";

$manager = new Api();
$db = new Database(PATH_DB);
$cache = new Cache(PATH_CACHE,$db);

$saved = $cache->save();
if(!$saved)
{
    $manager->error("Couldn't save data");
}

$manager->success(true);
