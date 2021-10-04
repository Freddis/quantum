<?php

spl_autoload_register(function ($className) {
    include PATH_ROOT."/classes/".$className . '.php';
});
