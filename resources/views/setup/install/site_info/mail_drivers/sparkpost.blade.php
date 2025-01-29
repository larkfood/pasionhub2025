<div class="row row-cols-2 sparkpost">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.sparkpost_secret'),
			'type'     => 'text',
			'name'     => 'settings[mail][sparkpost_secret]',
			'value'    => data_get($siteInfo, 'settings.mail.sparkpost_secret'),
			'required' => true,
		])
	</div>
	
	<div class="col"></div>
	
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.sparkpost_host'),
			'type'     => 'text',
			'name'     => 'settings[mail][sparkpost_host]',
			'value'    => data_get($siteInfo, 'settings.mail.sparkpost_host'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.sparkpost_port'),
			'type'     => 'text',
			'name'     => 'settings[mail][sparkpost_port]',
			'value'    => data_get($siteInfo, 'settings.mail.sparkpost_port'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.sparkpost_username'),
			'type'     => 'text',
			'name'     => 'settings[mail][sparkpost_username]',
			'value'    => data_get($siteInfo, 'settings.mail.sparkpost_username'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.sparkpost_password'),
			'type'     => 'text',
			'name'     => 'settings[mail][sparkpost_password]',
			'value'    => data_get($siteInfo, 'settings.mail.sparkpost_password'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.sparkpost_encryption'),
			'type'     => 'text',
			'name'     => 'settings[mail][sparkpost_encryption]',
			'value'    => data_get($siteInfo, 'settings.mail.sparkpost_encryption'),
			'hint'     => trans('messages.smtp_encryption_hint'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][sparkpost_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.sparkpost_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
