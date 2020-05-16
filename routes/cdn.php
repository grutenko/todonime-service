<?php

$group->get('/[{path:.*}]', \App\Action\Cdn\CdnGetFile::class);