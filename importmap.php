<?php

return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'url' => 'https://cdn.jsdelivr.net/npm/@hotwired/stimulus@3.2.2/dist/stimulus.js',
    ],
];
