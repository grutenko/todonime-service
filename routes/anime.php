<?php
/**
 *
 */
$group->get('/anime/{id}', \App\Action\Anime\GetAnimeByOIdAction::class);

$group->post('/anime/{id}/episode/name', \App\Action\Anime\UpdateEpisodeName::class);