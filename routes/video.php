<?php

use App\Action\Video\GetVideoByIdAction;
use App\Action\Video\SuggestVideo;

/** @var mixed $group */

$group->get('/video/suggest', SuggestVideo::class);
$group->get('/video/{id}', GetVideoByIdAction::class);