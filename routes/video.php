<?php

use App\Action\Video\AddComment;
use App\Action\Video\DeleteComment;
use App\Action\Video\GetComments;
use App\Action\Video\GetVideoByIdAction;
use App\Action\Video\SuggestVideo;
use App\Action\Video\UpdateComment;

$group->get('/video/suggest', SuggestVideo::class);

$group->get('/video/comments', GetComments::class);
$group->post('/video/comments', AddComment::class);
$group->delete('/video/comments/{commentId}', DeleteComment::class);
$group->post('/video/comments/{commentId}', UpdateComment::class);

$group->get('/video/{id}', GetVideoByIdAction::class);