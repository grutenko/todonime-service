<?php
namespace Deployer;

require 'recipe/common.php';

set('ssh_multiplexing', false);
set('default_stage', 'prod');

host('todonime.ru')
    ->stage('prod')
    ->user('deploy')
    ->identityFile('~/.ssh/id_rsa-deploy')
    ->set('repository', 'https://github.com/todonime/todonime-service.git')
    ->set('branch', 'master')
    ->set('deploy_path', '/var/www/todonime.ru')
    ->set('shared_files', [
        '.env',
        'client/.env',
        'composer-lock.json',
        'client/package-lock.json'
    ])
    ->set('shared_dirs', [
        'storage',
        'vendor',
        'client/node_modules'
    ]);

task('deploy:install-composer', 'cd {{release_path}} && composer install --no-dev');
task('deploy:install-npm', 'cd {{release_path}}/client && npm i && npm run build');
task('deploy:fpm-restart', 'sudo /etc/init.d/php7.2-fpm restart');
task('deploy:daemon-restart', 'sudo systemctl restart todonime-queue');
task('deploy:event-service-restart', "sudo systemctl restart todonime-ws.service");

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:install-composer',
    'deploy:install-npm',
    'deploy:event-service-restart',
    'deploy:unlock',
    'cleanup',
    'success',
    'deploy:fpm-restart',
    'deploy:daemon-restart'
]);

after('deploy:failed', 'deploy:unlock');



