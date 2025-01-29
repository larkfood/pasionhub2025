@extends('errors::layout')

@section('title', t('Unauthorized action'))

@section('message')
    <div class="text-center">
        <h1 style="font-size: 200px; margin: 0;">401</h1>
        <div style="font-size: 20px;">
            <h3 style="text-transform: capitalize;">
                {{ t('Unauthorized action') }}
            </h3>
            <p>
                @php
                    $defaultErrorMessage = t('Meanwhile, you may return to homepage', ['url' => url('/')]);
					$extractedMessage = null;
					
					if (isset($exception) && $exception instanceof \Throwable) {
						$extractedMessage = $exception->getMessage();
						$extractedMessage = str_replace(base_path(), '', $extractedMessage);
					}
					
					$message = !empty($extractedMessage) ? $extractedMessage : $defaultErrorMessage;
					
					echo $message;
                @endphp
            </p>
        </div>
    </div>
@endsection
