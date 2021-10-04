<?php
require __DIR__."/../core/bootstrap.php";

$manager = new Api();

$params = new JsonParamManager();
$id = $params->get("id",null);
if(!$id)
{
    $manager->error("No id has been passed");
}

$name = $params->get("name",null);
if(!$name)
{
    $manager->error("No name has been passed");
}

$db = new Database(PATH_DB);
$cache = new Cache(PATH_CACHE,$db);
if(!$cache->getCachedNode($id))
{
    $manager->error("Node not found");
}
$saved = $cache->renameNode($id,$name);
if(!$saved)
{
    $manager->error("Couldn't rename node");
}

$manager->success(true);
