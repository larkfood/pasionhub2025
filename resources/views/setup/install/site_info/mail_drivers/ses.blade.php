<div class="row row-cols-2 ses">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_key'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_key]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_key'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_secret'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_secret]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_secret'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_region'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_region]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_region'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_token'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_token]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_token'),
			'hint'     => trans('messages.ses_token_hint'),
			'required' => true,
		])
	</div>
	
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_host'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_host]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_host'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_port'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_port]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_port'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_username'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_username]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_username'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_password'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_password]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_password'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.ses_encryption'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_encryption]',
			'value'    => data_get($siteInfo, 'settings.mail.ses_encryption'),
			'hint'     => trans('messages.smtp_encryption_hint'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][ses_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.mailersend_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
