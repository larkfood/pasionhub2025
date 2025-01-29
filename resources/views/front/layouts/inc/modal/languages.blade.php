@php
	$supportedLanguagesExist ??= true;
	$showCountryFlagNextLang ??= false;
	$multiCountryIsEnabled ??= false;
	$supportedLanguages ??= [];
	$countryName ??= config('country.name');
	$countryFlagImg ??= null;
@endphp
{{-- Languages List --}}
<div class="modal fade modalHasList" id="selectLanguage" tabindex="-1" aria-labelledby="selectCountryLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			
			<div class="modal-header px-3">
				<h4 class="modal-title uppercase fw-bold" id="selectCountryLabel">
					@if (!$supportedLanguagesExist && ($showCountryFlagNextLang && $multiCountryIsEnabled))
						<i class="bi bi-geo"></i> {{ t('country') }}
					@else
						<i class="bi bi-translate"></i> {{ t('language') }}
					@endif
				</h4>
				
				<button type="button" class="close" data-bs-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">{{ t('Close') }}</span>
				</button>
			</div>
			
			<div class="modal-body">
				<div id="modalBodyLanguageList" class="row row-cols-lg-3 row-cols-md-3 row-cols-sm-2 row-cols-2 px-lg-1 px-md-1 px-3">
					
					@if ($supportedLanguagesExist)
						@foreach($supportedLanguages as $langCode => $lang)
							@php
								// Get the language infos
								$langName = $lang['name'] ?? '--';
								$langNativeName = $lang['native'] ?? '--';
								$langTag = $lang['tag'] ?? getLangTag($langCode);
								$langFlag = $lang['flag'] ?? '';
								
								// Language flag
								$langFlagCountry = str_replace('flag-icon-', '', $langFlag);
								$isFlagEnabled = (
									config('settings.localization.show_languages_flags')
									&& !empty(trim($langFlag)) && is_string($langFlag)
								);
								
								// Is it the current language?
								$isActivatedLang = (strtolower($langCode) == strtolower(config('app.locale')));
								
								// Get the language icon
								$checkBox = $isActivatedLang
												? '<i class="fa-solid fa-circle-dot"></i>'
												: '<i class="fa-regular fa-circle"></i>';
								$checkBox .= '&nbsp;';
								
								// Get the language flag
								$langFlag = '<img src="' . getCountryFlagUrl($langFlagCountry) . '">&nbsp;';
								$langFlag .= '&nbsp;';
								
								// Get the language prefix
								$langPrefix = $isFlagEnabled ? $langFlag : $checkBox;
								
								// Language URL & link infos
								$langUrl = url('locale/' . $langCode);
								$langTitle = ($langName != $langNativeName)
									? $langNativeName . ' - ' . $langName
									: $langName;
								$langAttr = 'tabindex="-1" rel="alternate" hreflang="' . $langTag . '"';
								$tooltip = 'data-bs-toggle="tooltip" data-bs-custom-class="modal-tooltip" title="' . $langTitle . '"';
								
								// Language display label
								$langNativeLabel = $isActivatedLang
									? '<span class="fw-bold" ' . $tooltip . '>' . $langNativeName . '</span>'
									: '<a href="' . $langUrl . '" ' . $langAttr . ' ' . $tooltip . '>' . $langNativeName . '</a>';
								$langLabel = $langPrefix . $langNativeLabel;
							@endphp
							<div class="col mb-1 cat-list">
								{!! $langLabel !!}
							</div>
						@endforeach
					@endif
				
				</div>
				
				@if ($showCountryFlagNextLang && $multiCountryIsEnabled)
					<div class="row px-lg-1 px-md-1 px-3">
						@if ($supportedLanguagesExist)
							<hr class="hr mt-3" />
						@endif
						@php
							$surfingOn = t('surfing_on', [
								'appName' => config('app.name'),
								'country' => $countryName
							]);
							$changeCountry = t('change_country');
						@endphp
						<div class="col col-8 mb-1 cat-list">
							<span class="float-start">
								@if (!empty($countryFlagImg))
									{!! $countryFlagImg !!}
								@endif
								{{ $surfingOn }}
							</span>
						</div>
						<div class="col col-4 mb-1 cat-list">
							<a data-bs-target="#selectCountry"
							   data-bs-toggle="modal"
							   class="btn btn-sm btn-primary rounded-pill float-end"
							   data-bs-toggle="tooltip"
							   data-bs-custom-class="modal-tooltip"
							   title="{{ $changeCountry }}"
							>
								{{ $changeCountry }}
							</a>
						</div>
					</div>
				@endif
			</div>
		
		</div>
	</div>
</div>
