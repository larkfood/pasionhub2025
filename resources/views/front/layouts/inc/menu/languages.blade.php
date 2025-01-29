@php
	/*
	 * The languages selection nav-item view variables are also used in the languages modal view
	 * available at: ../modal/languages.blade.php
	 */
	 
	$countries ??= collect();
	$showCountryFlagNextLang = (config('settings.localization.show_country_flag') == 'in_next_lang');
	
	$showCountrySpokenLang = config('settings.localization.show_country_spoken_languages');
	$showCountrySpokenLang = str_starts_with($showCountrySpokenLang, 'active');
	$supportedLanguages = $showCountrySpokenLang ? getCountrySpokenLanguages() : getSupportedLanguages();
	
	$supportedLanguagesExist = (count($supportedLanguages) > 1);
	$isLangOrCountryCanBeSelected = ($supportedLanguagesExist || $showCountryFlagNextLang);
	
	// Check if the Multi-Countries selection is enabled
	$multiCountryIsEnabled = false;
	$multiCountryLabel = '';
	if ($showCountryFlagNextLang) {
		if (!empty(config('country.code'))) {
			if ($countries->count() > 1) {
				$multiCountryIsEnabled = true;
			}
		}
	}
	
	$countryName = config('country.name');
	$countryFlag32Url = config('country.flag32_url');
	
	$countryFlagImg = $showCountryFlagNextLang
		? '<img class="flag-icon" src="' . $countryFlag32Url . '" alt="' . $countryName . '">'
		: null;
@endphp

@if ($isLangOrCountryCanBeSelected)
	<li class="nav-item dropdown lang-menu no-arrow">
		<a href="#selectLanguage" data-bs-toggle="modal" class="nav-link pt-1">
			@if (!empty($countryFlagImg))
				<span>
					{!! $countryFlagImg !!}
					{{ strtoupper(config('app.locale')) }}
				</span>
			@else
				<span><i class="bi bi-globe2"></i></span>
				<i class="bi bi-chevron-down"></i>
			@endif
		</a>
	</li>
@endif

@section('modal_languages')
	@include('front.layouts.inc.modal.languages')
@endsection
