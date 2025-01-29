@php
	use App\Http\Controllers\Web\Setup\Install\RequirementsController;
	
	$navItems ??= [];
@endphp
<ul class="nav nav-pills justify-content-center install-steps">
	@forelse($navItems as $key => $item)
		@php
			$itemParentClass = data_get($item, 'parentClass');
			$itemParentClass = !empty($itemParentClass) ? ' ' . $itemParentClass : '';
			
			$itemClass = data_get($item, 'class');
			$itemClass = !empty($itemClass) ? ' ' . $itemClass : '';
			
			$itemUrl = data_get($item, 'url');
			if ($key == RequirementsController::class) {
				$itemUrl = urlQuery($itemUrl)->setParameters(['mode' => 'manual'])->toString();
			}
			
			$itemIcon = data_get($item, 'icon');
			$itemLabel = data_get($item, 'label');
		@endphp
		<li class="nav-item{{ $itemParentClass }}">
			<a class="nav-link{{ $itemClass }}" href="{{ $itemUrl }}">
				<i class="{{ $itemIcon }}"></i> {{ $itemLabel }}
			</a>
		</li>
	@empty
		<div class="col-12">
			<div class="alert alert-danger">
				The navigation bar could not be loaded.
				Please report this issue to us <a href="https://support.laraclassifier.com/hc/tickets/new" target="_blank">here</a>.
			</div>
		</div>
	@endforelse
</ul>
