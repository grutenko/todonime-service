<?php
require __DIR__ . '/../src/header.php';

$app->group('', function($group) {
   require __DIR__ . '/../routes/cdn.php';
});

$app->run();
