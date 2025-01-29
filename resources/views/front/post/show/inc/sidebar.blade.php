@php
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;
	
	$post ??= [];
	$user ??= [];
	$countPackages ??= 0;
	$countPaymentMethods ??= 0;
	
	// Google Maps
	$isMapEnabled = (config('settings.listing_page.show_listing_on_googlemap') == '1');
	$isGoogleMapsEmbedApiEnabled ??= true;
	$geocodingApiKey = config('services.googlemaps.key');
	$mapHeight = 250;
	$city = data_get($post, 'city', []);
	$geoMapAddress = getItemAddressForMap($city);
	$googleMapsApiUrl = $isGoogleMapsEmbedApiEnabled
		? getGoogleMapsEmbedApiUrl($geocodingApiKey, $geoMapAddress)
		: getGoogleMapsApiUrl($geocodingApiKey);
@endphp
<aside>
	<div class="card card-user-info sidebar-card">
		@if ($authUserId == data_get($post, 'user_id'))
			<div class="card-header">{{ t('Manage Listing') }}</div>
		@else
			<div class="block-cell user">
				<div class="cell-media">
					<img src="{{ data_get($post, 'user_photo_url') }}" alt="{{ data_get($post, 'contact_name') }}">
				</div>
				<div class="cell-content">
					<h5 class="title">{{ t('Posted by') }}</h5>
					<span class="name">
						@if (!empty($user))
							<a href="{{ urlGen()->user($user) }}">
								{{ data_get($post, 'contact_name') }}
							</a>
						@else
							{{ data_get($post, 'contact_name') }}
						@endif
					</span>
					
					@if (config('plugins.reviews.installed'))
						@if (view()->exists('reviews::ratings-user'))
							@include('reviews::ratings-user')
						@endif
					@endif
				
				</div>
			</div>
		@endif
		
		<div class="card-content">
			@php
				$evActionStyle = 'style="border-top: 0;"';
			@endphp
			@if (empty($authUserId) || ($authUserId != data_get($post, 'user_id')))
				<div class="card-body text-start">
					<div class="grid-col">
						<div class="col from">
							<i class="bi bi-geo-alt"></i>
							<span>{{ t('location') }}</span>
						</div>
						<div class="col to">
							<span>
								<a href="{!! urlGen()->city(data_get($post, 'city')) !!}">
									{{ data_get($post, 'city.name') }}
								</a>
							</span>
						</div>
					</div>
					@if (!config('settings.listing_page.hide_date'))
						@if (!empty($user) && !empty(data_get($user, 'created_at_formatted')))
							<div class="grid-col">
								<div class="col from">
									<i class="bi bi-person-check"></i>
									<span>{{ t('Joined') }}</span>
								</div>
								<div class="col to">
									<span>{!! data_get($user, 'created_at_formatted') !!}</span>
								</div>
							</div>
						@endif
					@endif
				</div>
				@php
					$evActionStyle = 'style="border-top: 1px solid #ddd;"';
				@endphp
			@endif
			
			<div class="ev-action" {!! $evActionStyle !!}>
				@if (!empty($authUser))
					@if ($authUserId == data_get($post, 'user_id'))
						<a href="{{ urlGen()->editPost($post) }}" class="btn btn-default btn-block">
							<i class="fa-regular fa-pen-to-square"></i> {{ t('Update the details') }}
						</a>
						@if (isMultipleStepsFormEnabled())
							<a href="{{ url('posts/' . data_get($post, 'id') . '/photos') }}" class="btn btn-default btn-block">
								<i class="fa-solid fa-camera"></i> {{ t('Update Photos') }}
							</a>
							@if ($countPackages > 0 && $countPaymentMethods > 0)
								<a href="{{ url('posts/' . data_get($post, 'id') . '/payment') }}" class="btn btn-success btn-block">
									<i class="fa-regular fa-circle-check"></i> {{ t('Make It Premium') }}
								</a>
							@endif
						@endif
						@if (empty(data_get($post, 'archived_at')) && isVerifiedPost($post))
							<a href="{{ url('account/posts/list/' . data_get($post, 'id') . '/offline') }}"
							   class="btn btn-warning btn-block confirm-simple-action"
							>
								<i class="fa-solid fa-eye-slash"></i> {{ t('put_it_offline') }}
							</a>
						@endif
						@if (!empty(data_get($post, 'archived_at')))
							<a href="{{ url('account/posts/archived/' . data_get($post, 'id') . '/repost') }}"
							   class="btn btn-info btn-block confirm-simple-action"
							>
								<i class="fa-solid fa-recycle"></i> {{ t('re_post_it') }}
							</a>
						@endif
					@else
						{!! genPhoneNumberBtn($post, true) !!}
						{!! genEmailContactBtn($post, true) !!}
					@endif
						@php
							try {
								if (doesUserHavePermission($authUser, \App\Models\Permission::getStaffPermissions())) {
									$btnUrl = admin_url('blacklists/add') . '?';
									$btnQs = (!empty(data_get($post, 'email'))) ? 'email=' . data_get($post, 'email') : '';
									$btnQs = (!empty($btnQs)) ? $btnQs . '&' : $btnQs;
									$btnQs = (!empty(data_get($post, 'phone'))) ? $btnQs . 'phone=' . data_get($post, 'phone') : $btnQs;
									$btnUrl = $btnUrl . $btnQs;
									
									if (!isDemoDomain($btnUrl)) {
										$btnText = trans('admin.ban_the_user');
										$btnHint = $btnText;
										if (!empty(data_get($post, 'email')) && !empty(data_get($post, 'phone'))) {
											$btnHint = trans('admin.ban_the_user_email_and_phone', [
												'email' => data_get($post, 'email'),
												'phone' => data_get($post, 'phone'),
											]);
										} else {
											if (!empty(data_get($post, 'email'))) {
												$btnHint = trans('admin.ban_the_user_email', ['email' => data_get($post, 'email')]);
											}
											if (!empty(data_get($post, 'phone'))) {
												$btnHint = trans('admin.ban_the_user_phone', ['phone' => data_get($post, 'phone')]);
											}
										}
										$tooltip = ' data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $btnHint . '"';
										
										$btnOut = '<a href="'. $btnUrl .'" class="btn btn-outline-danger btn-block confirm-simple-action"'. $tooltip .'>';
										$btnOut .= $btnText;
										$btnOut .= '</a>';
										
										echo $btnOut;
									}
								}
							} catch (\Throwable $e) {}
						@endphp
				@else
					{!! genPhoneNumberBtn($post, true) !!}
					{!! genEmailContactBtn($post, true) !!}
				@endif
			</div>
		</div>
	</div>
	
	@if ($isMapEnabled)
		<div class="card sidebar-card">
			<div class="card-header">{{ t('location_map') }}</div>
			<div class="card-content">
				<div class="card-body text-start p-0">
					<div class="posts-googlemaps">
						@if ($isGoogleMapsEmbedApiEnabled)
							<iframe id="googleMaps"
							        width="100%"
							        height="{{ $mapHeight }}"
							        src="{{ $googleMapsApiUrl }}"
							        loading="lazy"
							></iframe>
						@else
							<div id="googleMaps" style="width: 100%; height: {{ $mapHeight }}px;"></div>
						@endif
					</div>
				</div>
			</div>
		</div>
	@endif
	
	@if (isVerifiedPost($post))
		@include('front.layouts.inc.social.horizontal')
	@endif
	
	<div class="card sidebar-card">
		<div class="card-header">{{ t('Safety Tips for Buyers') }}</div>
		<div class="card-content">
			<div class="card-body text-start">
				<ul class="list-check">
					<li>{{ t('Meet seller at a public place') }}</li>
					<li>{{ t('Check the item before you buy') }}</li>
					<li>{{ t('Pay only after collecting the item') }}</li>
				</ul>
				@php
					$tipsLinkAttributes = getUrlPageByType('tips');
				@endphp
				@if (!str_contains($tipsLinkAttributes, 'href="#"') && !str_contains($tipsLinkAttributes, 'href=""'))
					<p>
						<a class="float-end" {!! $tipsLinkAttributes !!}>
							{{ t('Know more') }} <i class="fa-solid fa-angles-right"></i>
						</a>
					</p>
				@endif
			</div>
		</div>
	</div>
</aside>

@section('after_scripts')
	@parent
	@if ($isMapEnabled)
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
	@endif
@endsection
