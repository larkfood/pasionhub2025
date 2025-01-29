<script>
	const phpDateFormat = "{{ $phpDateFormat }}";
	const phpDatetimeFormat = "{{ $phpDatetimeFormat }}";
	const phpDateFormatHint = "{!! escapeStringForJs($phpDateFormatHint) !!}";
	
	const isoDateFormat = "{{ $isoDateFormat }}";
	const isoDatetimeFormat = "{{ $isoDatetimeFormat }}";
	const isoDateFormatHint = "{!! escapeStringForJs($isoDateFormatHint) !!}";
	
	onDocumentReady((event) => {
		const darkModeEl = document.querySelector("input[type=checkbox][name=dark_mode]");
		if (darkModeEl) {
			toggleDarkModeFields(darkModeEl);
			darkModeEl.addEventListener("change", e => toggleDarkModeFields(e.target));
		}
		
		const phpSpecificDateFormatEl = document.querySelector("input[type=checkbox][name=php_specific_date_format]");
		if (phpSpecificDateFormatEl) {
			applyPhpSpecificDateFormatActions(phpSpecificDateFormatEl);
			phpSpecificDateFormatEl.addEventListener("change", e => applyPhpSpecificDateFormatActions(e.target));
		}
		
		const showCountriesChartsEl = document.querySelector("input[type=checkbox][name=show_countries_charts]");
		if (showCountriesChartsEl) {
			toggleCountriesChartsFields(showCountriesChartsEl);
			showCountriesChartsEl.addEventListener("change", e => toggleCountriesChartsFields(e.target));
		}
	});
	
	function toggleDarkModeFields(darkModeEl) {
		let action = darkModeEl.checked ? "show" : "hide";
		setElementsVisibility(action, '.dark-mode-field');
	}
	
	function applyPhpSpecificDateFormatActions(phpSpecificDateFormatEl) {
		let dateFormat;
		let datetimeFormat;
		let dateFormatHint;
		
		if (phpSpecificDateFormatEl.checked) {
			dateFormat = phpDateFormat;
			datetimeFormat = phpDateFormat;
			dateFormatHint = phpDateFormatHint;
		} else {
			dateFormat = isoDateFormat;
			datetimeFormat = isoDatetimeFormat;
			dateFormatHint = isoDateFormatHint;
		}
		
		const dateFormatEl = document.querySelector("input[type=text][name=date_format]");
		if (dateFormatEl) {
			dateFormatEl.value = dateFormat;
			const dateFormatHintEl = dateFormatEl.nextElementSibling;
			if (dateFormatHintEl) {
				dateFormatHintEl.innerHTML = dateFormatHint;
				
				/* Initialize all popovers in the dateFormatHintEl */
				initElementPopovers(dateFormatHintEl, {html: true});
			}
		}
		
		const datetimeFormatEl = document.querySelector("input[type=text][name=datetime_format]");
		if (datetimeFormatEl) {
			datetimeFormatEl.value = datetimeFormat;
			const datetimeFormatHintEl = datetimeFormatEl.nextElementSibling;
			if (datetimeFormatHintEl) {
				datetimeFormatHintEl.innerHTML = dateFormatHint;
				
				/* Initialize all popovers in the datetimeFormatHintEl */
				initElementPopovers(datetimeFormatHintEl, {html: true});
			}
		}
	}
	
	function toggleCountriesChartsFields(showCountriesChartsEl) {
		let action = showCountriesChartsEl.checked ? "show" : "hide";
		setElementsVisibility(action, '.countries-charts-field');
	}
</script>
