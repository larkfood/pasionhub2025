<div class="row row-cols-2 brevo">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.brevo_api_key'),
			'type'     => 'text',
			'name'     => 'settings[mail][brevo_api_key]',
			'value'    => data_get($siteInfo, 'settings.mail.brevo_api_key'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][brevo_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.brevo_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
