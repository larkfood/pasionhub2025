@if ($xPanel->hasAccess('revisions') && count($entry->revisionHistory))
    <a href="{{ url($xPanel->route.'/'.$entry->getKey().'/revisions') }}" class="btn btn-xs btn-secondary">
        <i class="fa-solid fa-clock-rotate-left"></i> {{ trans('admin.revisions') }}
    </a>
@endif
