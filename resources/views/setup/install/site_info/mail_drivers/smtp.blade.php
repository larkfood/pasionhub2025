<div class="row row-cols-2 smtp">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.smtp_host'),
			'type'     => 'text',
			'name'     => 'settings[mail][smtp_host]',
			'value'    => data_get($siteInfo, 'settings.mail.smtp_host'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.smtp_port'),
			'type'     => 'text',
			'name'     => 'settings[mail][smtp_port]',
			'value'    => data_get($siteInfo, 'settings.mail.smtp_port'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.smtp_username'),
			'type'     => 'text',
			'name'     => 'settings[mail][smtp_username]',
			'value'    => data_get($siteInfo, 'settings.mail.smtp_username'),
			'required' => false,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.smtp_password'),
			'type'     => 'text',
			'name'     => 'settings[mail][smtp_password]',
			'value'    => data_get($siteInfo, 'settings.mail.smtp_password'),
			'required' => false,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.smtp_encryption'),
			'type'     => 'text',
			'name'     => 'settings[mail][smtp_encryption]',
			'value'    => data_get($siteInfo, 'settings.mail.smtp_encryption'),
			'hint'     => trans('messages.smtp_encryption_hint'),
			'required' => false,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][smtp_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.smtp_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
