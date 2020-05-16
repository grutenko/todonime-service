<?php
require __DIR__ . '/../src/header.php';

$app->group('', function($group) use ($container) {
    require __DIR__ . '/../routes/auth.php';
});

$app->run();
