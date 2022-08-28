<?php

return [
    'session' => env('COUCHDB_SESSION', 300),
    'username' => env('COUCHDB_USER'),
    'password' => env('COUCHDB_PASSWORD'),
    'database' => env('COUCHDB_DATABASE', 'laravel'),
    'url' => env('COUCHDB_URL', 'http://couchdb:5984'),
];