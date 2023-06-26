<?php


return [
    'params' => [
        'uploadPath' => __ROOT_DIR__ . '/static/_upload',
        'exportPath' => __ROOT_DIR__ . '/static/_export',
        'logDir'     => __ROOT_DIR__ . '/log',
    ],
    'routes' => [
        'common_route_test'    => '/common/test',
        'duplicate_route_test' => '/common/test',
    ],
];