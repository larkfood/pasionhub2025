<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;

// ===| DATABASE |===
try {
	
	// settings
	$field = '{"name":"value","label":"Logo","type":"image","upload":"true","disk":"uploads","default":"images/logo@2x.png"}';
	DB::table('settings')->where('key', '=', 'app_logo')->update(['field' => $field]);
	
	$options = '{"default":"Default","blue":"Blue","yellow":"Yellow","green":"Green","red":"Red"}';
	$field = '{"name":"value","label":"Value","type":"select_from_array","options":' . $options . '}';
	DB::table('settings')->where('key', '=', 'app_theme')->update(['field' => $field]);
	
	$options = '{"smtp":"SMTP","mailgun":"Mailgun","mandrill":"Mandrill","ses":"Amazon SES","mail":"PHP Mail","sendmail":"Sendmail"}';
	$field = '{"name":"value","label":"Value","type":"select_from_array","options":' . $options . '}';
	DB::table('settings')->where('key', '=', 'mail_driver')->update(['field' => $field]);
	
	$allData = [
		[
			'key'         => 'upload_max_file_size',
			'name'        => 'Upload Max File Size',
			'value'       => '2500',
			'description' => 'Upload Max File Size (in KB)',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 25,
			'rgt'         => 25,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-01-13 11:21:08',
		],
		[
			'key'         => 'admin_notification',
			'name'        => 'settings_mail_admin_notification_label',
			'value'       => '0',
			'description' => 'settings_mail_admin_notification_hint',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 26,
			'rgt'         => 33,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-01-13 14:38:08',
		],
		[
			'key'         => 'payment_notification',
			'name'        => 'settings_mail_payment_notification_label',
			'value'       => '0',
			'description' => 'settings_mail_payment_notification_hint',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 26,
			'rgt'         => 33,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-01-13 14:38:08',
		],
	];
	foreach ($allData as $item) {
		$key = $item['key'] ?? '';
		
		// Delete the setting if exists
		DB::table('settings')->where('key', '=', $key)->delete();
		
		// Save the setting if not exists
		$setting = \App\Models\Setting::where('key', $key)->first();
		if (empty($setting)) {
			DB::table('settings')->insert($item);
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
