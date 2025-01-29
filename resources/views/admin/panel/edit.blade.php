@extends('admin.layouts.master')

@php
    $editUri = $xPanel->route . '/' . $entry->getKey() . '/edit';
	
    $modelTable = $xPanel->getModel()->getTable();
	$settingsTables = ['settings', 'sections', 'domain_settings', 'domain_sections'];
	$isSettingsModel = in_array($modelTable, $settingsTables);
    $isNotSettingsModel = !$isSettingsModel;
@endphp
@section('header')
    <div class="row page-titles">
        <div class="col-md-5 col-12 align-self-center">
            <h2 class="mb-0">
                <span class="text-capitalize">{!! $xPanel->entityNamePlural !!}</span>
                <small>{{ trans('admin.edit') }} {!! $xPanel->entityName !!}</small>
            </h2>
        </div>
        <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
            <ol class="breadcrumb mb-0 p-0 bg-transparent">
                <li class="breadcrumb-item"><a href="{{ admin_url() }}">{{ trans('admin.dashboard') }}</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ url($xPanel->route) }}" class="text-capitalize">{!! $xPanel->entityNamePlural !!}</a>
                </li>
                <li class="breadcrumb-item active d-flex align-items-center">{{ trans('admin.edit') }}</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex-row d-flex justify-content-center">
        @php
            $colMd = config('settings.style.admin_boxed_layout') == '1' ? ' col-md-12' : ' col-md-9';
			$settingsClass = $isSettingsModel ? ' settings-edition' : '';
        @endphp
        <div class="col-sm-12{{ $colMd }}">
            <div class="row">
                <div class="col-lg-6">
                    @if ($xPanel->hasAccess('list'))
                        <a href="{{ url($xPanel->route) }}" class="btn btn-primary shadow">
                            <i class="fa-solid fa-angles-left"></i> {{ trans('admin.back_to_all') }}
                            <span class="text-lowercase">{{-- $xPanel->entityNamePlural --}}</span>
                        </a>
                        <br><br>
                    @endif
                </div>
                <div class="col-lg-6 text-end">
                    @if ($xPanel->model->translationEnabled())
                        @php
                            $availableLocales = $xPanel->model->getAvailableLocales();
                            $appLocale = app()->getLocale();
                            $selectedLocale = $availableLocales[request()->input('locale', $appLocale)] ?? $appLocale;
                        @endphp
                        <div class="btn-group">
                            <button type="button"
                                    class="btn btn-primary shadow dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                            >
                                {{ trans('admin.Language') }}: {{ $selectedLocale }} &nbsp;<span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                @foreach ($availableLocales as $key => $locale)
                                    <a class="dropdown-item ps-3 pe-3 pt-1 pb-1" href="{{ url($editUri) }}?locale={{ $key }}">
                                        {{ $locale }}
                                    </a>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            
            @php
                $updateUrl = url($xPanel->route . '/' . $entry->getKey());
            @endphp
            @if ($xPanel->hasUploadFields('update', $entry->getKey()))
                {{ html()->form('PUT', $updateUrl)->acceptsFiles()->attribute('novalidate', true)->open() }}
            @else
                {{ html()->form('PUT', $updateUrl)->attribute('novalidate', true)->open() }}
            @endif
            <div class="card border-top border-primary{{ $settingsClass }}">
                
                @if ($isNotSettingsModel)
                    <div class="card-header">
                        <h3 class="mb-0">{{ trans('admin.edit') }}</h3>
                    </div>
				@endif
                <div class="card-body">
                    {{-- load the view from the application if it exists, otherwise load the one in the package --}}
                    @php
                        $form = 'update';
                    @endphp
                    @if (view()->exists('vendor.admin.panel.' . $xPanel->entityName . '.form_content'))
                        @include('vendor.admin.panel.' . $xPanel->entityName . '.form_content', [
							'form'   => $form,
			                'fields' => $xPanel->getFields($form, $entry->getKey())
                        ])
                    @elseif (view()->exists('vendor.admin.panel.form_content'))
                        @include('vendor.admin.panel.form_content', [
							'form'   => $form,
							'fields' => $xPanel->getFields($form, $entry->getKey())
                        ])
                    @else
                        @include('admin.panel.form_content', [
							'form'   => $form,
							'fields' => $xPanel->getFields($form, $entry->getKey())
                        ])
                    @endif
                </div>
                <div class="card-footer">
					@include('admin.panel.inc.form_save_buttons')
                </div>
                
            </div>
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

@section('after_styles')
@endsection

@section('after_scripts')
@endsection
