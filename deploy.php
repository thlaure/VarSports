<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'https://github.com/thlaure/VarSports.git');

add('shared_files', ['.env.local']);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('ssh-varsports.alwaysdata.net')
    ->set('remote_user', 'varsports_deployer')
    ->set('deploy_path', '~/www');

// Hooks

after('deploy:failed', 'deploy:unlock');
