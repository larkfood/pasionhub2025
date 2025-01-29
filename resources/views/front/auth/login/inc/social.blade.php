@if (isSocialAuthEnabled())
	@if (isset($boxedCol) && !empty($boxedCol) && is_numeric($boxedCol))
		<div class="col-12">
			<div class="row d-flex justify-content-center">
				<div class="col-{{ $boxedCol }}"> {{-- col-8 --}}
					@endif
					
					@php
						$sGutter = 'gx-2 gy-2';
						if (isset($socialCol) && !empty($socialCol) && is_numeric($socialCol)) {
							if ($socialCol >= 10) {
								$sGutter = 'gx-2 gy-1';
							}
							$sCol = 'col-xl-6 col-lg-6 col-md-6';
							$sCol = str_replace('-6', '-' . $socialCol, $sCol);
						} else {
							$sCol = 'col-xl-6 col-lg-6 col-md-6';
						}
						
						$loginWithFacebook = t('login_with', ['media' => 'Facebook']);
						$loginWithLinkedIn = t('login_with', ['media' => 'LinkedIn']);
						$loginWithTwitter = t('login_with', ['media' => 'X (Twitter)']);
						$loginWithGoogle = t('login_with', ['media' => 'Google']);
					@endphp
					<div class="row mb-3 social-media d-flex justify-content-center {{ $sGutter }}">
						@if (isSocialAuthEnabled('facebook'))
							<div class="{{ $sCol }} col-sm-12 col-12">
								<div class="col-xl-12 col-md-12 col-sm-12 col-12 btn btn-facebook">
									<a href="{{ url('auth/facebook') }}" title="{!! strip_tags($loginWithFacebook) !!}">
										<i class="fa-brands fa-facebook"></i> {!! $loginWithFacebook !!}
									</a>
								</div>
							</div>
						@endif
						@if (isSocialAuthEnabled('linkedin'))
							<div class="{{ $sCol }} col-sm-12 col-12">
								<div class="col-xl-12 col-md-12 col-sm-12 col-12 btn btn-linkedin">
									<a href="{{ url('auth/linkedin') }}" title="{!! strip_tags($loginWithLinkedIn) !!}">
										<i class="fa-brands fa-linkedin"></i> {!! $loginWithLinkedIn !!}
									</a>
								</div>
							</div>
						@endif
						@if (isSocialAuthEnabled('twitterOauth2'))
							<div class="{{ $sCol }} col-sm-12 col-12">
								<div class="col-xl-12 col-md-12 col-sm-12 col-12 btn btn-x-twitter">
									<a href="{{ url('auth/twitter-oauth-2') }}" title="{!! strip_tags($loginWithTwitter) !!}">
										<i class="fa-brands fa-x-twitter"></i> {!! $loginWithTwitter !!}
									</a>
								</div>
							</div>
						@endif
						@if (isSocialAuthEnabled('twitterOauth1'))
							<div class="{{ $sCol }} col-sm-12 col-12">
								<div class="col-xl-12 col-md-12 col-sm-12 col-12 btn btn-x-twitter">
									<a href="{{ url('auth/twitter') }}" title="{!! strip_tags($loginWithTwitter) !!}">
										<i class="fa-brands fa-x-twitter"></i> {!! $loginWithTwitter !!}
									</a>
								</div>
							</div>
						@endif
						@if (isSocialAuthEnabled('google'))
							<div class="{{ $sCol }} col-sm-12 col-12">
								<div class="col-xl-12 col-md-12 col-sm-12 col-12 btn btn-google">
									<a href="{{ url('auth/google') }}" title="{!! strip_tags($loginWithGoogle) !!}">
										<i class="fa-brands fa-google"></i> {!! $loginWithGoogle !!}
									</a>
								</div>
							</div>
						@endif
					</div>
					
					<div class="row d-flex justify-content-center loginOr my-4">
						<div class="col-12">
							<hr class="hrOr">
							<span class="spanOr rounded">{{ t('or') }}</span>
						</div>
					</div>
					
					@if (isset($boxedCol) && !empty($boxedCol) && is_numeric($boxedCol))
				</div>
			</div>
		</div>
	@endif
@endif
