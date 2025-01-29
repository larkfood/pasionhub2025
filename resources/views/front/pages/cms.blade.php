{{--
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
--}}
@extends('front.layouts.master')

@php
	$page ??= [];
@endphp

@section('search')
	@parent
    @include('front.pages.cms.intro')
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container inner-page">
		<div class="container">
			<div class="section-content">
				<div class="row">
                    
                    @if (empty(data_get($page, 'image_path')))
                        <h1 class="text-center title-1" style="color: {!! data_get($page, 'name_color') !!};">
							<strong>{{ data_get($page, 'name') }}</strong>
						</h1>
                        <hr class="center-block small mt-0" style="background-color: {!! data_get($page, 'name_color') !!};">
                    @endif
                    
					<div class="col-12 page-content">
						<div class="inner-box relative">
							<div class="row">
								<div class="col-12 page-content">
                                    @if (empty(data_get($page, 'image_path')))
									    <h3 class="text-center" style="color: {!! data_get($page, 'title_color') !!};">
										    {{ data_get($page, 'title') }}
									    </h3>
                                    @endif
									<div class="text-content text-start from-wysiwyg">
										{!! data_get($page, 'content') !!}
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>

				@include('front.layouts.inc.social.horizontal')

			</div>
		</div>
	</div>
@endsection

@section('info')
@endsection
