@extends('errors::layout')

@php
	// Get page error title
	$titleKey = 'global.error_http_404_title';
	$title = trans($titleKey);
	if ($title === $titleKey) {
		$title = 'Page not found';
	}
	
	// Get page error message
	$messageKey = 'global.error_http_404_message';
	$message = trans($messageKey, ['url' => url('/')]);
	if ($message === $messageKey) {
		if (isset($exception) && $exception instanceof \Throwable) {
			$message = $exception->getMessage();
			$message = str_replace(base_path(), '', $message);
		}
	}
@endphp

@section('title', $title)

@section('message')
	<div class="text-center">
		<h1 style="font-size: 200px; margin: 0;">404</h1>
		<div style="font-size: 20px;">
			<h3 style="text-transform: capitalize;">
				{{ $title }}
			</h3>
			<p>
				{!! $message !!}
			</p>
		</div>
	</div>
@endsection
