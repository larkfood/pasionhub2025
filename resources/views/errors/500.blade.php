@extends('errors::layout')

@section('title', t('Internal Server Error'))

@section('message')
	<div class="text-center">
		<h1 style="font-size: 200px; margin: 0;">500</h1>
		<div style="font-size: 20px;">
			<h3 style="text-transform: capitalize;">
				{{ t('Internal Server Error') }}
			</h3>
			<p>
				@php
					$isDebugEnabled = config('app.debug');
					$defaultErrorMessage = t('An internal server error has occurred');
					$extractedMessage = null;
					
					if (isset($exception) && $exception instanceof \Throwable) {
						$extractedMessage = $exception->getMessage();
						$extractedMessage = str_replace(base_path(), '', $extractedMessage);
						
						if (!empty($extractedMessage) && $isDebugEnabled) {
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
					
					$message = !empty($extractedMessage) ? $extractedMessage : $defaultErrorMessage;
					
					echo $message;
				@endphp
			</p>
		</div>
	</div>
@endsection
