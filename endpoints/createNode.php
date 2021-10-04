<?php
require __DIR__ . "/../core/bootstrap.php";

$manager = new Api();

$params = new JsonParamManager();
$id = $params->get("parentId", null);
if (!$id) {
    $manager->error("No parent id has been passed");
}

$name = $params->get("name", null);
if (!$name) {
    $manager->error("No name has been passed");
}

$db = new Database(PATH_DB);
$cache = new Cache(PATH_CACHE, $db);
if (!$cache->getCachedNode($id)) {
    $manager->error("Parent node not found");
}
$saved = $cache->createNode($id, $name);
if (!$saved) {
    $manager->error("Couldn't create node");
}

$manager->success(true);
