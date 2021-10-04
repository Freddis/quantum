<?php
require __DIR__."/../core/bootstrap.php";

$db = new Database(PATH_DB);
$tree = $db->getTree();

$manager = new Api();
$manager->success([$tree]);
