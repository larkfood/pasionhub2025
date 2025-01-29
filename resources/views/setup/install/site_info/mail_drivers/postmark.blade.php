<div class="row row-cols-2 postmark">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.postmark_token'),
			'type'     => 'text',
			'name'     => 'settings[mail][postmark_token]',
			'value'    => data_get($siteInfo, 'settings.mail.postmark_token'),
			'required' => true,
		])
	</div>
	
	<div class="col"></div>
	
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.postmark_host'),
			'type'     => 'text',
			'name'     => 'settings[mail][postmark_host]',
			'value'    => data_get($siteInfo, 'settings.mail.postmark_host'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.postmark_port'),
			'type'     => 'text',
			'name'     => 'settings[mail][postmark_port]',
			'value'    => data_get($siteInfo, 'settings.mail.postmark_port'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.postmark_username'),
			'type'     => 'text',
			'name'     => 'settings[mail][postmark_username]',
			'value'    => data_get($siteInfo, 'settings.mail.postmark_username'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.postmark_password'),
			'type'     => 'text',
			'name'     => 'settings[mail][postmark_password]',
			'value'    => data_get($siteInfo, 'settings.mail.postmark_password'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.postmark_encryption'),
			'type'     => 'text',
			'name'     => 'settings[mail][postmark_encryption]',
			'value'    => data_get($siteInfo, 'settings.mail.postmark_encryption'),
			'hint'     => trans('messages.smtp_encryption_hint'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][postmark_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.postmark_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
