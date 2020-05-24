<?php

use App\Action\Video\GetComments;
use App\Action\Video\GetVideoByIdAction;
use App\Action\Video\SuggestVideo;

/** @var mixed $group */

$group->get('/video/suggest', SuggestVideo::class);
$group->get('/video/comments', GetComments::class);
$group->post('/video/comments', \App\Action\Video\AddComment::class);
$group->delete('/video/comments/{commentId}', \App\Action\Video\DeleteComment::class);

$group->get('/video/{id}', GetVideoByIdAction::class);