@php
	// Logo
	$logoFactoryUrl = config('larapen.media.logo-factory');
	$logoDarkUrl = config('settings.app.logo_dark_url', $logoFactoryUrl);
	$logoLightUrl = config('settings.app.logo_light_url', $logoFactoryUrl);
	$logoAlt = strtolower(config('settings.app.name'));
	$logoWidth = (int)config('settings.upload.img_resize_logo_width', 454);
	$logoHeight = (int)config('settings.upload.img_resize_logo_height', 80);
@endphp
<div class="header">
	<nav class="navbar fixed-top navbar-site navbar-light bg-light navbar-expand-md" role="navigation">
		<div class="container">
			
			<div class="navbar-identity p-sm-0">
				{{-- Logo --}}
				<a href="{{ url('/') }}" class="navbar-brand logo logo-title">
					<img src="{{ $logoDarkUrl }}"
					     alt="{{ $logoAlt }}"
					     class="main-logo light-logo"
					     style="max-width: {{ $logoWidth }}px; max-height: {{ $logoHeight }}px; width:auto;"
					/>
					<img src="{{ $logoLightUrl }}"
					     alt="{{ $logoAlt }}"
					     class="main-logo dark-logo"
					     style="max-width: {{ $logoWidth }}px; max-height: {{ $logoHeight }}px; width:auto;"
					/>
				</a>
				{{-- Toggle Nav (Mobile) --}}
				<button class="navbar-toggler -toggler float-end"
						type="button"
						data-bs-toggle="collapse"
						data-bs-target="#navbarsDefault"
						aria-controls="navbarsDefault"
						aria-expanded="false"
						aria-label="Toggle navigation"
				>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="30" height="30" focusable="false">
						<title>{{ t('Menu') }}</title>
						<path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-miterlimit="10" d="M4 7h22M4 15h22M4 23h22"></path>
					</svg>
				</button>
			</div>
			
			<div class="navbar-collapse collapse" id="navbarsDefault">
				<ul class="nav navbar-nav me-md-auto navbar-left">
					{{----}}
				</ul>
				
				<ul class="nav navbar-nav ms-auto navbar-right">
					@include('front.layouts.inc.menu.languages')
				</ul>
			</div>
			
			
		</div>
	</nav>
</div>
