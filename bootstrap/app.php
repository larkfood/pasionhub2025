<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		using: function () {
			// api
			Route::middleware('api')
				->namespace('App\Http\Controllers\Api')
				->prefix('api')
				->group(base_path('routes/api.php'));
			
			// web
			Route::middleware('web')
				->namespace('App\Http\Controllers\Web')
				->group(base_path('routes/web.php'));
		},
		commands: __DIR__ . '/../routes/console.php',
		// health: '/up',
	)
	->withMiddleware(new \App\Http\Kernel())
	->withSchedule(new \App\Console\Kernel())
	->withExceptions(new \App\Exceptions\Handler())
	->create();
