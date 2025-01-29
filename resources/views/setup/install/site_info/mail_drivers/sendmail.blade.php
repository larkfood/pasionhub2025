<div class="row row-cols-2 sendmail">
	<div class="col">
		@php
			$sendmailPath = '/usr/sbin/sendmail -bs';
		@endphp
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.sendmail_path'),
			'type'     => 'text',
			'name'     => 'settings[mail][sendmail_path]',
			'value'    => data_get($siteInfo, 'settings.mail.sendmail_path', $sendmailPath),
			'hint'     => trans('admin.sendmail_path_hint'),
			'required' => false,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][sendmail_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.sendmail_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
