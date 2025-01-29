@extends('errors::layout')

@section('title', trans('admin.Service Unavailable'))

@php
	$data = [];
	
	$messageFilePath = storage_path('framework/down-message');
	if (file_exists($messageFilePath)) {
		$buffer = file_get_contents($messageFilePath);
		$data = json_decode($buffer, true);
	}
	
	$message = $data['message'] ?? trans('admin.Be right back');
@endphp

@section('message')
	{!! $message !!}
@endsection
