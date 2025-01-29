@if ($xPanel->hasAccess('delete'))
	<a href="{{ url($xPanel->route.'/'.$entry->getKey()) }}" class="btn btn-xs btn-danger" data-button-type="delete">
		<i class="fa-regular fa-trash-can"></i> {{ trans('admin.delete') }}
	</a>
@endif
