@php
	$siteInfo ??= [];
	$mailDrivers ??= [];
	$mailDriversSelectorsJson ??= '[]';
@endphp
<hr class="border-0 bg-secondary">

<h3 class="title-3">
	<i class="bi bi-envelope"></i> {{ trans('messages.mail_sending_configuration') }}
</h3>

<div class="row row-cols-2">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mail_driver'),
			'type'     => 'select',
			'name'     => 'settings[mail][driver]',
			'value'    => data_get($siteInfo, 'settings.mail.driver'),
			'options'  => $mailDrivers,
			'required' => false,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mail_driver_test'),
			'type'     => 'checkbox_switch',
			'name'     => 'settings[mail][driver_test]',
			'value'    => '1',
			'checked'  => (data_get($siteInfo, 'settings.mail.driver_test') == '1'),
			'hint'     => trans('admin.mail_driver_test_hint'),
			'required' => false,
		])
	</div>
</div>

@if (array_key_exists('sendmail', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.sendmail'))
		@include('setup.install.site_info.mail_drivers.sendmail')
	@endif
@endif
@if (array_key_exists('smtp', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.smtp'))
		@include('setup.install.site_info.mail_drivers.smtp')
	@endif
@endif
@if (array_key_exists('mailgun', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.mailgun'))
		@include('setup.install.site_info.mail_drivers.mailgun')
	@endif
@endif
@if (array_key_exists('postmark', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.postmark'))
		@include('setup.install.site_info.mail_drivers.postmark')
	@endif
@endif
@if (array_key_exists('ses', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.ses'))
		@include('setup.install.site_info.mail_drivers.ses')
	@endif
@endif
@if (array_key_exists('sparkpost', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.sparkpost'))
		@include('setup.install.site_info.mail_drivers.sparkpost')
	@endif
@endif
@if (array_key_exists('resend', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.resend'))
		@include('setup.install.site_info.mail_drivers.resend')
	@endif
@endif
@if (array_key_exists('mailersend', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.mailersend'))
		@include('setup.install.site_info.mail_drivers.mailersend')
	@endif
@endif
@if (array_key_exists('brevo', $mailDrivers))
	@if (view()->exists('setup.install.site_info.mail_drivers.brevo'))
		@include('setup.install.site_info.mail_drivers.brevo')
	@endif
@endif

@section('after_scripts')
	@parent
	<script>
		let mailDriversSelectors = {!! $mailDriversSelectorsJson !!};
		let mailDriversSelectorsList = Object.values(mailDriversSelectors);
		
		onDocumentReady((event) => {
			let driverEl = document.querySelector('select[name="settings[mail][driver]"]');
			let driverTestEl = document.querySelector('input[type=checkbox][name="settings[mail][driver_test]"]');
			if (!driverEl || !driverTestEl) return;
			
			getDriverFields(driverEl, driverTestEl);
			
			/* On driver element (select2) change|select */
			$(driverEl).on('change', e => getDriverFields(e.target, driverTestEl));
			
			/* On driver test element (checkbox) check */
			driverTestEl.addEventListener('change', e => getDriverFields(driverEl, e.target));
			
			let driverTestParentEl = driverTestEl.closest('div.form-check');
			if (driverTestParentEl) {
				driverTestParentEl.addEventListener('click', e => toggleDriverTestEl(e.target));
			}
		});
		
		function getDriverFields(driverEl, driverTestEl) {
			/* Show the selected driver fields */
			const driverElValue = driverEl.value;
			const selectedDriverSelector = mailDriversSelectors[driverElValue] ?? "";
			/* const driversSelectorsListToHide = mailDriversSelectorsList.filter(item => item !== selectedDriverSelector); */
			
			/* Hide all drivers fields except those of the selected driver */
			/* setElementsVisibility('hide', driversSelectorsListToHide); */
			setElementsVisibility('hide', mailDriversSelectorsList);
			
			if (driverElValue === 'sendmail') {
				/* Show the 'sendmail' driver fields only when the driver validation is enabled */
				/* That allows to use default sendmail parameters if validation is not required */
				if (isElDefined(driverTestEl) && driverTestEl.checked) {
					setElementsVisibility('show', selectedDriverSelector);
				}
			} else {
				setElementsVisibility('show', selectedDriverSelector);
			}
		}
		
		function toggleDriverTestEl(el) {
			if (!el) return;
			if (el.tagName.toLowerCase() === 'input') return;
			if (el.tagName.toLowerCase() !== 'div' || !el.classList.contains('form-check')) {
				el = el.closest('div.form-check');
			}
			
			el = el.querySelector('input[type=checkbox]');
			if (el.tagName.toLowerCase() === 'input') {
				el.checked = !el.checked;
				el.dispatchEvent(new Event('change'));
			}
		}
	</script>
@endsection
