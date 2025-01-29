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
    $authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;
	
    $fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$allowedFileFormatsJson = collect(getAllowedFileFormats())->toJson();
@endphp

@section('content')
	@include('front.common.spacer')
    <div class="main-container">
        <div class="container">
            <div class="row">
    
                <div class="col-md-3 page-sidebar">
                    @include('front.account.inc.sidebar')
                </div>
                
                <div class="col-md-9 page-content">
                    <div class="inner-box">
                        <h2 class="title-2">
                            <i class="fa-solid fa-envelope"></i> {{ t('inbox') }}
                        </h2>
    
                        @if (session()->has('flash_notification'))
                            <div class="row">
                                <div class="col-12">
                                    @include('flash::message')
                                </div>
                            </div>
                        @endif
                        
                        @if (isset($errors) && $errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ t('Close') }}"></button>
                                <ul class="list list-check">
                                    @foreach($errors->all() as $error)
                                        <li class="mb-0">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
    
                        <div id="successMsg" class="alert alert-success hide" role="alert"></div>
                        <div id="errorMsg" class="alert alert-danger hide" role="alert"></div>
                        
                        <div class="inbox-wrapper">
                            <div class="row">
                                <div class="col-md-12 col-lg-12">
                                    <div class="user-bar-top">
                                        <div class="user-top">
                                            <p>
                                                <a href="{{ url('account/messages') }}">
                                                    <i class="fa-solid fa-inbox"></i>
                                                </a>&nbsp;
                                                @if ($authUserId != data_get($thread, 'p_creator.id'))
                                                    <a href="#user">
                                                        @if (isUserOnline(data_get($thread, 'p_creator')))
                                                            <i class="fa-solid fa-circle color-success"></i>&nbsp;
                                                        @endif
                                                        <strong>
                                                            <a href="{{ urlGen()->user(data_get($thread, 'p_creator')) }}">
                                                                {{ data_get($thread, 'p_creator.name') }}
                                                            </a>
                                                        </strong>
                                                    </a>
                                                @endif
                                                <strong>{{ t('Contact request about') }}</strong>
                                                <a href="{{ urlGen()->post(data_get($thread, 'post')) }}">
                                                    {{ data_get($thread, 'post.title') }}
                                                </a>
                                            </p>
                                        </div>
    
                                        <div class="message-tool-bar-right float-end call-xhr-action">
                                            <div class="btn-group btn-group-sm">
                                                @if (data_get($thread, 'p_is_important'))
                                                    <a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsNotImportant') }}"
                                                       class="btn btn-secondary markAsNotImportant"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{ t('Mark as not important') }}"
                                                    >
                                                        <i class="fa-solid fa-star"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsImportant') }}"
                                                       class="btn btn-secondary markAsImportant"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{ t('Mark as important') }}"
                                                    >
                                                        <i class="fa-regular fa-star"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ url('account/messages/' . data_get($thread, 'id') . '/delete') }}"
                                                   class="btn btn-secondary"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="{{ t('Delete') }}"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                                @if (data_get($thread, 'p_is_unread'))
                                                    <a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsRead') }}"
                                                       class="btn btn-secondary markAsRead"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{ t('Mark as read') }}"
                                                    >
                                                        <i class="fa-solid fa-envelope"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsUnread') }}"
                                                       class="btn btn-secondary markAsRead"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{ t('Mark as unread') }}"
                                                    >
                                                        <i class="fa-solid fa-envelope-open"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="border-0 bg-secondary">
                            
                            <div class="row">
                                @include('front.account.messenger.partials.sidebar')
                                
                                <div class="col-md-9 col-lg-10 chat-row">
                                    <div class="message-chat p-2 rounded">
                                        <div id="messageChatHistory" class="message-chat-history">
                                            <div id="linksMessages" class="text-center">
                                                {!! $linksRender !!}
                                            </div>
                                            
                                            @include('front.account.messenger.messages.messages')
                                            
                                        </div>
                                        
                                        <div class="type-message">
                                            <div class="type-form">
                                                @php
                                                    $updateUrl = url('account/messages/' . data_get($thread, 'id'));
                                                @endphp
                                                <form id="chatForm" role="form" method="POST" action="{{ $updateUrl }}" enctype="multipart/form-data">
                                                    {!! csrf_field() !!}
                                                    @honeypot
                                                    <input name="_method" type="hidden" value="PUT">
                                                    <textarea id="body" name="body"
                                                          maxlength="500"
                                                          rows="3"
                                                          class="input-write form-control"
                                                          placeholder="{{ t('Type a message') }}"
                                                          style="{{ (config('lang.direction')=='rtl') ? 'padding-left' : 'padding-right' }}: 75px;"
                                                    ></textarea>
                                                    <div class="button-wrap">
                                                        <input id="addFile" name="filename" type="file">
                                                        <button id="sendChat" class="btn btn-primary" type="submit">
                                                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
@endsection

@section('after_styles')
    @parent
    <link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
    @if (config('lang.direction') == 'rtl')
        <link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput-rtl.min.css') }}" rel="stylesheet">
    @endif
    @if (str_starts_with($fiTheme, 'explorer'))
        <link href="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.min.css') }}" rel="stylesheet">
    @endif
    <style>
        .file-input {
            display: inline-block;
        }
    </style>
@endsection

@section('after_scripts')
    @parent

    <script>
        var loadingImage = '{{ url('images/spinners/fading-line.gif') }}';
        var loadingErrorMessage = '{{ t('Threads could not be loaded') }}';
        var actionErrorMessage = '{{ t('This action could not be done') }}';
        var title = {
            'seen': '{{ t('Mark as read') }}',
            'notSeen': '{{ t('Mark as unread') }}',
            'important': '{{ t('Mark as important') }}',
            'notImportant': '{{ t('Mark as not important') }}',
        };
    </script>
    <script src="{{ url('assets/js/app/messenger.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/js/app/messenger-chat.js') }}" type="text/javascript"></script>
    
    <script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.js') }}" type="text/javascript"></script>
    <script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>
    
    <script>
        let options = {};
        options.theme = '{{ $fiTheme }}';
        options.language = '{{ config('app.locale') }}';
        options.rtl = {{ (config('lang.direction') == 'rtl') ? 'true' : 'false' }};
        options.allowedFileExtensions = {!! $allowedFileFormatsJson !!};
        options.minFileSize = {{ (int)config('settings.upload.min_file_size', 0) }};
        options.maxFileSize = {{ (int)config('settings.upload.max_file_size', 1000) }};
        options.browseClass = 'btn btn-primary';
        options.browseIcon = '<i class="fa-solid fa-paperclip" aria-hidden="true"></i>';
        options.layoutTemplates = {
            main1: '{browse}',
            main2: '{browse}',
            btnBrowse: '<div tabindex="500" class="{css}"{status}>{icon}</div>',
        };
        
        onDocumentReady((event) => {
            {{-- fileinput (filename) --}}
            $('#addFile').fileinput(options);
        });
    </script>
@endsection
