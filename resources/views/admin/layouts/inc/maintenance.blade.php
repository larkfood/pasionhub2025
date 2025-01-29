<div class="modal fade" id="maintenanceMode">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">{{ trans('admin.Maintenance Mode') }}</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ t('Close') }}"></button>
			</div>
			
			<form role="form" method="POST" action="{{ admin_url('actions/maintenance/down') }}">
				{!! csrf_field() !!}
				
				<div class="modal-body">
					
					@if (isset($errors) && $errors->any() && old('maintenanceForm')=='1')
						<div class="alert alert-danger ms-0 me-0 mb-5">
							@foreach ($errors->all() as $error)
								{{ $error }}<br>
							@endforeach
						</div>
					@endif
					
					@php
						if (isset($errors)) {
							$messageHasError = $errors->has('message');
							$messageRowError = $messageHasError ? ' has-danger' : '';
							$messageFieldError = $messageHasError ? ' form-control-danger' : '';
							$messageError = $errors->first('message');
						}
					@endphp
					<div class="form-group required{{ $messageRowError ?? '' }}">
						<label for="message" class="control-label">
							{{ t('Message') }} <span class="text-count">({{ t('number_max', ['number' => 500]) }})</span>
						</label>
						<textarea id="message"
						          name="message"
						          class="form-control required{{ $messageFieldError ?? '' }}"
						          placeholder="{{ trans('admin.Be right back') }}"
						          rows="3"
						>{{ old('message') }}</textarea>
					</div>
					@if (isset($messageHasError) && $messageHasError)
						<div class="invalid-feedback">{{ $messageError ?? '' }}</div>
					@endif
					
					<input type="hidden" name="maintenanceForm" value="1">
				</div>
				
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">{{ trans('admin.Put in Maintenance Mode') }}</button>
					<button type="button" class="btn btn-light float-start" data-bs-dismiss="modal">{{ t('Close') }}</button>
				</div>
			</form>
		</div>
	</div>
</div>
