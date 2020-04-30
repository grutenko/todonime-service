<?php

use App\Action\Video\GetVideoByIdAction;

/** @var mixed $group */
$group->get('/video/{id}', GetVideoByIdAction::class);