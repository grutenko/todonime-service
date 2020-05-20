<?php

/** @var mixed $group */
$group->put('/user/episode/watched', \App\Action\User\BumpEpisode::class);

/**
 *
 */
$group->get('/user/current', \App\Action\User\Current::class);

$group->get('/user/logout', \App\Action\User\Logout::class);