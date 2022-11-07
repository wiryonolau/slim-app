<?php

use Laminas\I18n\Translator\Loader\Gettext;

return [
    "translator" => [
        "locale" => [
            "id_ID",
            "en_US",
        ],
        "translation_file_patterns" => [
            [
                "type" => Gettext::class,
                "base_dir" => __DIR__.'/../lang',
                "pattern" => "%s.mo"
            ]
        ],
        "cache" => [
            'adapter' => 'filesystem',
            'options' => [
                'namespace' => 'translation',
                'ttl' => 1,
            ],
            'plugins' => [
                [
                    'name' => 'serializer',
                ],
                [
                    'name' => 'exception_handler',
                    'options' => [
                        'throw_exceptions' => false,
                     ],
                ],
            ],
        ]
    ]
];
