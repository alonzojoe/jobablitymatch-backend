<?php

require __DIR__ . '/../vendor/autoload.php';

// Fix the request URI so Laravel sees the correct route
$_SERVER['REQUEST_URI'] = '/' . ltrim($_SERVER['PATH_INFO'] ?? '', '/');

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
