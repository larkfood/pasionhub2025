@php
	use App\Helpers\Files\Storage\StorageDisk;
	
	// Logo
	$logoFactoryUrl = config('larapen.media.logo-factory');
	$logoUrl = '';
	try {
		if (is_link(public_path('storage'))) {
			$disk = StorageDisk::getDisk();
			$defaultLogo = config('larapen.media.logo');
			if (!empty($defaultLogo) && $disk->exists($defaultLogo)) {
				$logoUrl = $disk->url($defaultLogo);
			}
		}
	} catch (\Throwable $e) {}
	$logoUrl = empty($logoUrl) ? $logoFactoryUrl : $logoUrl;
	$logoCssSize = 'max-width:200px; max-height:40px; width:auto; height:auto;';
@endphp
<div class="header">
	<nav class="navbar fixed-top navbar-site navbar-light bg-light navbar-expand-md" role="navigation">
		<div class="container">
			
			<div class="navbar-identity p-sm-0">
				{{-- Logo --}}
				<a href="{{ url('/') }}" class="navbar-brand logo logo-title">
					<img src="{{ $logoUrl }}" alt="logo" style="float:left; margin:0 5px 0 0; {!! $logoCssSize !!}">
				</a>
			</div>
			
			<div class="navbar-collapse collapse" id="navbarsDefault">
				<ul class="nav navbar-nav me-md-auto navbar-left"></ul>
				<ul class="nav navbar-nav ms-auto navbar-right"></ul>
			</div>
			
		</div>
	</nav>
</div>
