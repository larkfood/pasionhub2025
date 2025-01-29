@php
	$selectedPackage ??= null;
	$paymentMethods ??= collect();
	
	$packageId = request()->query('packageId');
	$noPackageOrPremiumOneSelected ??= true;
@endphp
@if (!empty($selectedPackage) && $paymentMethods->count() > 0 && $noPackageOrPremiumOneSelected)
	
	<table id="packagesTable" class="table table-hover checkboxtable mb-0">
		<tr class="hide">
			<td class="text-start align-middle p-3">
				@php
					$packageCheckedAttr = (old('package_id', $packageId) == $selectedPackage->id)
											? ' checked'
											: (($selectedPackage->price == 0) ? ' checked' : '');
					$packageIdError = (isset($errors) && $errors->has('package_id')) ? ' is-invalid' : '';
				@endphp
				<div class="form-check">
					<input class="form-check-input package-selection"
						   type="radio"
						   name="package_id"
						   id="packageId-{{ $selectedPackage->id }}"
						   value="{{ $selectedPackage->id }}"
						   data-name="{{ $selectedPackage->name }}"
						   data-currency-symbol="{{ $selectedPackage->currency->symbol }}"
						   data-currency-in-left="{{ $selectedPackage->currency->in_left }}"
							{{ $packageCheckedAttr }}
					>
					<label class="form-check-label mb-0{{ $packageIdError }}">
						<strong class=""
						        data-bs-placement="right"
						        data-bs-toggle="tooltip"
						        title="{!! $selectedPackage->description_string !!}"
						>{!! $selectedPackage->name !!} </strong>
					</label>
				</div>
			</td>
			<td class="text-end align-middle p-3">
				<p id="price-{{ $selectedPackage->id }}">
					@if ($selectedPackage->currency->in_left == 1)
						<span class="price-currency">{!! $selectedPackage->currency->symbol !!}</span>
					@endif
					<span class="price-int">{{ $selectedPackage->price }}</span>
					@if ($selectedPackage->currency->in_left == 0)
						<span class="price-currency">{!! $selectedPackage->currency->symbol !!}</span>
					@endif
				</p>
			</td>
		</tr>
		<tr>
			<td class="text-start align-middle p-3 border-top-0">
				@include('front.payment.payment-methods')
			</td>
			<td class="text-end align-middle p-3 border-top-0">
				<p class="mb-0">
					<strong>
						{{ t('Payable Amount') }}:
						<span class="price-currency amount-currency currency-in-left" style="display: none;"></span>
						<span class="payable-amount">0</span>
						<span class="price-currency amount-currency currency-in-right" style="display: none;"></span>
					</strong>
				</p>
			</td>
		</tr>
	</table>
	
	@include('front.payment.payment-methods.plugins')
	
@endif
