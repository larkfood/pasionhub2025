@php
	// Google Maps
	$isGoogleMapsEmbedApiEnabled ??= true;
	$geocodingApiKey = config('services.googlemaps.key');
	$mapHeight = 400;
	$city ??= [];
	$geoMapAddress = getItemAddressForMap($city);
	$googleMapsApiUrl = $isGoogleMapsEmbedApiEnabled
		? getGoogleMapsEmbedApiUrl($geocodingApiKey, $geoMapAddress)
		: getGoogleMapsApiUrl($geocodingApiKey);
@endphp

@if (!empty($geocodingApiKey))
	<div class="intro-inner" style="height: {{ $mapHeight }}px;">
		@if ($isGoogleMapsEmbedApiEnabled)
			<iframe
					id="googleMaps"
					width="100%"
					height="{{ $mapHeight }}"
					style="border:0;"
					loading="lazy"
					title="{{ $geoMapAddress }}"
					aria-label="{{ $geoMapAddress }}"
					src="{{ $googleMapsApiUrl }}"
			></iframe>
		@else
			<div id="googleMaps" style="width: 100%; height: {{ $mapHeight }}px;"></div>
		@endif
	</div>
@endif

@section('after_scripts')
	@parent
	@if (!$isGoogleMapsEmbedApiEnabled)
		@if (!empty($googleMapsApiUrl))
			<script async defer src="{{ $googleMapsApiUrl }}"></script>
		@endif
		<script>
			var geocodingApiKey = '{{ $geocodingApiKey }}';
			var locationAddress = '{{ $geoMapAddress }}';
			var locationMapElId = 'googleMaps';
			var locationMapId = '{{ uniqueCode(16) }}';
		</script>
		<script src="{{ url('assets/js/app/google-maps.js') }}"></script>
	@endif
@endsection
