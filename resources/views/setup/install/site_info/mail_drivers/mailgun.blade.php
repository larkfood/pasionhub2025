<div class="row row-cols-2 mailgun">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_domain'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_domain]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_domain'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_secret'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_secret]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_secret'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_endpoint'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_endpoint]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_endpoint', 'api.mailgun.net'),
			'required' => true,
		])
	</div>
	
	<div class="col"></div>
	
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_host'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_host]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_host'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_port'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_port]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_port'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_username'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_username]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_username'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_password'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_password]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_password'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailgun_encryption'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_encryption]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_encryption'),
			'hint'     => trans('messages.smtp_encryption_hint'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailgun_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.mailgun_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
