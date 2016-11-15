<?php

use Cronboy\Cronboy\Services\RequestRunner;
use Illuminate\Http\Request;

Route::any('cronboy/task/handle', function (Request $request, RequestRunner $requestRunner) {
    // Process a Job from Cronboy SaaS
    $requestRunner->run($request);

    return response()
        ->json([
            'status' => 'processed',
        ]);
})->middleware(\Cronboy\Cronboy\Http\Middleware\VerifySignature::class);
