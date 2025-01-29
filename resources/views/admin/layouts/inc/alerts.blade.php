{{-- Bootstrap Notifications using Prologue Alerts --}}
{{-- PNotify: https://github.com/sciactive/pnotify --}}
<script type="text/javascript">
	onDocumentReady((event) => {
		
		/* Load Modules */
		PNotify.defaultModules.set(PNotifyFontAwesome5Fix, {});
		PNotify.defaultModules.set(PNotifyFontAwesome5, {});
		
		/* Set Default Values */
		PNotify.defaults.styling = 'custom';    /* Can be 'brighttheme', 'material', or 'custom' */
		/* PNotify.defaults.mode = 'light';     /* Can be 'no-preference', 'light', or 'dark' */
		/* PNotify.defaults.icons = 'material'; /* Can be 'brighttheme', 'material', or ... */
		
		@foreach (Alert::getMessages() as $type => $messages)
			@foreach ($messages as $message)
				
				@php
					$message = escapeStringForJs($message);
				@endphp
				
				$(function () {
					let alertMessage = "{!! $message !!}";
					let alertType = "{{ $type }}";
					
					@if ($message == t('demo_mode_message'))
						pnAlertForPrologue(alertType, alertMessage, 'Information');
					@else
						pnAlertForPrologue(alertType, alertMessage);
					@endif
				});
				
			@endforeach
		@endforeach
		
		/**
		 * Show a PNotify alert (Using the Stack feature)
		 * @param type
		 * @param message
		 * @param title
		 */
		function pnAlertForPrologue(type, message, title = '') {
			if (typeof window.stackTopRight === 'undefined') {
				window.stackTopRight = new PNotify.Stack({
					dir1: 'down',
					dir2: 'left',
					firstpos1: 25,
					firstpos2: 25,
					spacing1: 10,
					spacing2: 25,
					modal: false,
					maxOpen: Infinity
				});
			}
			let alertParams = {
				text: message,
				textTrusted: true,
				type: 'info',
				icon: false,
				stack: window.stackTopRight
			};
			switch (type) {
				case 'error':
					alertParams.type = 'error';
					break;
				case 'warning':
					alertParams.type = 'notice';
					break;
				case 'notice':
					alertParams.type = 'notice';
					break;
				case 'info':
					alertParams.type = 'info';
					break;
				case 'success':
					alertParams.type = 'success';
					break;
			}
			if (typeof title !== 'undefined' && title != '' && title.length !== 0) {
				alertParams.title = title;
				alertParams.icon = true;
			}
			
			PNotify.alert(alertParams);
		}
	});
</script>
