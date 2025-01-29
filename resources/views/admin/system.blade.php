@extends('admin.layouts.master')

@section('header')
	<div class="row page-titles">
		<div class="col-md-6 col-12 align-self-center">
			<h2 class="mb-0">
				{{ trans('admin.system_info') }}
			</h2>
		</div>
		<div class="col-md-6 col-12 align-self-center d-none d-md-flex justify-content-end">
			<ol class="breadcrumb mb-0 p-0 bg-transparent">
				<li class="breadcrumb-item"><a href="{{ admin_url() }}">{{ trans('admin.dashboard') }}</a></li>
				<li class="breadcrumb-item active d-flex align-items-center">{{ trans('admin.system') }}</li>
			</ol>
		</div>
	</div>
@endsection

@section('content')
	
	<div class="row">
		
		{{-- System Info --}}
		<div class="col-12">
			<div class="card rounded">
				<div class="card-header rounded-top">
					<h3 class="card-title"><i class="bi bi-info-circle"></i> {{ trans('admin.system') }}</h3>
				</div>
				
				<div class="card-body rounded-bottom">
					<div class="row">
						<div class="col-md-12">
							@foreach ($systemInfo as $key => $item)
								<div class="row mt-2 mb-2">
									<div class="col-xl-2 col-lg-3 col-md-3 col-4 fw-bolder">
										{!! $item['name'] !!}
									</div>
									<div class="col-xl-10 col-lg-9 col-md-9 col-8">
										{!! $item['value'] !!}
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
		
		{{-- Database Info --}}
		<div class="col-12">
			<div class="card rounded">
				<div class="card-header rounded-top">
					<h3 class="card-title"><i class="bi bi-database"></i> {{ trans('admin.database') }}</h3>
				</div>
				
				<div class="card-body rounded-bottom">
					<div class="row">
						<div class="col-md-12">
							@foreach ($databaseInfo as $key => $item)
								<div class="row mt-2 mb-2">
									<div class="col-xl-2 col-lg-3 col-md-3 col-4 fw-bolder">
										{!! $item['name'] !!}
									</div>
									<div class="col-xl-10 col-lg-9 col-md-9 col-8">
										{!! $item['value'] !!}
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
		
		{{-- Server Requirements --}}
		<div class="col-6">
			<div class="card rounded">
				<div class="card-header rounded-top">
					<h3 class="card-title"><i class="bi bi-exclamation-triangle"></i> {{ trans('messages.requirements') }}</h3>
				</div>
				
				<div class="card-body rounded-bottom pt-0 pb-0">
					<div class="row">
						<div class="col-md-12">
							<ul class="system-info">
								@foreach ($components as $key => $item)
									<li class="d-flex align-items-start">
										<div class="d-flex align-items-center">
											@if ($item['isOk'])
												<i class="bi bi-check text-success"></i>
											@else
												<i class="bi bi-x text-danger"></i>
											@endif
										</div>
										<div class="row d-flex align-items-center">
											<h5 class="col-12 title-5 fw-bolder">
												{{ $item['name'] }}
											</h5>
											<p class="col-12">
												{!! ($item['isOk']) ? $item['success'] : $item['warning'] !!}
											</p>
										</div>
									</li>
								@endforeach
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		{{-- Files Permissions --}}
		<div class="col-6">
			<div class="card rounded">
				<div class="card-header rounded-top">
					<h3 class="card-title"><i class="bi bi-folder2-open"></i> {{ trans('messages.permissions') }}</h3>
				</div>
				
				<div class="card-body rounded-bottom pt-0 pb-0">
					<div class="row">
						<div class="col-md-12">
							<ul class="system-info">
								@foreach ($permissions as $key => $item)
									<li class="d-flex align-items-start">
										<div class="d-flex align-items-center">
											@if ($item['isOk'])
												<i class="bi bi-check text-success"></i>
											@else
												<i class="bi bi-x text-danger"></i>
											@endif
										</div>
										<div class="row d-flex align-items-center">
											<h5 class="col-12 title-5 fw-bolder">
												{{ relativeAppPath($item['name']) }}
											</h5>
											<p class="col-12">
												{!! ($item['isOk']) ? $item['success'] : $item['warning'] !!}
											</p>
										</div>
									</li>
								@endforeach
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		@php
			$imageFormats ??= [];
			$gdFormats = $imageFormats['gd'] ?? [];
			$imagickFormats = $imageFormats['imagick'] ?? [];
			
			$gdDriver = \Intervention\Image\Drivers\Gd\Driver::class;
			$imagickDriver = \Intervention\Image\Drivers\Imagick\Driver::class;
			$driver = config('image.driver');
			
			$defaultDriverText = trans('admin.default_image_driver');
			
			$gbBadge = ($driver == $gdDriver) ? $defaultDriverText : '';
			$gbBadge = !empty($gbBadge) ? ' <span class="badge bg-secondary">' . $gbBadge . '</span>' : '';
			
			$imagickBadge = ($driver == $imagickDriver) ? $defaultDriverText : '';
			$imagickBadge = !empty($imagickBadge) ? ' <span class="badge bg-secondary">' . $imagickBadge . '</span>' : '';
		@endphp
		
		{{-- PHP GD Extension Formats --}}
		@if (!empty($gdFormats))
			<div class="col-6">
				<div class="card rounded">
					<div class="card-header rounded-top">
						<h3 class="card-title">
							<i class="bi bi-info-circle"></i> {!! trans('admin.image_gd_formats') . $gbBadge !!}
						</h3>
					</div>
					
					<div class="card-body rounded-bottom pt-0 pb-0">
						<div class="row">
							<div class="col-md-12">
								<ul class="system-info">
									@foreach ($gdFormats as $key => $item)
										<li class="d-flex align-items-start">
											<div class="d-flex align-items-center">
												@if ($item['isOk'])
													<i class="bi bi-check text-success"></i>
												@else
													<i class="bi bi-x text-danger"></i>
												@endif
											</div>
											<div class="row d-flex align-items-center">
												<h5 class="col-12 title-5 fw-bolder">
													{{ $item['name'] }}
												</h5>
												<p class="col-12">
													{!! ($item['isOk']) ? $item['success'] : $item['warning'] !!}
												</p>
											</div>
										</li>
									@endforeach
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endif
		
		{{-- PHP Imagick Extension Formats --}}
		@if (!empty($imagickFormats))
			<div class="col-6">
				<div class="card rounded">
					<div class="card-header rounded-top">
						<h3 class="card-title">
							<i class="bi bi-info-circle"></i> {!! trans('admin.image_imagick_formats') . $imagickBadge !!}
						</h3>
					</div>
					
					<div class="card-body rounded-bottom pt-0 pb-0">
						<div class="row">
							<div class="col-md-12">
								<ul class="system-info">
									@foreach ($imagickFormats as $key => $item)
										<li class="d-flex align-items-start">
											<div class="d-flex align-items-center">
												@if ($item['isOk'])
													<i class="bi bi-check text-success"></i>
												@else
													<i class="bi bi-x text-danger"></i>
												@endif
											</div>
											<div class="row d-flex align-items-center">
												<h5 class="col-12 title-5 fw-bolder">
													{{ $item['name'] }}
												</h5>
												<p class="col-12">
													{!! ($item['isOk']) ? $item['success'] : $item['warning'] !!}
												</p>
											</div>
										</li>
									@endforeach
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endif
		
	</div>

@endsection

@section('after_styles')
	@parent
	<style>
		/* SYSTEM INFO */
		ul.system-info {
			padding-left: 0;
		}
		ul.system-info li:first-child {
			border-top: 0;
			padding-top: 20px;
		}
		ul.system-info li:last-child {
			border-bottom: 0;
			margin-bottom: 0;
		}
		ul.system-info li {
			border-bottom: 1px solid #ddd;
			clear: both;
			list-style: outside none none;
			margin-bottom: 20px;
		}
		ul.system-info li i {
			color: #7324bc;
			float: left;
			font-size: 30px;
			max-height: 70px;
			margin-right: 20px;
			margin-top: 5px;
		}
	</style>
@endsection

@section('after_scripts')
	@parent
@endsection
