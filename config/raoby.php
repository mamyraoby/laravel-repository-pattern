<?php

// You may ommit unchanged configuration

return [
    'command' => [
        'repository' => [
            'make' => 'raoby:make:repository',
        ],
    ],
    'repository' => [
        'abstract' => [
            'path'      => 'app/Contracts/Repositories',
            'namespace' => 'App\\Contracts\\Repositories\\',
            'suffix'    => 'RepositoryContract',
        ],
        'concrete' => [
            'path'      => 'app/Repositories',
            'namespace' => 'App\\Repositories\\',
            'suffix'    => 'Repository',
        ],
    ],
    'models' => [
        'namespace' => 'App\\Models\\',
    ]
];
