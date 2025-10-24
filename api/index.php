<?php

require __DIR__ . '/../vendor/autoload.php';

// Fix server variables for Laravel routing
$_SERVER['SCRIPT_NAME'] = '/api/index.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['PHP_SELF'] = '/api/index.php';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';




$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
