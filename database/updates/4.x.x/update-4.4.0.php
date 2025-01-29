<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBIndex;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/Account/MessagesController.php'));
	File::delete(base_path('config/javascript.php'));
	
	File::moveDirectory(public_path('vendor/adminlte/plugins/jquery/'), public_path('vendor/adminlte/plugins/jqueryNew/'));
	File::moveDirectory(public_path('vendor/adminlte/plugins/jqueryNew/'), public_path('vendor/adminlte/plugins/jQuery/'));
	
	File::move(public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.0.min.js'), public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.0.new.js'));
	File::move(public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.0.new.js'), public_path('vendor/adminlte/plugins/jQuery/jquery-2.2.0.min.js'));
	
	File::move(public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.3.min.js'), public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.3.new.js'));
	File::move(public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.3.new.js'), public_path('vendor/adminlte/plugins/jQuery/jquery-2.2.3.min.js'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// cities
	DB::table('cities')
		->where('country_code', '=', 'RU')
		->where('name', 'Ak”yar')
		->update(['name' => 'Akyar',]);
	
	// home_sections
	DB::table('home_sections')->where('method', 'getLocations')->update(['name' => 'Locations & SVG Map']);
	
	// messages
	if (Schema::hasTable('messages')) {
		if (Schema::hasColumn('messages', 'name') && !Schema::hasColumn('messages', 'to_name')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->renameColumn('name', 'to_name');
			});
		}
		if (Schema::hasColumn('messages', 'email') && !Schema::hasColumn('messages', 'to_email')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->renameColumn('email', 'to_email');
			});
		}
		if (Schema::hasColumn('messages', 'phone') && !Schema::hasColumn('messages', 'to_phone')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->renameColumn('phone', 'to_phone');
			});
		}
		
		if (!Schema::hasColumn('messages', 'parent_id') && Schema::hasColumn('messages', 'post_id')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->integer('parent_id')->unsigned()->nullable()->default(0)->after('post_id');
			});
		}
		if (!Schema::hasColumn('messages', 'from_user_id') && Schema::hasColumn('messages', 'parent_id')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->integer('from_user_id')->unsigned()->nullable()->default(0)->after('parent_id');
			});
		}
		if (!Schema::hasColumn('messages', 'to_user_id') && Schema::hasColumn('messages', 'from_user_id')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->integer('to_user_id')->unsigned()->nullable()->default(0)->after('from_user_id');
			});
		}
		if (!Schema::hasColumn('messages', 'from_name') && Schema::hasColumn('messages', 'to_user_id')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->string('from_name', 200)->nullable()->after('to_user_id');
			});
		}
		if (!Schema::hasColumn('messages', 'from_email') && Schema::hasColumn('messages', 'from_name')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->string('from_email', 100)->nullable()->after('from_name');
			});
		}
		if (!Schema::hasColumn('messages', 'from_phone') && Schema::hasColumn('messages', 'from_email')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->string('from_phone', 50)->nullable()->after('from_email');
			});
		}
		if (!Schema::hasColumn('messages', 'subject') && Schema::hasColumn('messages', 'to_phone')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->text('subject')->nullable()->after('to_phone');
			});
		}
		if (!Schema::hasColumn('messages', 'deleted_by') && Schema::hasColumn('messages', 'is_read')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->integer('deleted_by')->unsigned()->nullable()->after('is_read');
			});
		}
		
		if (Schema::hasColumn('messages', 'is_read')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->tinyInteger('is_read')->unsigned()->nullable()->default(0)->change();
			});
		}
		if (Schema::hasColumn('messages', 'post_id')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->integer('post_id')->unsigned()->nullable()->default(0)->change();
			});
		}
		if (Schema::hasColumn('messages', 'message')) {
			Schema::table('messages', function (Blueprint $table) {
				$table->text('message')->nullable()->change();
			});
		}
		
		DBIndex::dropIndexIfExists('messages', 'parent_id');
		DBIndex::createIndexIfNotExists('messages', 'parent_id');
		
		DBIndex::dropIndexIfExists('messages', 'from_user_id');
		DBIndex::createIndexIfNotExists('messages', 'from_user_id');
		
		DBIndex::dropIndexIfExists('messages', 'to_user_id');
		DBIndex::createIndexIfNotExists('messages', 'to_user_id');
		
		DBIndex::dropIndexIfExists('messages', 'deleted_by');
		DBIndex::createIndexIfNotExists('messages', 'deleted_by');
	}
	
	// settings
	DB::table('settings')->truncate();
	DB::table('settings')->insert([
		[
			'id'          => 1,
			'key'         => 'app',
			'name'        => 'Application',
			'value'       => null,
			'description' => 'Application Setup',
			'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Brand Info</h3>"},{"name":"purchase_code","label":"Purchase Code","type":"text","hint":"Get your purchase code <a href=\":purchaseCodeFindingUrl\" target=\"_blank\">here</a>."},{"name":"name","label":"App Name","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"slogan","label":"App Slogan","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"logo","label":"App Logo","type":"image","upload":"true","disk":"uploads","default":"app/default/logo.png","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"favicon","label":"Favicon","type":"image","upload":"true","disk":"uploads","default":"app/default/ico/favicon.png","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_clear_1","type":"custom_html","value":"<div style=\"clear: both;\"></div>"},{"name":"email","label":"Email","type":"email","hint":"The email address that all emails from the contact form will go to.","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"phone_number","label":"Phone number","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>Date Format</h3>"},{"name":"default_date_format","label":"Date Format","type":"text","hint":"The implementation makes a call to <a href=\"https://www.php.net/manual/en/function.strftime.php\" target=\"_blank\">strftime</a> using the current instance timestamp.","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"default_datetime_format","label":"Date Time Format","type":"text","hint":"The implementation makes a call to <a href=\"https://www.php.net/manual/en/function.strftime.php\" target=\"_blank\">strftime</a> using the current instance timestamp.","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"default_timezone","label":"Default Timezone","type":"select2","attribute":"time_zone_id","model":"\\App\\Models\\TimeZone","hint":"NOTE: This option is used in the Admin panel","wrapperAttributes":{"class":"form-group col-md-6"}}]',
			'parent_id'   => 0,
			'lft'         => 2,
			'rgt'         => 3,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => null,
		],
	]);
	
	DB::table('settings')->insert([
		'id'          => 2,
		'key'         => 'style',
		'name'        => 'Style',
		'value'       => null,
		'description' => 'Style Customization',
		'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Front-End</h3>"},{"name":"app_skin","label":"Front Skin","type":"select2_from_array","options":{"skin-default":"Default","skin-blue":"Blue","skin-yellow":"Yellow","skin-green":"Green","skin-red":"Red"}},{"name":"separator_2","type":"custom_html","value":"<h4>Customize the Front Style</h4>"},{"name":"separator_2_1","type":"custom_html","value":"<h5><strong>Global</strong></h5>"},{"name":"body_background_color","label":"Body Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#FFFFFF"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"body_text_color","label":"Body Text Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#292B2C"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"body_background_image","label":"Body Background Image","type":"image","upload":"true","disk":"uploads","default":"","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_clear_1","type":"custom_html","value":"<div style=\"clear: both;\"></div>"},{"name":"body_background_image_fixed","label":"Body Background Image Fixed","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6","style":"margin-top: 20px;"}},{"name":"page_width","label":"Page Width","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_clear_2","type":"custom_html","value":"<div style=\"clear: both;\"></div>"},{"name":"title_color","label":"Titles Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#292B2C"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"progress_background_color","label":"Progress Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":""},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"link_color","label":"Links Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#4682B4"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"link_color_hover","label":"Links Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#FF8C00"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2_2","type":"custom_html","value":"<h5><strong>Header</strong></h5>"},{"name":"header_sticky","label":"Header Sticky","type":"checkbox"},{"name":"header_height","label":"Header Height","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"header_background_color","label":"Header Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#F8F8F8"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"header_bottom_border_width","label":"Header Bottom Border Width","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"header_bottom_border_color","label":"Header Bottom Border Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#E8E8E8"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"header_link_color","label":"Header Links Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#333"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"header_link_color_hover","label":"Header Links Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#000"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2_3","type":"custom_html","value":"<h5><strong>Footer</strong></h5>"},{"name":"footer_background_color","label":"Footer Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#F5F5F5"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"footer_text_color","label":"Footer Text Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#333"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"footer_title_color","label":"Footer Titles Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#000"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_clear_2","type":"custom_html","value":"<div style=\"clear: both;\"></div>"},{"name":"footer_link_color","label":"Footer Links Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#333"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"footer_link_color_hover","label":"Footer Links Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#333"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"payment_icon_top_border_width","label":"Payment Methods Icons Top Border Width","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"payment_icon_top_border_color","label":"Payment Methods Icons Top Border Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#DDD"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"payment_icon_bottom_border_width","label":"Payment Methods Icons Bottom Border Width","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"payment_icon_bottom_border_color","label":"Payment Methods Icons Bottom Border Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#DDD"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2_4","type":"custom_html","value":"<h5><strong>Button \'Add Listing\'</strong></h5>"},{"name":"btn_post_bg_top_color","label":"Gradient Background Top Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#ffeb43"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"btn_post_bg_bottom_color","label":"Gradient Background Bottom Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#fcde11"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"btn_post_border_color","label":"Button Border Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#f6d80f"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"btn_post_text_color","label":"Button Text Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#292b2c"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"btn_post_bg_top_color_hover","label":"Gradient Background Top Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#fff860"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"btn_post_bg_bottom_color_hover","label":"Gradient Background Bottom Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#ffeb43"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"btn_post_border_color_hover","label":"Button Border Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#fcde11"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"btn_post_text_color_hover","label":"Button Text Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#1b1d1e"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_3","type":"custom_html","value":"<h4>Raw CSS (Optional)</h4>"},{"name":"separator_3_1","type":"custom_html","value":"You can also add raw CSS to customize your website style by using the field below. <br>If you want to add a large CSS code, you have to use the /public/css/custom.css file."},{"name":"custom_css","label":"Custom CSS","type":"textarea","attributes":{"rows":"5"},"hint":"Please <strong>do not</strong> include the &lt;style&gt; tags."},{"name":"separator_4","type":"custom_html","value":"<h3>Admin panel</h3>"},{"name":"admin_skin","label":"Admin Skin","type":"select2_from_array","options":{"skin-black":"Black","skin-blue":"Blue","skin-purple":"Purple","skin-red":"Red","skin-yellow":"Yellow","skin-green":"Green","skin-blue-light":"Blue light","skin-black-light":"Black light","skin-purple-light":"Purple light","skin-green-light":"Green light","skin-red-light":"Red light","skin-yellow-light":"Yellow light"}}]',
		'parent_id'   => 0,
		'lft'         => 4,
		'rgt'         => 5,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 3,
		'key'         => 'listing',
		'name'        => 'Listing & Search',
		'value'       => null,
		'description' => 'Listing & Search Options',
		'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Displaying</h3>"},{"name":"display_mode","label":"Listing Page Display Mode","type":"select2_from_array","options":{".grid-view":"Grid",".list-view":"List",".compact-view":"Compact"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"grid_view_cols","label":"Grid View Columns","type":"select2_from_array","options":{"4":"4","3":"3","2":"2"},"wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"items_per_page","label":"Items per page","type":"text","hint":"Number of items per page (> 4 and < 40)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"left_sidebar","label":"Listing Page Left Sidebar","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6","style":"margin-top: 20px;"}},{"name":"separator_2","type":"custom_html","value":"<h3>Distance</h3>"},{"name":"search_distance_max","label":"Max Search Distance","type":"select2_from_array","options":{"1000":"1000","900":"900","800":"800","700":"700","600":"600","500":"500","400":"400","300":"300","200":"200","100":"100","50":"50","0":"0"},"hint":"Max search radius distance (in km or miles)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"search_distance_default","label":"Default Search Distance","type":"select2_from_array","options":{"200":"200","100":"100","50":"50","25":"25","20":"20","10":"10","0":"0"},"hint":"Default search radius distance (in km or miles)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"search_distance_interval","label":"Distance Interval","type":"select2_from_array","options":{"250":"250","200":"200","100":"100","50":"50","25":"25","20":"20","10":"10","5":"5"},"hint":"The interval between filter distances (shown on the search results page)","wrapperAttributes":{"class":"form-group col-md-6"}}]',
		'parent_id'   => 0,
		'lft'         => 6,
		'rgt'         => 7,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 4,
		'key'         => 'single',
		'name'        => 'Ads Single Page',
		'value'       => null,
		'description' => 'Ads Single Page Options',
		'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Publication</h3>"},{"name":"pictures_limit","label":"Pictures Limit","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"tags_limit","label":"Tags Limit","type":"text","hint":"NOTE: The \'tags\' field in the \'posts\' table is a varchar 255","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"guests_can_post_ads","label":"Allow Guests to post Ads","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"posts_review_activation","label":"Allow Ads to be reviewed by Admins","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"guests_can_contact_seller","label":"Allow Guests to contact Sellers","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>Edition</h3>"},{"name":"simditor_wysiwyg","label":"Allow the Simditor WYSIWYG Editor","type":"checkbox"},{"name":"ckeditor_wysiwyg","label":"Allow the CKEditor WYSIWYG Editor","type":"checkbox","hint":"For commercial use: http://ckeditor.com/pricing. NOTE: You need to disable the \'Simditor WYSIWYG Editor\'"},{"name":"separator_3","type":"custom_html","value":"<h3>External Services</h3>"},{"name":"show_post_on_googlemap","label":"Show Ads on Google Maps (Single Page Only)","type":"checkbox","hint":"You have to enter your Google Maps API key at: <br>Setup -> General Settings -> Others.","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"activation_facebook_comments","label":"Allow Facebook Comments (Single Page Only)","type":"checkbox","hint":"You have to configure the Login with Facebook at: <br>Setup -> General Settings -> Social Login.","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_4","type":"custom_html","value":"<hr>"}]',
		'parent_id'   => 0,
		'lft'         => 8,
		'rgt'         => 9,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 5,
		'key'         => 'mail',
		'name'        => 'Mail',
		'value'       => null,
		'description' => 'Mail Sending Configuration',
		'field'       => '[{"name":"driver","label":"Mail Driver","type":"select2_from_array","options":{"smtp":"SMTP","mailgun":"Mailgun","mandrill":"Mandrill","ses":"Amazon SES","sparkpost":"Sparkpost","mail":"PHP Mail","sendmail":"Sendmail"}},{"name":"separator_1","type":"custom_html","value":"<h3>SMTP</h3>"},{"name":"separator_1_1","type":"custom_html","value":"Required for drivers: SMTP, Mailgun, Mandrill, Sparkpost"},{"name":"host","label":"Mail Host","type":"text","hint":"SMTP Host","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"port","label":"Mail Port","type":"text","hint":"SMTP Port (e.g. 25, 587, ...)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"username","label":"Mail Username","type":"text","hint":"SMTP Username","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"password","label":"Mail Password","type":"text","hint":"SMTP Password","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"encryption","label":"Mail Encryption","type":"text","hint":"SMTP Encryption (e.g. tls, ssl, starttls)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>Mailgun</h3>"},{"name":"mailgun_domain","label":"Mailgun Domain","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"mailgun_secret","label":"Mailgun Secret","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_3","type":"custom_html","value":"<h3>Mandrill</h3>"},{"name":"mandrill_secret","label":"Mandrill Secret","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_4","type":"custom_html","value":"<h3>Amazon SES</h3>"},{"name":"ses_key","label":"SES Key","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"ses_secret","label":"SES Secret","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"ses_region","label":"SES Region","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_5","type":"custom_html","value":"<h3>Sparkpost</h3>"},{"name":"sparkpost_secret","label":"Sparkpost Secret","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_6","type":"custom_html","value":"<hr>"},{"name":"separator_7","type":"custom_html","value":"<h3>Notification Types</h3>"},{"name":"email_sender","label":"Email Sender","type":"email","hint":"Transactional Email Sender. Example: noreply@yoursite.com","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"email_verification","label":"email_verification_label","type":"checkbox","hint":"email_verification_hint"},{"name":"admin_notification","label":"settings_mail_admin_notification_label","type":"checkbox","hint":"settings_mail_admin_notification_hint"},{"name":"payment_notification","label":"settings_mail_payment_notification_label","type":"checkbox","hint":"settings_mail_payment_notification_hint"}]',
		'parent_id'   => 0,
		'lft'         => 10,
		'rgt'         => 11,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 6,
		'key'         => 'sms',
		'name'        => 'SMS',
		'value'       => null,
		'description' => 'SMS Sending Configuration',
		'field'       => '[{"name":"driver","label":"SMS Driver","type":"select2_from_array","options":{"nexmo":"Nexmo","twilio":"Twilio"}},{"name":"separator_1","type":"custom_html","value":"<h3>Nexmo</h3>"},{"name":"separator_1_1","type":"custom_html","value":"Get a Nexmo Account <a href=\"https://www.nexmo.com/\" target=\"_blank\">here</a>."},{"name":"nexmo_key","label":"Nexmo Key","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"nexmo_secret","label":"Nexmo Secret","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"nexmo_from","label":"Nexmo From","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>Twilio</h3>"},{"name":"separator_2_1","type":"custom_html","value":"Get a Twilio Account <a href=\"https://www.twilio.com/\" target=\"_blank\">here</a>."},{"name":"twilio_account_sid","label":"Twilio Account SID","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"twilio_auth_token","label":"Twilio Auth Token","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"twilio_from","label":"Twilio From","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_3","type":"custom_html","value":"<hr>"},{"name":"separator_4","type":"custom_html","value":"<h3>Notification Types</h3>"},{"name":"phone_verification","label":"Enable Phone Verification","type":"checkbox","hint":"By enabling this option you have to add this entry: <strong>DISABLE_PHONE=false</strong> in the /.env file."},{"name":"message_activation","label":"Enable SMS Message","type":"checkbox","hint":"Send a SMS in addition for each message between users. NOTE: You will have a lot to spend on the SMS sending credit."}]',
		'parent_id'   => 0,
		'lft'         => 12,
		'rgt'         => 13,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 7,
		'key'         => 'seo',
		'name'        => 'SEO',
		'value'       => null,
		'description' => 'SEO Tools',
		'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Verification Tools</h3>"},{"name":"google_site_verification","label":"Google site verification content","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"alexa_verify_id","label":"Alexa site verification content","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"msvalidate","label":"Bing site verification content","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"twitter_username","label":"Twitter Username","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>Indexing (On Search Engines)</h3>"},{"name":"no_index_categories","label":"No Index Categories Pages","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"no_index_tags","label":"No Index Tags Pages","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"no_index_cities","label":"No Index Cities Pages","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"no_index_users","label":"No Index Users Pages","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"no_index_all","label":"No Index All Pages","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_3","type":"custom_html","value":"<h3>Posts Permalink Settings</h3>"},{"name":"posts_permalink","label":"Posts Permalink","type":"select2_from_array","options":{"{slug}-{id}":"{slug}-{id}","{slug}/{id}":"{slug}/{id}","{slug}_{id}":"{slug}_{id}","{id}-{slug}":"{id}-{slug}","{id}/{slug}":"{id}/{slug}","{id}_{slug}":"{id}_{slug}","{id}":"{id}"},"hint":"The word {id} will be replaced by the Post ID, and {slug} by the Post title\'s slug.<br>e.g. http://www.domain.com/{slug}/{id}","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"posts_permalink_ext","label":"Posts Permalink Extension","type":"select2_from_array","options":{"":"&nbsp;",".html":".html",".htm":".htm",".php":".php",".aspx":".aspx"},"hint":"You can add an extension for the Posts Permalink (Optional).<br>e.g. http://www.domain.com/{slug}/{id}.html","wrapperAttributes":{"class":"form-group col-md-6"}}]',
		'parent_id'   => 0,
		'lft'         => 14,
		'rgt'         => 15,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 8,
		'key'         => 'upload',
		'name'        => 'Upload',
		'value'       => null,
		'description' => 'Upload Settings',
		'field'       => '[{"name":"image_types","label":"Upload Image Types","type":"text","hint":"Upload image types (ex: jpg,jpeg,gif,png,...)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"file_types","label":"Upload File Types","type":"text","hint":"Upload file types (ex: pdf,doc,docx,odt,...)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"max_file_size","label":"Upload Max File Size","type":"text","hint":"Upload Max File Size (in KB)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_1","type":"custom_html","value":"<hr>"}]',
		'parent_id'   => 0,
		'lft'         => 16,
		'rgt'         => 17,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 9,
		'key'         => 'geo_location',
		'name'        => 'Geo Location',
		'value'       => null,
		'description' => 'Geo Location Configuration',
		'field'       => '[{"name":"geolocation_activation","label":"Enable Geolocation","type":"checkbox","hint":"Before enabling this option you need to download the Maxmind database by following the documentation <a href=\\\"http://support.bedigit.com/help-center/articles/14/enable-the-geo-location\\\" target=\\\"_blank\\\">here</a>.","wrapperAttributes":{"class":"form-group col-md-6","style":"margin-top: 20px;"}},{"name":"default_country_code","label":"Default Country","type":"select2","attribute":"name","model":"\\\\App\\\\Models\\\\Country","allows_null":"true","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_clear_1","type":"custom_html","value":"<div style=\\\"clear: both;\\\"></div>"},{"name":"country_flag_activation","label":"Show country flag on top","type":"checkbox","hint":"<br>","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"local_currency_packages_activation","label":"Allow users to pay the Packages in their country currency","type":"checkbox","hint":"You have to create a list of <a href=\\\"{adminUrl}/package\\\" target=\\\"_blank\\\">Packages</a> per currency (using currencies of activated countries) to allow users to pay the Packages in their local currency.<br>NOTE: By unchecking this field all the lists of Packages (without currency matching) will be shown during the payment process.","wrapperAttributes":{"class":"form-group col-md-6"}}]',
		'parent_id'   => 0,
		'lft'         => 18,
		'rgt'         => 19,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 10,
		'key'         => 'security',
		'name'        => 'Security',
		'value'       => null,
		'description' => 'Security Options',
		'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Login</h3>"},{"name":"login_open_in_modal","label":"Open In Modal","type":"checkbox","hint":"Open the top login link into Modal"},{"name":"login_max_attempts","label":"Max Attempts","type":"select2_from_array","options":{"30":"30","20":"20","10":"10","5":"5","4":"4","3":"3","2":"2","1":"1"},"hint":"The maximum number of attempts to allow","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"login_decay_minutes","label":"Decay Minutes","type":"select2_from_array","options":{"1440":"1440","720":"720","60":"60","30":"30","20":"20","15":"15","10":"10","5":"5","4":"4","3":"3","2":"2","1":"1"},"hint":"The number of minutes to throttle for","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>reCAPTCHA</h3>"},{"name":"recaptcha_activation","label":"Enable reCAPTCHA","type":"checkbox","hint":"Get reCAPTCHA site keys <a href=\"https://www.google.com/recaptcha/\" target=\"_blank\">here</a>."},{"name":"recaptcha_public_key","label":"reCAPTCHA Public Key","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"recaptcha_private_key","label":"reCAPTCHA Private Key","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}}]',
		'parent_id'   => 0,
		'lft'         => 20,
		'rgt'         => 21,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 11,
		'key'         => 'social_auth',
		'name'        => 'Social Login',
		'value'       => null,
		'description' => 'Social Network Login',
		'field'       => '[{"name":"social_auth_enabled","label":"Enable Social Login","type":"checkbox","hint":"Allow users to connect via social networks"},{"name":"separator_1","type":"custom_html","value":"<h3>Facebook</h3>"},{"name":"separator_1_1","type":"custom_html","value":"Create a Facebook App <a href=\"https://developers.facebook.com/\" target=\"_blank\">here</a>. The \"OAuth redirect URI\" is: (http:// or https://) yoursite.com<strong>/auth/facebook/callback</strong> or www.yoursite.com<strong>/auth/facebook/callback</strong>"},{"name":"facebook_client_id","label":"Facebook Client ID","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"facebook_client_secret","label":"Facebook Client Secret","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>Google+</h3>"},{"name":"separator_2_1","type":"custom_html","value":"Create a Google+ App <a href=\"https://console.developers.google.com/\" target=\"_blank\">here</a>. The \"Authorized Redirect URI\" is: (http:// or https://) yoursite.com<strong>/auth/google/callback</strong> or www.yoursite.com<strong>/auth/google/callback</strong>"},{"name":"google_client_id","label":"Google Client ID","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"google_client_secret","label":"Google Client Secret","type":"text","wrapperAttributes":{"class":"form-group col-md-6"}}]',
		'parent_id'   => 0,
		'lft'         => 22,
		'rgt'         => 23,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 12,
		'key'         => 'social_link',
		'name'        => 'Social Network',
		'value'       => null,
		'description' => 'Social Network Profiles',
		'field'       => '[{"name":"facebook_page_url","label":"Facebook Page URL","type":"text"},{"name":"twitter_url","label":"Twitter URL","type":"text"},{"name":"tiktok_url","label":"Tiktok URL","type":"text"},{"name":"linkedin_url","label":"LinkedIn URL","type":"text"},{"name":"pinterest_url","label":"Pinterest URL","type":"text"}]',
		'parent_id'   => 0,
		'lft'         => 24,
		'rgt'         => 25,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 13,
		'key'         => 'other',
		'name'        => 'Others',
		'value'       => null,
		'description' => 'Other Options',
		'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Tips Info</h3>"},{"name":"show_tips_messages","label":"Show Tips Notification Messages","type":"checkbox","hint":"e.g. SITENAME is also available in your country: COUNTRY. Starting good deals here now!<br>Login for faster access to the best deals. Click here if you don\'t have an account."},{"name":"separator_2","type":"custom_html","value":"<h3>Google Maps</h3>"},{"name":"googlemaps_key","label":"Google Maps Key","type":"text"},{"name":"separator_3","type":"custom_html","value":"<h3>Number Format</h3>"},{"name":"decimals_superscript","label":"Decimals Superscript","type":"checkbox"},{"name":"separator_4","type":"custom_html","value":"<h3>Optimization</h3>"},{"name":"cookie_expiration","label":"Cookie Expiration Time","type":"text","hint":"Cookie Expiration Time (in secondes)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"cache_expiration","label":"Cache Expiration Time","type":"text","hint":"Cache Expiration Time (in minutes)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"minify_html_activation","label":"Enable HTML Minify","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"http_cache_activation","label":"Enable HTTP Cache","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_5","type":"custom_html","value":"<h3>JavaScript (in the &lt;head&gt; section)</h3>"},{"name":"js_code","label":"JavaScript Code","type":"textarea","attributes":{"rows":"10"},"hint":"Paste your JavaScript code here to put it in the &lt;head&gt; section of HTML pages."}]',
		'parent_id'   => 0,
		'lft'         => 26,
		'rgt'         => 27,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 14,
		'key'         => 'cron',
		'name'        => 'Cron',
		'value'       => null,
		'description' => 'Cron Job',
		'field'       => '[{"name":"separator_1","type":"custom_html","value":"<h3>Cron</h3>"},{"name":"separator_1_1","type":"custom_html","value":"You need to add \'/usr/bin/php -q /path/to/your/website/artisan ads:clean\' in your Cron Job tab. Click <a href=\\\"http://support.bedigit.com/help-center/articles/19/configuring-the-cron-job\\\" target=\\\"_blank\\\">here</a> for more information."},{"name":"unactivated_listings_expiration","label":"Unactivated Ads Expiration","type":"text","hint":"In days (Delete the unactivated ads after this expiration)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"activated_listings_expiration","label":"Activated Ads Expiration","type":"text","hint":"In days (Archive the activated ads after this expiration)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"archived_listings_expiration","label":"Archived Ads Expiration","type":"text","hint":"In days (Delete the archived ads after this expiration)","wrapperAttributes":{"class":"form-group col-md-6"}},{"name":"separator_2","type":"custom_html","value":"<h3>Test</h3>"},{"name":"separator_2_1","type":"custom_html","value":"You can run manually the Cron Job command by clicking the button below. <br><strong>CAUTION:</strong><br>- All the expirated paid ads (also called: premium or featured ads) will become regular ads (also called: normal or free ads).<br>&nbsp;&nbsp;You have to setup the premium ads expiration duration from: Setup -> Packages.<br>- All expirated active regular ads will be archived. <br>- All expirated inactive regular ads will be deleted.<br>- All expirated archived regular ads will be deleted."},{"name":"separator_2_2","type":"custom_html","value":"<a href=\\\"{adminUrl}/test_cron\\\" class=\\\"btn btn-primary\\\"><i class=\\\"fa fa-play-circle-o\\\"></i> Run Manually</a>"}]',
		'parent_id'   => 0,
		'lft'         => 28,
		'rgt'         => 29,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	DB::table('settings')->insert([
		'id'          => 15,
		'key'         => 'footer',
		'name'        => 'Footer',
		'value'       => null,
		'description' => 'Pages Footer',
		'field'       => '[{"name":"show_payment_plugins_logos","label":"Show Payment Plugins Logos","type":"checkbox"},{"name":"show_powered_by","label":"Show Powered by Info","type":"checkbox"},{"name":"powered_by_info","label":"Powered by","type":"text"},{"name":"tracking_code","label":"Tracking Code","type":"textarea","attributes":{"rows":"15"},"hint":"Paste your Google Analytics (or other) tracking code here. This will be added into the footer."}]',
		'parent_id'   => 0,
		'lft'         => 30,
		'rgt'         => 31,
		'depth'       => 1,
		'active'      => 1,
		'created_at'  => null,
		'updated_at'  => null,
	]);
	
	// time_zones
	if (Schema::hasTable('time_zones')) {
		DB::table('time_zones')->truncate();
		
		$allData = [
			['id' => 1, 'country_code' => 'CI', 'time_zone_id' => 'Africa/Abidjan', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 2, 'country_code' => 'GH', 'time_zone_id' => 'Africa/Accra', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 3, 'country_code' => 'ET', 'time_zone_id' => 'Africa/Addis_Ababa', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 4, 'country_code' => 'DZ', 'time_zone_id' => 'Africa/Algiers', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 5, 'country_code' => 'ER', 'time_zone_id' => 'Africa/Asmara', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 6, 'country_code' => 'ML', 'time_zone_id' => 'Africa/Bamako', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 7, 'country_code' => 'CF', 'time_zone_id' => 'Africa/Bangui', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 8, 'country_code' => 'GM', 'time_zone_id' => 'Africa/Banjul', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 9, 'country_code' => 'GW', 'time_zone_id' => 'Africa/Bissau', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 10, 'country_code' => 'MW', 'time_zone_id' => 'Africa/Blantyre', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 11, 'country_code' => 'CG', 'time_zone_id' => 'Africa/Brazzaville', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 12, 'country_code' => 'BI', 'time_zone_id' => 'Africa/Bujumbura', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 13, 'country_code' => 'EG', 'time_zone_id' => 'Africa/Cairo', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 14, 'country_code' => 'MA', 'time_zone_id' => 'Africa/Casablanca', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 15, 'country_code' => 'ES', 'time_zone_id' => 'Africa/Ceuta', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 16, 'country_code' => 'GN', 'time_zone_id' => 'Africa/Conakry', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 17, 'country_code' => 'SN', 'time_zone_id' => 'Africa/Dakar', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 18, 'country_code' => 'TZ', 'time_zone_id' => 'Africa/Dar_es_Salaam', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 19, 'country_code' => 'DJ', 'time_zone_id' => 'Africa/Djibouti', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 20, 'country_code' => 'CM', 'time_zone_id' => 'Africa/Douala', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 21, 'country_code' => 'EH', 'time_zone_id' => 'Africa/El_Aaiun', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 22, 'country_code' => 'SL', 'time_zone_id' => 'Africa/Freetown', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 23, 'country_code' => 'BW', 'time_zone_id' => 'Africa/Gaborone', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 24, 'country_code' => 'ZW', 'time_zone_id' => 'Africa/Harare', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 25, 'country_code' => 'ZA', 'time_zone_id' => 'Africa/Johannesburg', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 26, 'country_code' => 'SS', 'time_zone_id' => 'Africa/Juba', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 27, 'country_code' => 'UG', 'time_zone_id' => 'Africa/Kampala', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 28, 'country_code' => 'SD', 'time_zone_id' => 'Africa/Khartoum', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 29, 'country_code' => 'RW', 'time_zone_id' => 'Africa/Kigali', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 30, 'country_code' => 'CD', 'time_zone_id' => 'Africa/Kinshasa', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 31, 'country_code' => 'NG', 'time_zone_id' => 'Africa/Lagos', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 32, 'country_code' => 'GA', 'time_zone_id' => 'Africa/Libreville', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 33, 'country_code' => 'TG', 'time_zone_id' => 'Africa/Lome', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 34, 'country_code' => 'AO', 'time_zone_id' => 'Africa/Luanda', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 35, 'country_code' => 'CD', 'time_zone_id' => 'Africa/Lubumbashi', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 36, 'country_code' => 'ZM', 'time_zone_id' => 'Africa/Lusaka', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 37, 'country_code' => 'GQ', 'time_zone_id' => 'Africa/Malabo', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 38, 'country_code' => 'MZ', 'time_zone_id' => 'Africa/Maputo', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 39, 'country_code' => 'LS', 'time_zone_id' => 'Africa/Maseru', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 40, 'country_code' => 'SZ', 'time_zone_id' => 'Africa/Mbabane', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 41, 'country_code' => 'SO', 'time_zone_id' => 'Africa/Mogadishu', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 42, 'country_code' => 'LR', 'time_zone_id' => 'Africa/Monrovia', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 43, 'country_code' => 'KE', 'time_zone_id' => 'Africa/Nairobi', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 44, 'country_code' => 'TD', 'time_zone_id' => 'Africa/Ndjamena', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 45, 'country_code' => 'NE', 'time_zone_id' => 'Africa/Niamey', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 46, 'country_code' => 'MR', 'time_zone_id' => 'Africa/Nouakchott', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 47, 'country_code' => 'BF', 'time_zone_id' => 'Africa/Ouagadougou', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 48, 'country_code' => 'BJ', 'time_zone_id' => 'Africa/Porto-Novo', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 49, 'country_code' => 'ST', 'time_zone_id' => 'Africa/Sao_Tome', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 50, 'country_code' => 'LY', 'time_zone_id' => 'Africa/Tripoli', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 51, 'country_code' => 'TN', 'time_zone_id' => 'Africa/Tunis', 'gmt' => 1, 'dst' => 1, 'raw' => 1],
			['id' => 52, 'country_code' => 'NA', 'time_zone_id' => 'Africa/Windhoek', 'gmt' => 2, 'dst' => 1, 'raw' => 1],
			['id' => 53, 'country_code' => 'US', 'time_zone_id' => 'America/Adak', 'gmt' => -10, 'dst' => -9, 'raw' => -10],
			['id' => 54, 'country_code' => 'US', 'time_zone_id' => 'America/Anchorage', 'gmt' => -9, 'dst' => -8, 'raw' => -9],
			['id' => 55, 'country_code' => 'AI', 'time_zone_id' => 'America/Anguilla', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 56, 'country_code' => 'AG', 'time_zone_id' => 'America/Antigua', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 57, 'country_code' => 'BR', 'time_zone_id' => 'America/Araguaina', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 58, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Buenos_Aires', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 59, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Catamarca', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 60, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Cordoba', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 61, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Jujuy', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 62, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/La_Rioja', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 63, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Mendoza', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 64, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Rio_Gallegos', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 65, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Salta', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 66, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/San_Juan', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 67, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/San_Luis', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 68, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Tucuman', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 69, 'country_code' => 'AR', 'time_zone_id' => 'America/Argentina/Ushuaia', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 70, 'country_code' => 'AW', 'time_zone_id' => 'America/Aruba', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 71, 'country_code' => 'PY', 'time_zone_id' => 'America/Asuncion', 'gmt' => -3, 'dst' => -4, 'raw' => -4],
			['id' => 72, 'country_code' => 'CA', 'time_zone_id' => 'America/Atikokan', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 73, 'country_code' => 'BR', 'time_zone_id' => 'America/Bahia', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 74, 'country_code' => 'MX', 'time_zone_id' => 'America/Bahia_Banderas', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 75, 'country_code' => 'BB', 'time_zone_id' => 'America/Barbados', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 76, 'country_code' => 'BR', 'time_zone_id' => 'America/Belem', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 77, 'country_code' => 'BZ', 'time_zone_id' => 'America/Belize', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 78, 'country_code' => 'CA', 'time_zone_id' => 'America/Blanc-Sablon', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 79, 'country_code' => 'BR', 'time_zone_id' => 'America/Boa_Vista', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 80, 'country_code' => 'CO', 'time_zone_id' => 'America/Bogota', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 81, 'country_code' => 'US', 'time_zone_id' => 'America/Boise', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 82, 'country_code' => 'CA', 'time_zone_id' => 'America/Cambridge_Bay', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 83, 'country_code' => 'BR', 'time_zone_id' => 'America/Campo_Grande', 'gmt' => -3, 'dst' => -4, 'raw' => -4],
			['id' => 84, 'country_code' => 'MX', 'time_zone_id' => 'America/Cancun', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 85, 'country_code' => 'VE', 'time_zone_id' => 'America/Caracas', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 86, 'country_code' => 'GF', 'time_zone_id' => 'America/Cayenne', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 87, 'country_code' => 'KY', 'time_zone_id' => 'America/Cayman', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 88, 'country_code' => 'US', 'time_zone_id' => 'America/Chicago', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 89, 'country_code' => 'MX', 'time_zone_id' => 'America/Chihuahua', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 90, 'country_code' => 'CR', 'time_zone_id' => 'America/Costa_Rica', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 91, 'country_code' => 'CA', 'time_zone_id' => 'America/Creston', 'gmt' => -7, 'dst' => -7, 'raw' => -7],
			['id' => 92, 'country_code' => 'BR', 'time_zone_id' => 'America/Cuiaba', 'gmt' => -3, 'dst' => -4, 'raw' => -4],
			['id' => 93, 'country_code' => 'CW', 'time_zone_id' => 'America/Curacao', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 94, 'country_code' => 'GL', 'time_zone_id' => 'America/Danmarkshavn', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 95, 'country_code' => 'CA', 'time_zone_id' => 'America/Dawson', 'gmt' => -8, 'dst' => -7, 'raw' => -8],
			['id' => 96, 'country_code' => 'CA', 'time_zone_id' => 'America/Dawson_Creek', 'gmt' => -7, 'dst' => -7, 'raw' => -7],
			['id' => 97, 'country_code' => 'US', 'time_zone_id' => 'America/Denver', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 98, 'country_code' => 'US', 'time_zone_id' => 'America/Detroit', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 99, 'country_code' => 'DM', 'time_zone_id' => 'America/Dominica', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 100, 'country_code' => 'CA', 'time_zone_id' => 'America/Edmonton', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 101, 'country_code' => 'BR', 'time_zone_id' => 'America/Eirunepe', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 102, 'country_code' => 'SV', 'time_zone_id' => 'America/El_Salvador', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 103, 'country_code' => 'CA', 'time_zone_id' => 'America/Fort_Nelson', 'gmt' => -7, 'dst' => -7, 'raw' => -7],
			['id' => 104, 'country_code' => 'BR', 'time_zone_id' => 'America/Fortaleza', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 105, 'country_code' => 'CA', 'time_zone_id' => 'America/Glace_Bay', 'gmt' => -4, 'dst' => -3, 'raw' => -4],
			['id' => 106, 'country_code' => 'GL', 'time_zone_id' => 'America/Godthab', 'gmt' => -3, 'dst' => -2, 'raw' => -3],
			['id' => 107, 'country_code' => 'CA', 'time_zone_id' => 'America/Goose_Bay', 'gmt' => -4, 'dst' => -3, 'raw' => -4],
			['id' => 108, 'country_code' => 'TC', 'time_zone_id' => 'America/Grand_Turk', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 109, 'country_code' => 'GD', 'time_zone_id' => 'America/Grenada', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 110, 'country_code' => 'GP', 'time_zone_id' => 'America/Guadeloupe', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 111, 'country_code' => 'GT', 'time_zone_id' => 'America/Guatemala', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 112, 'country_code' => 'EC', 'time_zone_id' => 'America/Guayaquil', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 113, 'country_code' => 'GY', 'time_zone_id' => 'America/Guyana', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 114, 'country_code' => 'CA', 'time_zone_id' => 'America/Halifax', 'gmt' => -4, 'dst' => -3, 'raw' => -4],
			['id' => 115, 'country_code' => 'CU', 'time_zone_id' => 'America/Havana', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 116, 'country_code' => 'MX', 'time_zone_id' => 'America/Hermosillo', 'gmt' => -7, 'dst' => -7, 'raw' => -7],
			['id' => 117, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Indianapolis', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 118, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Knox', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 119, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Marengo', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 120, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Petersburg', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 121, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Tell_City', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 122, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Vevay', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 123, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Vincennes', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 124, 'country_code' => 'US', 'time_zone_id' => 'America/Indiana/Winamac', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 125, 'country_code' => 'CA', 'time_zone_id' => 'America/Inuvik', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 126, 'country_code' => 'CA', 'time_zone_id' => 'America/Iqaluit', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 127, 'country_code' => 'JM', 'time_zone_id' => 'America/Jamaica', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 128, 'country_code' => 'US', 'time_zone_id' => 'America/Juneau', 'gmt' => -9, 'dst' => -8, 'raw' => -9],
			['id' => 129, 'country_code' => 'US', 'time_zone_id' => 'America/Kentucky/Louisville', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 130, 'country_code' => 'US', 'time_zone_id' => 'America/Kentucky/Monticello', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 131, 'country_code' => 'BQ', 'time_zone_id' => 'America/Kralendijk', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 132, 'country_code' => 'BO', 'time_zone_id' => 'America/La_Paz', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 133, 'country_code' => 'PE', 'time_zone_id' => 'America/Lima', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 134, 'country_code' => 'US', 'time_zone_id' => 'America/Los_Angeles', 'gmt' => -8, 'dst' => -7, 'raw' => -8],
			['id' => 135, 'country_code' => 'SX', 'time_zone_id' => 'America/Lower_Princes', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 136, 'country_code' => 'BR', 'time_zone_id' => 'America/Maceio', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 137, 'country_code' => 'NI', 'time_zone_id' => 'America/Managua', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 138, 'country_code' => 'BR', 'time_zone_id' => 'America/Manaus', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 139, 'country_code' => 'MF', 'time_zone_id' => 'America/Marigot', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 140, 'country_code' => 'MQ', 'time_zone_id' => 'America/Martinique', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 141, 'country_code' => 'MX', 'time_zone_id' => 'America/Matamoros', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 142, 'country_code' => 'MX', 'time_zone_id' => 'America/Mazatlan', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 143, 'country_code' => 'US', 'time_zone_id' => 'America/Menominee', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 144, 'country_code' => 'MX', 'time_zone_id' => 'America/Merida', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 145, 'country_code' => 'US', 'time_zone_id' => 'America/Metlakatla', 'gmt' => -9, 'dst' => -8, 'raw' => -9],
			['id' => 146, 'country_code' => 'MX', 'time_zone_id' => 'America/Mexico_City', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 147, 'country_code' => 'PM', 'time_zone_id' => 'America/Miquelon', 'gmt' => -3, 'dst' => -2, 'raw' => -3],
			['id' => 148, 'country_code' => 'CA', 'time_zone_id' => 'America/Moncton', 'gmt' => -4, 'dst' => -3, 'raw' => -4],
			['id' => 149, 'country_code' => 'MX', 'time_zone_id' => 'America/Monterrey', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 150, 'country_code' => 'UY', 'time_zone_id' => 'America/Montevideo', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 151, 'country_code' => 'MS', 'time_zone_id' => 'America/Montserrat', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 152, 'country_code' => 'BS', 'time_zone_id' => 'America/Nassau', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 153, 'country_code' => 'US', 'time_zone_id' => 'America/New_York', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 154, 'country_code' => 'CA', 'time_zone_id' => 'America/Nipigon', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 155, 'country_code' => 'US', 'time_zone_id' => 'America/Nome', 'gmt' => -9, 'dst' => -8, 'raw' => -9],
			['id' => 156, 'country_code' => 'BR', 'time_zone_id' => 'America/Noronha', 'gmt' => -2, 'dst' => -2, 'raw' => -2],
			['id' => 157, 'country_code' => 'US', 'time_zone_id' => 'America/North_Dakota/Beulah', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 158, 'country_code' => 'US', 'time_zone_id' => 'America/North_Dakota/Center', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 159, 'country_code' => 'US', 'time_zone_id' => 'America/North_Dakota/New_Salem', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 160, 'country_code' => 'MX', 'time_zone_id' => 'America/Ojinaga', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 161, 'country_code' => 'PA', 'time_zone_id' => 'America/Panama', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 162, 'country_code' => 'CA', 'time_zone_id' => 'America/Pangnirtung', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 163, 'country_code' => 'SR', 'time_zone_id' => 'America/Paramaribo', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 164, 'country_code' => 'US', 'time_zone_id' => 'America/Phoenix', 'gmt' => -7, 'dst' => -7, 'raw' => -7],
			['id' => 165, 'country_code' => 'HT', 'time_zone_id' => 'America/Port-au-Prince', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 166, 'country_code' => 'TT', 'time_zone_id' => 'America/Port_of_Spain', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 167, 'country_code' => 'BR', 'time_zone_id' => 'America/Porto_Velho', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 168, 'country_code' => 'PR', 'time_zone_id' => 'America/Puerto_Rico', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 169, 'country_code' => 'CL', 'time_zone_id' => 'America/Punta_Arenas', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 170, 'country_code' => 'CA', 'time_zone_id' => 'America/Rainy_River', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 171, 'country_code' => 'CA', 'time_zone_id' => 'America/Rankin_Inlet', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 172, 'country_code' => 'BR', 'time_zone_id' => 'America/Recife', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 173, 'country_code' => 'CA', 'time_zone_id' => 'America/Regina', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 174, 'country_code' => 'CA', 'time_zone_id' => 'America/Resolute', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 175, 'country_code' => 'BR', 'time_zone_id' => 'America/Rio_Branco', 'gmt' => -5, 'dst' => -5, 'raw' => -5],
			['id' => 176, 'country_code' => 'BR', 'time_zone_id' => 'America/Santarem', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 177, 'country_code' => 'CL', 'time_zone_id' => 'America/Santiago', 'gmt' => -3, 'dst' => -4, 'raw' => -4],
			['id' => 178, 'country_code' => 'DO', 'time_zone_id' => 'America/Santo_Domingo', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 179, 'country_code' => 'BR', 'time_zone_id' => 'America/Sao_Paulo', 'gmt' => -2, 'dst' => -3, 'raw' => -3],
			['id' => 180, 'country_code' => 'GL', 'time_zone_id' => 'America/Scoresbysund', 'gmt' => -1, 'dst' => 0, 'raw' => -1],
			['id' => 181, 'country_code' => 'US', 'time_zone_id' => 'America/Sitka', 'gmt' => -9, 'dst' => -8, 'raw' => -9],
			['id' => 182, 'country_code' => 'BL', 'time_zone_id' => 'America/St_Barthelemy', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 183, 'country_code' => 'CA', 'time_zone_id' => 'America/St_Johns', 'gmt' => -3.5, 'dst' => -2.5, 'raw' => -3.5],
			['id' => 184, 'country_code' => 'KN', 'time_zone_id' => 'America/St_Kitts', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 185, 'country_code' => 'LC', 'time_zone_id' => 'America/St_Lucia', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 186, 'country_code' => 'VI', 'time_zone_id' => 'America/St_Thomas', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 187, 'country_code' => 'VC', 'time_zone_id' => 'America/St_Vincent', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 188, 'country_code' => 'CA', 'time_zone_id' => 'America/Swift_Current', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 189, 'country_code' => 'HN', 'time_zone_id' => 'America/Tegucigalpa', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 190, 'country_code' => 'GL', 'time_zone_id' => 'America/Thule', 'gmt' => -4, 'dst' => -3, 'raw' => -4],
			['id' => 191, 'country_code' => 'CA', 'time_zone_id' => 'America/Thunder_Bay', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 192, 'country_code' => 'MX', 'time_zone_id' => 'America/Tijuana', 'gmt' => -8, 'dst' => -7, 'raw' => -8],
			['id' => 193, 'country_code' => 'CA', 'time_zone_id' => 'America/Toronto', 'gmt' => -5, 'dst' => -4, 'raw' => -5],
			['id' => 194, 'country_code' => 'VG', 'time_zone_id' => 'America/Tortola', 'gmt' => -4, 'dst' => -4, 'raw' => -4],
			['id' => 195, 'country_code' => 'CA', 'time_zone_id' => 'America/Vancouver', 'gmt' => -8, 'dst' => -7, 'raw' => -8],
			['id' => 196, 'country_code' => 'CA', 'time_zone_id' => 'America/Whitehorse', 'gmt' => -8, 'dst' => -7, 'raw' => -8],
			['id' => 197, 'country_code' => 'CA', 'time_zone_id' => 'America/Winnipeg', 'gmt' => -6, 'dst' => -5, 'raw' => -6],
			['id' => 198, 'country_code' => 'US', 'time_zone_id' => 'America/Yakutat', 'gmt' => -9, 'dst' => -8, 'raw' => -9],
			['id' => 199, 'country_code' => 'CA', 'time_zone_id' => 'America/Yellowknife', 'gmt' => -7, 'dst' => -6, 'raw' => -7],
			['id' => 200, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Casey', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 201, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Davis', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 202, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/DumontDUrville', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 203, 'country_code' => 'AU', 'time_zone_id' => 'Antarctica/Macquarie', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 204, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Mawson', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 205, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/McMurdo', 'gmt' => 13, 'dst' => 12, 'raw' => 12],
			['id' => 206, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Palmer', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 207, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Rothera', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 208, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Syowa', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 209, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Troll', 'gmt' => 0, 'dst' => 2, 'raw' => 0],
			['id' => 210, 'country_code' => 'AQ', 'time_zone_id' => 'Antarctica/Vostok', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 211, 'country_code' => 'SJ', 'time_zone_id' => 'Arctic/Longyearbyen', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 212, 'country_code' => 'YE', 'time_zone_id' => 'Asia/Aden', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 213, 'country_code' => 'KZ', 'time_zone_id' => 'Asia/Almaty', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 214, 'country_code' => 'JO', 'time_zone_id' => 'Asia/Amman', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 215, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Anadyr', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 216, 'country_code' => 'KZ', 'time_zone_id' => 'Asia/Aqtau', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 217, 'country_code' => 'KZ', 'time_zone_id' => 'Asia/Aqtobe', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 218, 'country_code' => 'TM', 'time_zone_id' => 'Asia/Ashgabat', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 219, 'country_code' => 'KZ', 'time_zone_id' => 'Asia/Atyrau', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 220, 'country_code' => 'IQ', 'time_zone_id' => 'Asia/Baghdad', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 221, 'country_code' => 'BH', 'time_zone_id' => 'Asia/Bahrain', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 222, 'country_code' => 'AZ', 'time_zone_id' => 'Asia/Baku', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 223, 'country_code' => 'TH', 'time_zone_id' => 'Asia/Bangkok', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 224, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Barnaul', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 225, 'country_code' => 'LB', 'time_zone_id' => 'Asia/Beirut', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 226, 'country_code' => 'KG', 'time_zone_id' => 'Asia/Bishkek', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 227, 'country_code' => 'BN', 'time_zone_id' => 'Asia/Brunei', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 228, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Chita', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 229, 'country_code' => 'MN', 'time_zone_id' => 'Asia/Choibalsan', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 230, 'country_code' => 'LK', 'time_zone_id' => 'Asia/Colombo', 'gmt' => 5.5, 'dst' => 5.5, 'raw' => 5.5],
			['id' => 231, 'country_code' => 'SY', 'time_zone_id' => 'Asia/Damascus', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 232, 'country_code' => 'BD', 'time_zone_id' => 'Asia/Dhaka', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 233, 'country_code' => 'TL', 'time_zone_id' => 'Asia/Dili', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 234, 'country_code' => 'AE', 'time_zone_id' => 'Asia/Dubai', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 235, 'country_code' => 'TJ', 'time_zone_id' => 'Asia/Dushanbe', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 236, 'country_code' => 'CY', 'time_zone_id' => 'Asia/Famagusta', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 237, 'country_code' => 'PS', 'time_zone_id' => 'Asia/Gaza', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 238, 'country_code' => 'PS', 'time_zone_id' => 'Asia/Hebron', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 239, 'country_code' => 'VN', 'time_zone_id' => 'Asia/Ho_Chi_Minh', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 240, 'country_code' => 'HK', 'time_zone_id' => 'Asia/Hong_Kong', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 241, 'country_code' => 'MN', 'time_zone_id' => 'Asia/Hovd', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 242, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Irkutsk', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 243, 'country_code' => 'ID', 'time_zone_id' => 'Asia/Jakarta', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 244, 'country_code' => 'ID', 'time_zone_id' => 'Asia/Jayapura', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 245, 'country_code' => 'IL', 'time_zone_id' => 'Asia/Jerusalem', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 246, 'country_code' => 'AF', 'time_zone_id' => 'Asia/Kabul', 'gmt' => 4.5, 'dst' => 4.5, 'raw' => 4.5],
			['id' => 247, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Kamchatka', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 248, 'country_code' => 'PK', 'time_zone_id' => 'Asia/Karachi', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 249, 'country_code' => 'NP', 'time_zone_id' => 'Asia/Kathmandu', 'gmt' => 5.75, 'dst' => 5.75, 'raw' => 5.75],
			['id' => 250, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Khandyga', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 251, 'country_code' => 'IN', 'time_zone_id' => 'Asia/Kolkata', 'gmt' => 5.5, 'dst' => 5.5, 'raw' => 5.5],
			['id' => 252, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Krasnoyarsk', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 253, 'country_code' => 'MY', 'time_zone_id' => 'Asia/Kuala_Lumpur', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 254, 'country_code' => 'MY', 'time_zone_id' => 'Asia/Kuching', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 255, 'country_code' => 'KW', 'time_zone_id' => 'Asia/Kuwait', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 256, 'country_code' => 'MO', 'time_zone_id' => 'Asia/Macau', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 257, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Magadan', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 258, 'country_code' => 'ID', 'time_zone_id' => 'Asia/Makassar', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 259, 'country_code' => 'PH', 'time_zone_id' => 'Asia/Manila', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 260, 'country_code' => 'OM', 'time_zone_id' => 'Asia/Muscat', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 261, 'country_code' => 'CY', 'time_zone_id' => 'Asia/Nicosia', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 262, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Novokuznetsk', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 263, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Novosibirsk', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 264, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Omsk', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 265, 'country_code' => 'KZ', 'time_zone_id' => 'Asia/Oral', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 266, 'country_code' => 'KH', 'time_zone_id' => 'Asia/Phnom_Penh', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 267, 'country_code' => 'ID', 'time_zone_id' => 'Asia/Pontianak', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 268, 'country_code' => 'KP', 'time_zone_id' => 'Asia/Pyongyang', 'gmt' => 8.5, 'dst' => 8.5, 'raw' => 8.5],
			['id' => 269, 'country_code' => 'QA', 'time_zone_id' => 'Asia/Qatar', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 270, 'country_code' => 'KZ', 'time_zone_id' => 'Asia/Qyzylorda', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 271, 'country_code' => 'SA', 'time_zone_id' => 'Asia/Riyadh', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 272, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Sakhalin', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 273, 'country_code' => 'UZ', 'time_zone_id' => 'Asia/Samarkand', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 274, 'country_code' => 'KR', 'time_zone_id' => 'Asia/Seoul', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 275, 'country_code' => 'CN', 'time_zone_id' => 'Asia/Shanghai', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 276, 'country_code' => 'SG', 'time_zone_id' => 'Asia/Singapore', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 277, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Srednekolymsk', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 278, 'country_code' => 'TW', 'time_zone_id' => 'Asia/Taipei', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 279, 'country_code' => 'UZ', 'time_zone_id' => 'Asia/Tashkent', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 280, 'country_code' => 'GE', 'time_zone_id' => 'Asia/Tbilisi', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 281, 'country_code' => 'IR', 'time_zone_id' => 'Asia/Tehran', 'gmt' => 3.5, 'dst' => 4.5, 'raw' => 3.5],
			['id' => 282, 'country_code' => 'BT', 'time_zone_id' => 'Asia/Thimphu', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 283, 'country_code' => 'JP', 'time_zone_id' => 'Asia/Tokyo', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 284, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Tomsk', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 285, 'country_code' => 'MN', 'time_zone_id' => 'Asia/Ulaanbaatar', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 286, 'country_code' => 'CN', 'time_zone_id' => 'Asia/Urumqi', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 287, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Ust-Nera', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 288, 'country_code' => 'LA', 'time_zone_id' => 'Asia/Vientiane', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 289, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Vladivostok', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 290, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Yakutsk', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 291, 'country_code' => 'MM', 'time_zone_id' => 'Asia/Yangon', 'gmt' => 6.5, 'dst' => 6.5, 'raw' => 6.5],
			['id' => 292, 'country_code' => 'RU', 'time_zone_id' => 'Asia/Yekaterinburg', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 293, 'country_code' => 'AM', 'time_zone_id' => 'Asia/Yerevan', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 294, 'country_code' => 'PT', 'time_zone_id' => 'Atlantic/Azores', 'gmt' => -1, 'dst' => 0, 'raw' => -1],
			['id' => 295, 'country_code' => 'BM', 'time_zone_id' => 'Atlantic/Bermuda', 'gmt' => -4, 'dst' => -3, 'raw' => -4],
			['id' => 296, 'country_code' => 'ES', 'time_zone_id' => 'Atlantic/Canary', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 297, 'country_code' => 'CV', 'time_zone_id' => 'Atlantic/Cape_Verde', 'gmt' => -1, 'dst' => -1, 'raw' => -1],
			['id' => 298, 'country_code' => 'FO', 'time_zone_id' => 'Atlantic/Faroe', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 299, 'country_code' => 'PT', 'time_zone_id' => 'Atlantic/Madeira', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 300, 'country_code' => 'IS', 'time_zone_id' => 'Atlantic/Reykjavik', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 301, 'country_code' => 'GS', 'time_zone_id' => 'Atlantic/South_Georgia', 'gmt' => -2, 'dst' => -2, 'raw' => -2],
			['id' => 302, 'country_code' => 'SH', 'time_zone_id' => 'Atlantic/St_Helena', 'gmt' => 0, 'dst' => 0, 'raw' => 0],
			['id' => 303, 'country_code' => 'FK', 'time_zone_id' => 'Atlantic/Stanley', 'gmt' => -3, 'dst' => -3, 'raw' => -3],
			['id' => 304, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Adelaide', 'gmt' => 10.5, 'dst' => 9.5, 'raw' => 9.5],
			['id' => 305, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Brisbane', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 306, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Broken_Hill', 'gmt' => 10.5, 'dst' => 9.5, 'raw' => 9.5],
			['id' => 307, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Currie', 'gmt' => 11, 'dst' => 10, 'raw' => 10],
			['id' => 308, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Darwin', 'gmt' => 9.5, 'dst' => 9.5, 'raw' => 9.5],
			['id' => 309, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Eucla', 'gmt' => 8.75, 'dst' => 8.75, 'raw' => 8.75],
			['id' => 310, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Hobart', 'gmt' => 11, 'dst' => 10, 'raw' => 10],
			['id' => 311, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Lindeman', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 312, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Lord_Howe', 'gmt' => 11, 'dst' => 10.5, 'raw' => 10.5],
			['id' => 313, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Melbourne', 'gmt' => 11, 'dst' => 10, 'raw' => 10],
			['id' => 314, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Perth', 'gmt' => 8, 'dst' => 8, 'raw' => 8],
			['id' => 315, 'country_code' => 'AU', 'time_zone_id' => 'Australia/Sydney', 'gmt' => 11, 'dst' => 10, 'raw' => 10],
			['id' => 316, 'country_code' => 'NL', 'time_zone_id' => 'Europe/Amsterdam', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 317, 'country_code' => 'AD', 'time_zone_id' => 'Europe/Andorra', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 318, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Astrakhan', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 319, 'country_code' => 'GR', 'time_zone_id' => 'Europe/Athens', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 320, 'country_code' => 'RS', 'time_zone_id' => 'Europe/Belgrade', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 321, 'country_code' => 'DE', 'time_zone_id' => 'Europe/Berlin', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 322, 'country_code' => 'SK', 'time_zone_id' => 'Europe/Bratislava', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 323, 'country_code' => 'BE', 'time_zone_id' => 'Europe/Brussels', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 324, 'country_code' => 'RO', 'time_zone_id' => 'Europe/Bucharest', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 325, 'country_code' => 'HU', 'time_zone_id' => 'Europe/Budapest', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 326, 'country_code' => 'DE', 'time_zone_id' => 'Europe/Busingen', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 327, 'country_code' => 'MD', 'time_zone_id' => 'Europe/Chisinau', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 328, 'country_code' => 'DK', 'time_zone_id' => 'Europe/Copenhagen', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 329, 'country_code' => 'IE', 'time_zone_id' => 'Europe/Dublin', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 330, 'country_code' => 'GI', 'time_zone_id' => 'Europe/Gibraltar', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 331, 'country_code' => 'GG', 'time_zone_id' => 'Europe/Guernsey', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 332, 'country_code' => 'FI', 'time_zone_id' => 'Europe/Helsinki', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 333, 'country_code' => 'IM', 'time_zone_id' => 'Europe/Isle_of_Man', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 334, 'country_code' => 'TR', 'time_zone_id' => 'Europe/Istanbul', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 335, 'country_code' => 'JE', 'time_zone_id' => 'Europe/Jersey', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 336, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Kaliningrad', 'gmt' => 2, 'dst' => 2, 'raw' => 2],
			['id' => 337, 'country_code' => 'UA', 'time_zone_id' => 'Europe/Kiev', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 338, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Kirov', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 339, 'country_code' => 'PT', 'time_zone_id' => 'Europe/Lisbon', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 340, 'country_code' => 'SI', 'time_zone_id' => 'Europe/Ljubljana', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 341, 'country_code' => 'UK', 'time_zone_id' => 'Europe/London', 'gmt' => 0, 'dst' => 1, 'raw' => 0],
			['id' => 342, 'country_code' => 'LU', 'time_zone_id' => 'Europe/Luxembourg', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 343, 'country_code' => 'ES', 'time_zone_id' => 'Europe/Madrid', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 344, 'country_code' => 'MT', 'time_zone_id' => 'Europe/Malta', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 345, 'country_code' => 'AX', 'time_zone_id' => 'Europe/Mariehamn', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 346, 'country_code' => 'BY', 'time_zone_id' => 'Europe/Minsk', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 347, 'country_code' => 'MC', 'time_zone_id' => 'Europe/Monaco', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 348, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Moscow', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 349, 'country_code' => 'NO', 'time_zone_id' => 'Europe/Oslo', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 350, 'country_code' => 'FR', 'time_zone_id' => 'Europe/Paris', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 351, 'country_code' => 'ME', 'time_zone_id' => 'Europe/Podgorica', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 352, 'country_code' => 'CZ', 'time_zone_id' => 'Europe/Prague', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 353, 'country_code' => 'LV', 'time_zone_id' => 'Europe/Riga', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 354, 'country_code' => 'IT', 'time_zone_id' => 'Europe/Rome', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 355, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Samara', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 356, 'country_code' => 'SM', 'time_zone_id' => 'Europe/San_Marino', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 357, 'country_code' => 'BA', 'time_zone_id' => 'Europe/Sarajevo', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 358, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Saratov', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 359, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Simferopol', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 360, 'country_code' => 'MK', 'time_zone_id' => 'Europe/Skopje', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 361, 'country_code' => 'BG', 'time_zone_id' => 'Europe/Sofia', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 362, 'country_code' => 'SE', 'time_zone_id' => 'Europe/Stockholm', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 363, 'country_code' => 'EE', 'time_zone_id' => 'Europe/Tallinn', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 364, 'country_code' => 'AL', 'time_zone_id' => 'Europe/Tirane', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 365, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Ulyanovsk', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 366, 'country_code' => 'UA', 'time_zone_id' => 'Europe/Uzhgorod', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 367, 'country_code' => 'LI', 'time_zone_id' => 'Europe/Vaduz', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 368, 'country_code' => 'VA', 'time_zone_id' => 'Europe/Vatican', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 369, 'country_code' => 'AT', 'time_zone_id' => 'Europe/Vienna', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 370, 'country_code' => 'LT', 'time_zone_id' => 'Europe/Vilnius', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 371, 'country_code' => 'RU', 'time_zone_id' => 'Europe/Volgograd', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 372, 'country_code' => 'PL', 'time_zone_id' => 'Europe/Warsaw', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 373, 'country_code' => 'HR', 'time_zone_id' => 'Europe/Zagreb', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 374, 'country_code' => 'UA', 'time_zone_id' => 'Europe/Zaporozhye', 'gmt' => 2, 'dst' => 3, 'raw' => 2],
			['id' => 375, 'country_code' => 'CH', 'time_zone_id' => 'Europe/Zurich', 'gmt' => 1, 'dst' => 2, 'raw' => 1],
			['id' => 376, 'country_code' => 'MG', 'time_zone_id' => 'Indian/Antananarivo', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 377, 'country_code' => 'IO', 'time_zone_id' => 'Indian/Chagos', 'gmt' => 6, 'dst' => 6, 'raw' => 6],
			['id' => 378, 'country_code' => 'CX', 'time_zone_id' => 'Indian/Christmas', 'gmt' => 7, 'dst' => 7, 'raw' => 7],
			['id' => 379, 'country_code' => 'CC', 'time_zone_id' => 'Indian/Cocos', 'gmt' => 6.5, 'dst' => 6.5, 'raw' => 6.5],
			['id' => 380, 'country_code' => 'KM', 'time_zone_id' => 'Indian/Comoro', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 381, 'country_code' => 'TF', 'time_zone_id' => 'Indian/Kerguelen', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 382, 'country_code' => 'SC', 'time_zone_id' => 'Indian/Mahe', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 383, 'country_code' => 'MV', 'time_zone_id' => 'Indian/Maldives', 'gmt' => 5, 'dst' => 5, 'raw' => 5],
			['id' => 384, 'country_code' => 'MU', 'time_zone_id' => 'Indian/Mauritius', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 385, 'country_code' => 'YT', 'time_zone_id' => 'Indian/Mayotte', 'gmt' => 3, 'dst' => 3, 'raw' => 3],
			['id' => 386, 'country_code' => 'RE', 'time_zone_id' => 'Indian/Reunion', 'gmt' => 4, 'dst' => 4, 'raw' => 4],
			['id' => 387, 'country_code' => 'WS', 'time_zone_id' => 'Pacific/Apia', 'gmt' => 14, 'dst' => 13, 'raw' => 13],
			['id' => 388, 'country_code' => 'NZ', 'time_zone_id' => 'Pacific/Auckland', 'gmt' => 13, 'dst' => 12, 'raw' => 12],
			['id' => 389, 'country_code' => 'PG', 'time_zone_id' => 'Pacific/Bougainville', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 390, 'country_code' => 'NZ', 'time_zone_id' => 'Pacific/Chatham', 'gmt' => 13.75, 'dst' => 12.75, 'raw' => 12.75],
			['id' => 391, 'country_code' => 'FM', 'time_zone_id' => 'Pacific/Chuuk', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 392, 'country_code' => 'CL', 'time_zone_id' => 'Pacific/Easter', 'gmt' => -5, 'dst' => -6, 'raw' => -6],
			['id' => 393, 'country_code' => 'VU', 'time_zone_id' => 'Pacific/Efate', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 394, 'country_code' => 'KI', 'time_zone_id' => 'Pacific/Enderbury', 'gmt' => 13, 'dst' => 13, 'raw' => 13],
			['id' => 395, 'country_code' => 'TK', 'time_zone_id' => 'Pacific/Fakaofo', 'gmt' => 13, 'dst' => 13, 'raw' => 13],
			['id' => 396, 'country_code' => 'FJ', 'time_zone_id' => 'Pacific/Fiji', 'gmt' => 13, 'dst' => 12, 'raw' => 12],
			['id' => 397, 'country_code' => 'TV', 'time_zone_id' => 'Pacific/Funafuti', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 398, 'country_code' => 'EC', 'time_zone_id' => 'Pacific/Galapagos', 'gmt' => -6, 'dst' => -6, 'raw' => -6],
			['id' => 399, 'country_code' => 'PF', 'time_zone_id' => 'Pacific/Gambier', 'gmt' => -9, 'dst' => -9, 'raw' => -9],
			['id' => 400, 'country_code' => 'SB', 'time_zone_id' => 'Pacific/Guadalcanal', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 401, 'country_code' => 'GU', 'time_zone_id' => 'Pacific/Guam', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 402, 'country_code' => 'US', 'time_zone_id' => 'Pacific/Honolulu', 'gmt' => -10, 'dst' => -10, 'raw' => -10],
			['id' => 403, 'country_code' => 'KI', 'time_zone_id' => 'Pacific/Kiritimati', 'gmt' => 14, 'dst' => 14, 'raw' => 14],
			['id' => 404, 'country_code' => 'FM', 'time_zone_id' => 'Pacific/Kosrae', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 405, 'country_code' => 'MH', 'time_zone_id' => 'Pacific/Kwajalein', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 406, 'country_code' => 'MH', 'time_zone_id' => 'Pacific/Majuro', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 407, 'country_code' => 'PF', 'time_zone_id' => 'Pacific/Marquesas', 'gmt' => -9.5, 'dst' => -9.5, 'raw' => -9.5],
			['id' => 408, 'country_code' => 'UM', 'time_zone_id' => 'Pacific/Midway', 'gmt' => -11, 'dst' => -11, 'raw' => -11],
			['id' => 409, 'country_code' => 'NR', 'time_zone_id' => 'Pacific/Nauru', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 410, 'country_code' => 'NU', 'time_zone_id' => 'Pacific/Niue', 'gmt' => -11, 'dst' => -11, 'raw' => -11],
			['id' => 411, 'country_code' => 'NF', 'time_zone_id' => 'Pacific/Norfolk', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 412, 'country_code' => 'NC', 'time_zone_id' => 'Pacific/Noumea', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 413, 'country_code' => 'AS', 'time_zone_id' => 'Pacific/Pago_Pago', 'gmt' => -11, 'dst' => -11, 'raw' => -11],
			['id' => 414, 'country_code' => 'PW', 'time_zone_id' => 'Pacific/Palau', 'gmt' => 9, 'dst' => 9, 'raw' => 9],
			['id' => 415, 'country_code' => 'PN', 'time_zone_id' => 'Pacific/Pitcairn', 'gmt' => -8, 'dst' => -8, 'raw' => -8],
			['id' => 416, 'country_code' => 'FM', 'time_zone_id' => 'Pacific/Pohnpei', 'gmt' => 11, 'dst' => 11, 'raw' => 11],
			['id' => 417, 'country_code' => 'PG', 'time_zone_id' => 'Pacific/Port_Moresby', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 418, 'country_code' => 'CK', 'time_zone_id' => 'Pacific/Rarotonga', 'gmt' => -10, 'dst' => -10, 'raw' => -10],
			['id' => 419, 'country_code' => 'MP', 'time_zone_id' => 'Pacific/Saipan', 'gmt' => 10, 'dst' => 10, 'raw' => 10],
			['id' => 420, 'country_code' => 'PF', 'time_zone_id' => 'Pacific/Tahiti', 'gmt' => -10, 'dst' => -10, 'raw' => -10],
			['id' => 421, 'country_code' => 'KI', 'time_zone_id' => 'Pacific/Tarawa', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 422, 'country_code' => 'TO', 'time_zone_id' => 'Pacific/Tongatapu', 'gmt' => 14, 'dst' => 13, 'raw' => 13],
			['id' => 423, 'country_code' => 'UM', 'time_zone_id' => 'Pacific/Wake', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
			['id' => 424, 'country_code' => 'WF', 'time_zone_id' => 'Pacific/Wallis', 'gmt' => 12, 'dst' => 12, 'raw' => 12],
		];
		foreach ($allData as $item) {
			$countryCode = $item['country_code'] ?? '';
			$timeZoneId = $item['time_zone_id'] ?? '';
			
			$timeZone = DB::table('time_zones')->where('country_code', '=', $countryCode)->where('time_zone_id', $timeZoneId)->first();
			if (empty($timeZone)) {
				DB::table('time_zones')->insert($item);
			}
		}
	}
	
	// users
	if (!Schema::hasColumn('users', 'can_be_impersonated') && Schema::hasColumn('users', 'is_admin')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('can_be_impersonated')->unsigned()->nullable()->default(1)->after('is_admin');
			$table->index('can_be_impersonated');
		});
	}
	if (Schema::hasColumn('users', 'is_admin')) {
		DBIndex::dropIndexIfExists('users', 'is_admin');
		DBIndex::createIndexIfNotExists('users', 'is_admin');
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
