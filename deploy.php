<?php
namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'api-vk-questions');

// Project repository
set('repository', 'git@github.com:abashkatov/api-vk-answers.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);


// Hosts

host('prod')
    ->setHostname('api-vk-questions.r2ls.ru')
    ->setRemoteUser('deployer')
    ->set('deploy_path', '/var/www/{{application}}');
    
// Tasks

//task('database:migrate')->disable();

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

