<?php
require_once __DIR__ . "/../core/bootstrap.php";

echo "Generating Database \n";

$params = new ConsoleParamManager($argv);
$numOfLevels = intval($params->getParamAt(1)) ?? intval(readline("Enter the number of levels: "));
if (!is_integer($numOfLevels) || $numOfLevels > 1000000 || $numOfLevels < 1) {
    die("Number of levels should be from 1 to 1000000 \n");
}

$maxNumOfSiblings = intval($params->getParamAt(2)) ?? intval(readline("Enter max number of siblings: "));
if (!is_integer($maxNumOfSiblings) || $maxNumOfSiblings > 1000000 || $maxNumOfSiblings < 1) {
    die("Max number of siblings should be from 1 to 1000000 \n");
}

$nextId = 1;
$root = ["id" => uniqid(), "value" => "Root","children" => []];

$fill =  function (&$container, $level) use($maxNumOfSiblings,&$nextId, &$fill)
{
    $numOfSiblings = rand(1,$maxNumOfSiblings);
//    print("Level: ".$level."\n");
    $level--;
    for($i=0; $i < $numOfSiblings;$i++)
    {
        ++$nextId;
        $row = ["id" => uniqid(), "value" => "Node".$nextId, "children" => []];
        $container[] = $row;
        $skip = ($i != 0 && (rand(0,100) < 20 )) ? true : false;
        if($level > 0 && !$skip)
        {
            $fill($container[count($container)-1]["children"],$level);
        }
    }
};

$fill($root["children"],$numOfLevels);

print("Done\n");
$json = json_encode($root,JSON_PRETTY_PRINT);
file_put_contents(PATH_ORIGINAL_DB,$json);
file_put_contents(PATH_DB,$json);

print("Number of elements: $nextId \n");
