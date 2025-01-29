<?php

return [
	
	App\Providers\AliasServiceProvider::class,
	App\Providers\AppServiceProvider::class,
	App\Providers\AuthServiceProvider::class,
	// App\Providers\BroadcastServiceProvider::class,
	App\Providers\PluginServiceProvider::class,
	App\Providers\MacroServiceProvider::class,
	App\Providers\ExtensionServiceProvider::class,
	
	Larapen\TextToImage\TextToImageServiceProvider::class,
	Larapen\LaravelMetaTags\MetaTagsServiceProvider::class,
	Larapen\Honeypot\HoneypotServiceProvider::class,
	Larapen\Captcha\CaptchaServiceProvider::class,
	Larapen\ReCaptcha\ReCaptchaServiceProvider::class,
	Larapen\LaravelDistance\DistanceServiceProvider::class,
	Larapen\Feed\FeedServiceProvider::class,
	Larapen\Impersonate\ImpersonateServiceProvider::class,

];
