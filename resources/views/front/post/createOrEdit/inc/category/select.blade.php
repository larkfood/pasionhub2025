@php
	$catDisplayType ??= 'c_bigIcon_list';
	
	$apiResult ??= [];
	$totalCategories = (int)data_get($apiResult, 'meta.total', 0);
	$areCategoriesPageable = (!empty(data_get($apiResult, 'links.prev')) || !empty(data_get($apiResult, 'links.next')));
	
	$categories ??= [];
	$category ??= null;
	$hasChildren ??= false;
	$catId ??= 0; /* The selected category ID */
@endphp
@if (!$hasChildren)
	
	{{-- To append in the form (will replace the category field) --}}
	
	@if (!empty($category))
		@if (!empty(data_get($category, 'children')))
			<a href="#browseCategories" data-bs-toggle="modal" class="cat-link" data-id="{{ data_get($category, 'id') }}">
				{{ data_get($category, 'name') }}
			</a>
		@else
			{{ data_get($category, 'name') }}&nbsp;
			[ <a href="#browseCategories"
				 data-bs-toggle="modal"
				 class="cat-link"
				 data-id="{{ data_get($category, 'parent.id', 0) }}"
			><i class="fa-regular fa-pen-to-square"></i> {{ t('Edit') }}</a> ]
		@endif
	@else
		<a href="#browseCategories" data-bs-toggle="modal" class="cat-link" data-id="0">
			{{ t('select_a_category') }}
		</a>
	@endif
	
@else
	
	{{-- To append in the modal (will replace the modal content) --}}

	@if (!empty($category))
		<p>
			<a href="#" class="btn btn-sm btn-success cat-link" data-id="{{ data_get($category, 'parent_id') }}">
				<i class="fa-solid fa-reply"></i> {{ t('go_to_parent_categories') }}
			</a>&nbsp;
			<strong>{{ data_get($category, 'name') }}</strong>
		</p>
		<div style="clear:both"></div>
	@endif
	
	@if (!empty($categories))
		<div class="col-12 content-box layout-section">
			@if ($catDisplayType == 'c_picture_list')
				
				<div id="modalCategoryList" class="row row-cols-lg-6 row-cols-md-4 row-cols-sm-3 row-cols-2 row-featured row-featured-category">
					@foreach($categories as $key => $cat)
						@php
							$_hasChildren = (!empty(data_get($cat, 'children'))) ? 1 : 0;
							$_parentId = data_get($cat, 'parent.id', 0);
							$_hasLink = (data_get($cat, 'id') != $catId || $_hasChildren == 1);
						@endphp
						<div class="col f-category">
							@if ($_hasLink)
								<a href="#" class="cat-link"
								   data-id="{{ data_get($cat, 'id') }}"
								   data-parent-id="{{ $_parentId }}"
								   data-has-children="{{ $_hasChildren }}"
								   data-type="{{ data_get($cat, 'type') }}"
								>
							@endif
								<img src="{{ data_get($cat, 'image_url') }}" class="lazyload img-fluid" alt="{{ data_get($cat, 'name') }}">
								<h6 class="{{ !$_hasLink ? 'text-secondary' : '' }}">
									{{ data_get($cat, 'name') }}
								</h6>
							@if ($_hasLink)
								</a>
							@endif
						</div>
					@endforeach
				</div>
			
			@elseif ($catDisplayType == 'c_bigIcon_list')
			
				<div id="modalCategoryList" class="row row-cols-lg-6 row-cols-md-4 row-cols-sm-3 row-cols-2 row-featured row-featured-category">
					@foreach($categories as $key => $cat)
						@php
							$_hasChildren = (!empty(data_get($cat, 'children'))) ? 1 : 0;
							$_parentId = data_get($cat, 'parent.id', 0);
							$_hasLink = (data_get($cat, 'id') != $catId || $_hasChildren == 1);
						@endphp
						<div class="col f-category">
							@if ($_hasLink)
								<a href="#" class="cat-link"
								   data-id="{{ data_get($cat, 'id') }}"
								   data-parent-id="{{ $_parentId }}"
								   data-has-children="{{ $_hasChildren }}"
								   data-type="{{ data_get($cat, 'type') }}"
								>
							@endif
								@if (in_array(config('settings.listings_list.show_category_icon'), [2, 6, 7, 8]))
									<i class="{{ data_get($cat, 'icon_class') ?? 'fa-solid fa-folder' }}"></i>
								@endif
								<h6 class="{{ !$_hasLink ? 'text-secondary' : '' }}">
									{{ data_get($cat, 'name') }}
								</h6>
							@if ($_hasLink)
								</a>
							@endif
						</div>
					@endforeach
				</div>
				
			@else
				
				@php
					$isShowingCategoryIconEnabled = in_array(config('settings.listings_list.show_category_icon'), [2, 6, 7, 8]);
					
					$listTab = ['c_border_list' => 'list-border'];
					$catListClass = (isset($listTab[$catDisplayType])) ? 'list ' . $listTab[$catDisplayType] : 'list';
					$catListClass = !empty($catListClass) ? ' ' . $catListClass : '';
				@endphp
				<div class="list-categories">
					<ul id="modalCategoryList" class="row row-cols-lg-3 row-cols-md-2 row-cols-sm-1 row-cols-1{{ $catListClass }} my-4">
						@foreach ($categories as $key => $cat)
							@php
								$_itemClass = (count($categories) == $key + 1) ? ' cat-list-border' : '';
								
								$_catId = data_get($cat, 'id', 0);
								$_catIconClass = $isShowingCategoryIconEnabled ? data_get($cat, 'icon_class', 'fa-solid fa-check') : '';
								$_catIcon = !empty($_catIconClass) ? '<i class="' . $_catIconClass . '"></i> ' : '';
								$_catName = data_get($cat, 'name', '--');
								$_catType = data_get($cat, 'type');
								
								$_hasChildren = !empty(data_get($cat, 'children')) ? 1 : 0;
								$_parentId = data_get($cat, 'parent.id', 0);
								$_hasLink = ($_catId != $catId || $_hasChildren == 1);
								$_hasLinkClass = !$_hasLink ? ' text-secondary fw-bold' : '';
							@endphp
							<li class="col cat-list{{ $_itemClass . $_hasLinkClass }} mb-0 px-4">
								<span>
									{!! $_catIcon !!}
									@if ($_hasLink)
										<a href="#" class="cat-link"
										   data-id="{{ $_catId }}"
										   data-parent-id="{{ $_parentId }}"
										   data-has-children="{{ $_hasChildren }}"
										   data-type="{{ $_catType }}"
										>
									@endif
										{{ $_catName }}
									@if ($_hasLink)
										</a>
									@endif
								</span>
							</li>
						@endforeach
					</ul>
				</div>
			
			@endif
		</div>
		@if ($totalCategories > 0 && $areCategoriesPageable)
			<br>
			@include('vendor.pagination.api.bootstrap-4')
		@endif
	@else
		{{ $apiMessage ?? t('no_categories_found') }}
	@endif
@endif

@section('before_scripts')
	@parent
@endsection
