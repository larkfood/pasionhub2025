@extends('admin.layouts.master')

@section('after_styles')
    {{-- Ladda Buttons (loading buttons) --}}
    <link href="{{ asset('assets/plugins/ladda/ladda-themeless.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('header')
	<div class="row page-titles">
		<div class="col-md-6 col-12 align-self-center">
			<h3 class="mb-0">
				{{ trans('admin.Plugins') }}
			</h3>
		</div>
		<div class="col-md-6 col-12 align-self-center d-none d-md-flex justify-content-end">
			<ol class="breadcrumb mb-0 p-0 bg-transparent">
				<li class="breadcrumb-item"><a href="{{ admin_url() }}">{{ trans('admin.dashboard') }}</a></li>
				<li class="breadcrumb-item active d-flex align-items-center">{{ trans('admin.Plugins') }}</li>
			</ol>
		</div>
	</div>
@endsection

@section('content')
    {{-- Default box --}}
	<div class="row">
		<div class="col-12">
			
			<div class="card rounded">
				<div class="card-header">
					<h3>{{ trans('admin.Existing plugins') }}</h3>
				</div>
				
				<div class="card-body">
					<table class="table table-hover table-condensed">
						<thead>
						<tr>
							<th>#</th>
							<th>{{ trans('admin.Name') }}</th>
							<th>{{ trans('admin.Description') }}</th>
							<th class="text-end">{{ trans('admin.Version') }}</th>
							<th class="text-end">{{ mb_ucfirst(trans('admin.options')) }}</th>
							<th class="text-end">{{ trans('admin.actions') }}</th>
						</tr>
						</thead>
						<tbody>
						@foreach ($plugins as $key => $plugin)
							@php
								$isCodeRequired = !empty($plugin->item_id);
								$installWithoutCodeUrl = admin_url('plugins/' . $plugin->name . '/install');
								$installWithCodeUrl = admin_url('plugins/' . $plugin->name . '/install/code');
								$installUrl = $isCodeRequired ? $installWithCodeUrl : $installWithoutCodeUrl;
								$uninstallUrl = admin_url('plugins/' . $plugin->name . '/uninstall');
								$deleteUrl = admin_url('plugins/' . $plugin->name . '/delete');
							@endphp
							<tr>
								<th scope="row">{{ $loop->iteration }}</th>
								<td>{!! $plugin->formatted_name !!}</td>
								<td>{{ $plugin->description }}</td>
								<td class="text-end">{{ $plugin->version }}</td>
								<td class="text-end">
									@if ($plugin->has_installer)
										@if ($plugin->installed && $plugin->activated)
											@if (!empty($plugin->options))
												@foreach($plugin->options as $option)
													@continue(!isset($option->url))
													@php
														$opBtnClass = !empty($option->btnClass) ? $option->btnClass : 'btn-light';
														$opIconClass = !empty($option->iClass) ? $option->iClass : 'fa-solid fa-gear';
														$opName = !empty($option->name) ? $option->name : trans('admin.Configure');
													@endphp
													<a class="btn btn-xs {{ $opBtnClass }}" href="{{ $option->url }}">
														<i class="{{ $opIconClass }}"></i> {{ $opName }}
													</a>
												@endforeach
											@else
												-
											@endif
										@else
											-
										@endif
									@endif
								</td>
								<td class="text-end">
									@if ($plugin->is_compatible)
										@if ($plugin->has_installer)
											@if ($plugin->installed)
												@if ($plugin->activated)
													<a class="btn btn-xs btn-success btn-uninstall confirm-simple-action" href="{{ $uninstallUrl }}">
														<i class="fa-solid fa-toggle-on"></i> {{ trans('admin.Uninstall') }}
													</a>
												@else
													<a class="btn btn-xs btn-danger btn-install"
													   data-name="{!! $plugin->display_name !!}"
													   data-is-code-required="{{ (int)$isCodeRequired }}"
													   href="{{ $installUrl }}"
													>
														<i class="fa-solid fa-lock"></i> {{ trans('admin.Activate') }}
													</a>
													<a class="btn btn-xs btn-warning btn-uninstall confirm-simple-action" href="{{ $uninstallUrl }}">
														<i class="fa-solid fa-toggle-on"></i> {{ trans('admin.Uninstall') }}
													</a>
												@endif
											@else
												<a class="btn btn-xs btn-light btn-install"
												   data-name="{!! $plugin->display_name !!}"
												   data-is-code-required="{{ (int)$isCodeRequired }}"
												   href="{{ $installUrl }}"
												>
													<i class="fa-solid fa-toggle-off"></i> {{ trans('admin.Install') }}
												</a>
											@endif
										@endif
									@else
										@php
											$toolTip = 'data-bs-toggle="tooltip" title="' . $plugin->compatibility_hint . '"';
										@endphp
										<a class="btn btn-xs btn-warning" href="#" {!! $toolTip !!}>
											<i class="fa-solid fa-triangle-exclamation"></i> {{ $plugin->compatibility ?? '--' }}
										</a>
									@endif
									{{--
									<a class="btn btn-xs btn-danger" data-button-type="delete" href="{{ $deleteUrl }}">
										<i class="fa-regular fa-trash-can"></i> {{ trans('admin.delete') }}
									</a>
									--}}
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>

        </div>
    </div>
	
	{{-- Install using purchase code --}}
	<div class="modal fade" id="purchaseCodeModal" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				
				<div class="modal-header">
					<h4 class="modal-title">{{ trans('admin.Plugin') }}</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ t('Close') }}"></button>
				</div>
				
				<form role="form" method="POST" action="">
					{!! csrf_field() !!}
				
					<div class="modal-body">
					
						@if (isset($errors) && $errors->any() && old('purchaseCodeForm')=='1')
							<div class="alert alert-danger">
								<ul class="list list-check mb-0">
									@foreach($errors->all() as $error)
										<li>{!! $error !!}</li>
									@endforeach
								</ul>
							</div>
						@endif
						
						{{-- purchase_code --}}
						@php
							$purchaseCodeError = (isset($errors) && $errors->has('purchase_code_valid')) ? ' is-invalid' : '';
						@endphp
						<div class="mb-3 required{{ $purchaseCodeError }}">
							<label for="purchase_code" class="control-label">{{ trans('admin.Purchase Code') }}</label>
							<input id="purchaseCode"
							       name="purchase_code"
							       class="form-control required"
							       placeholder="{{ trans('admin.purchase_code_placeholder') }}"
							       value="{{ old('purchase_code') }}"
							>
							<div class="form-text">
								{!! trans('admin.find_my_purchase_code', ['purchaseCodeFindingUrl' => config('larapen.core.purchaseCodeFindingUrl')]) !!}
							</div>
						</div>
						
						<input type="hidden" name="displayName">
						<input type="hidden" name="installUrl">
						<input type="hidden" name="purchaseCodeForm" value="1">
					</div>
					
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary" id="btnSubmit">{{ trans('admin.Install') }}</button>
						<button type="button" class="btn btn-light float-start" data-bs-dismiss="modal">{{ t('Close') }}</button>
					</div>
				</form>
				
			</div>
		</div>
	</div>

@endsection

@section('after_scripts')
    {{-- Ladda Buttons (loading buttons) --}}
    <script src="{{ asset('assets/plugins/ladda/spin.js') }}"></script>
    <script src="{{ asset('assets/plugins/ladda/ladda.js') }}"></script>
	
    <script>
	    onDocumentReady((event) => {
        	
        	/* Installation: Display the Purchase Code Form */
			const installBtnEls = document.querySelectorAll('.btn-install');
			if (installBtnEls.length > 0) {
				installBtnEls.forEach((element) => {
					element.addEventListener('click', (e) => {
						e.preventDefault(); /* Prevents submission or reloading */
						
						const thisEl = (e.target.tagName.toLowerCase() === 'i')
							? e.target.parentElement
							: e.target;
						
						/* Clear form existing data */
						clearFormData();
						
						/* Retrieve form data */
						let displayName = thisEl.dataset.name;
						let installUrl = thisEl.getAttribute('href');
						let isCodeRequired = thisEl.dataset.isCodeRequired;
						isCodeRequired = (isCodeRequired === 1 || isCodeRequired === '1');
						
						if (isCodeRequired) {
							return showInstallationForm(displayName, installUrl);
						} else {
							Swal.fire({
								text: langLayout.confirm.message.question,
								icon: 'warning',
								showCancelButton: true,
								confirmButtonText: langLayout.confirm.button.yes,
								cancelButtonText: langLayout.confirm.button.no
							}).then((result) => {
								if (result.isConfirmed) {
									
									redirect(installUrl);
									
								} else if (result.dismiss === Swal.DismissReason.cancel) {
									jsAlert(langLayout.confirm.message.cancel, 'info');
								}
							});
						}
						
						return false;
					});
				});
			}
			
		    /* Installation: Submit the Purchase Code Form */
			const submitBtnEl = document.getElementById('btnSubmit');
			if (submitBtnEl) {
				submitBtnEl.addEventListener('click', (e) => {
					e.preventDefault(); /* Prevents submission or reloading */
					
					const formEl = document.querySelector('#purchaseCodeModal form');
					if (formEl) {
						formEl.submit();
					}
					
					return false;
				});
			}
			
	        @if (isset($errors) && $errors->any())
				@if ($errors->any() && old('purchaseCodeForm')=='1')
					let displayName = '{!! old('displayName') !!}';
					let installUrl = '{!! old('installUrl') !!}';
					showInstallationForm(displayName, installUrl);
				@endif
			@endif
			
        });
	    
	    function showInstallationForm(displayName, installUrl) {
		    /* Set the modal title */
			const modalTitleEl = document.querySelector('#purchaseCodeModal h4.modal-title');
			if (modalTitleEl) {
				modalTitleEl.innerHTML = displayName;
			}
		    /* Set the displayName input value */
		    const displayNameEl = document.querySelector('#purchaseCodeModal [name="displayName"]');
			if (displayNameEl) {
				displayNameEl.value = displayName;
			}
		    /* Set the form action attribute */
		    const formEl = document.querySelector('#purchaseCodeModal form');
			if (formEl) {
				formEl.setAttribute('action', installUrl);
			}
		    /* Set the installUrl input value */
		    const installUrlEl = document.querySelector('#purchaseCodeModal [name="installUrl"]');
			if (installUrlEl) {
				installUrlEl.value = installUrl;
			}
		    
		    /* Open Modal */
		    const modalEl = document.getElementById('purchaseCodeModal');
			if (modalEl) {
				const modal = new bootstrap.Modal(modalEl, {});
				modal.show();
			}
		
		    return false;
	    }
	    
	    function clearFormData() {
		    /* Clear the modal title */
		    const modalTitleEl = document.querySelector('#purchaseCodeModal h4.modal-title');
			if (modalTitleEl) {
				modalTitleEl.innerHTML = '';
			}
		    /* Clear the displayName input value */
		    const displayNameEl = document.querySelector('#purchaseCodeModal [name="displayName"]');
			if (displayNameEl) {
				displayNameEl.value = '';
			}
		    /* Clear the form action attribute */
		    const formEl = document.querySelector('#purchaseCodeModal form');
			if (formEl) {
				formEl.setAttribute('action', '');
			}
		    /* Clear the installUrl input value */
		    const installUrlEl = document.querySelector('#purchaseCodeModal [name="installUrl"]');
			if (installUrlEl) {
				installUrlEl.value = '';
			}
		    /* Clear and hide the alert message */
		    const alertEl = document.querySelector('#purchaseCodeModal .alert.alert-danger');
			if (alertEl) {
				alertEl.innerHTML = '';
				alertEl.style.display = 'none';
			}
		    /* Clear the purchase code input value */
		    const purchaseCodeField = document.querySelector('#purchaseCodeModal [name="purchase_code"]');
			if (purchaseCodeField) {
				purchaseCodeField.value = '';
				
				/* Remove the is-invalid class from the parent div */
				const parentPurchaseCodeField = purchaseCodeField.closest('div.input-group');
				if (parentPurchaseCodeField) {
					parentPurchaseCodeField.classList.remove('is-invalid');
				}
			}
	    }
    </script>
@endsection
