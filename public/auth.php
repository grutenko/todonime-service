<?php
require __DIR__ . '/../src/header.php';

$app->group('/auth', function($group) use ($container) {
    require __DIR__ . '/../routes/auth.php';
});

$app->run();
