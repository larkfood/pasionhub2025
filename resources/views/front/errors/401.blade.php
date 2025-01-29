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
@extends('front.errors.layouts.master')

@section('title', t('Unauthorized action'))

@section('search')
    @parent
    @include('front.errors.layouts.inc.search')
@endsection

@section('content')
    @include('front.common.spacer')
    <div class="main-container inner-page">
        <div class="container">
            <div class="section-content">
                <div class="row">

                    <div class="col-md-12 page-content">
                        
                        <div class="error-page mt-5 mb-5 ms-0 me-0 pt-5">
                            <h1 class="headline text-center" style="font-size: 180px;">401</h1>
                            <div class="text-center m-l-0 mt-5">
                                <h3 class="m-t-0 color-danger">
                                    <i class="fa-solid fa-triangle-exclamation"></i> {{ t('Unauthorized action') }}
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
                        
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
