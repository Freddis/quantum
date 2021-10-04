<?php
require_once __DIR__ . "/core/bootstrap.php";

if (isset($_POST["wipe"])) {
    unlink(PATH_CACHE);
    unlink(PATH_DB);
    copy(PATH_ORIGINAL_DB, PATH_DB);
    header("Location: /");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Тестовое задача Quantum Soft</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div id="app"></div>
<script src="https://unpkg.com/react@17/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<script type="text/babel" src="/assets/js/treeview.jsx"></script>
<script type="text/babel" src="/assets/js/app.jsx"></script>
<script type="text/babel" src="/assets/js/api.jsx"></script>
<script type="text/babel" src="/assets/js/bootstrap.jsx"></script>
</body>
</html>
