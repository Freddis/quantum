<?php
require __DIR__."/../core/bootstrap.php";


$manager = new Api();

$params = new JsonParamManager();
$id = $params->get("id",null);

if(!$id)
{
    $manager->error("No id has been passed");
}

$db = new Database(PATH_DB);
$cache = new Cache(PATH_CACHE,$db);
if($cache->getCachedNode($id))
{
    $manager->error("This node is already loaded");
}
$data = $cache->getNode($id);
$manager->success($data);
