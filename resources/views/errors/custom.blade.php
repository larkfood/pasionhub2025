@php
	$httpStatus = getReferrerList('http-status');
	
	$statusCode = (!empty($status) && !empty($httpStatus[$status])) ? $status : 500;
	if (isset($exception) && $exception instanceof \Throwable) {
		if (method_exists($exception, 'getStatusCode')) {
			try {
				$statusCode = $exception->getStatusCode();
			} catch (\Throwable $e) {
			}
		}
	}
	$title = $httpStatus[$statusCode] ?? 'Internal Server Error';
@endphp
<!DOCTYPE html>
<html lang="{{ getLangTag(config('app.locale', 'en')) }}">
<head>
	<title>{{ $title }}</title>
	<meta charset="{{ config('larapen.core.charset', 'utf-8') }}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex,nofollow">
	<meta name="googlebot" content="noindex">
	<link href="{{ url(mix('dist/front/styles.css')) }}" rel="stylesheet">
	<style>
		.page-not-found {
			background-color: #f0f0f0;
			height: 100vh;
		}
		
		.page-not-found h2 {
			font-size: 150px;
			color: #e0e0e0;
			line-height: normal !important;
		}
		
		.page-not-found h3 {
			font-size: 30px;
		}
		
		.page-not-found .bg-light {
			width: 50%;
			padding: 50px;
			border-radius: 10px;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
		}
		
		@media (max-width: 991px) {
			.page-not-found h2 {
				font-size: 100px;
			}
			
			.page-not-found h3 {
				font-size: 28px;
			}
			
			.page-not-found .bg-light {
				width: 95%;
			}
		}
	</style>
	@include('front.common.js.document')
</head>
<body>
<div class="page-not-found pt-5">
	<div class="bg-light text-center shadow">
		<h2 class="fw-bold py-0 text-primary">{{ $statusCode }}</h2>
		<h3 class="mt-4 mb-3">{{ $title }}</h3>
		<p class="text-start">
			@php
				$defaultErrorMessage = 'An internal server error has occurred.';
				$extractedMessage = null;
				
				if (!empty($message)) {
					$extractedMessage = nl2br($message);
				}
				if (empty($extractedMessage)) {
					if (isset($exception) && $exception instanceof \Throwable) {
						$extractedMessage = $exception->getMessage();
						$extractedMessage = str_replace(base_path(), '', $extractedMessage);
						
						if (!empty($extractedMessage)) {
							if (method_exists($exception, 'getFile')) {
								$filePath = $exception->getFile();
								$filePath = str_replace(base_path(), '', $filePath);
								$extractedMessage .= "\n" . 'In the: <code>' . $filePath . '</code> file';
								if (method_exists($exception, 'getLine')) {
									$extractedMessage .= ' at line: <code>' . $exception->getLine() . '</code>';
								}
							}
							$extractedMessage = nl2br($extractedMessage);
						}
					}
				}
				
				$message = !empty($extractedMessage) ? $extractedMessage : $defaultErrorMessage;
				
				echo $message;
			@endphp
		</p>
		<div class="mt-5">
			@php
				if (isFromInstallOrUpgradeProcess()) {
					if (in_array(request()->method(), ['POST', 'PUT'])) {
						$linkLabel = 'Go back';
						$linkIcon = 'bi bi-arrow-left';
						$linkUrl = url()->previous();
					} else {
						$linkLabel = 'Reload'; // Reload | Refresh
						$linkIcon = 'bi bi-arrow-clockwise';
						$linkUrl = url()->full();
					}
				} else {
					$linkLabel = 'Back Home';
					$linkIcon = 'bi bi-house-door-fill';
					$linkUrl = url('/');
				}
			@endphp
			<a href="{{ $linkUrl }}" class="btn m-2 m-md-0 btn-primary">
				<i class="{{ $linkIcon }}"></i> {{ $linkLabel }}
			</a>
		</div>
	</div>
</div>
</body>
</html>
