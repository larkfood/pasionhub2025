@php
	$categories ??= [];
	$isCountPostsEnabled = (config('settings.listings_list.count_categories_listings') == '1');
	$isShowingCategoryIconEnabled = in_array(config('settings.listings_list.show_category_icon'), [2, 6, 7, 8]);
	
	$catDisplayType ??= 'c_normal_list';
	
	$listTab = ['c_border_list' => 'list-border'];
	$catListClass = isset($listTab[$catDisplayType]) ? 'list ' . $listTab[$catDisplayType] : 'list';
	$catListClass = !empty($catListClass) ? ' ' . $catListClass : '';
@endphp
@if (!empty($categories))
	<div class="list-categories">
		<ul class="row row-cols-lg-3 row-cols-md-2 row-cols-sm-1 row-cols-1{{ $catListClass }} my-4">
			@foreach ($categories as $key => $cat)
				@php
					$itemClass = (count($categories) == $key + 1) ? ' cat-list-border' : '';
					
					$catId = data_get($cat, 'id', 0);
					$catIconClass = $isShowingCategoryIconEnabled ? data_get($cat, 'icon_class', 'fa-solid fa-check') : '';
					$catIcon = !empty($catIconClass) ? '<i class="' . $catIconClass . '"></i> ' : '';
					$catName = data_get($cat, 'name', '--');
					
					$catCountPosts = $isCountPostsEnabled
						? ' (' . ($countPostsPerCat[$catId]['total'] ?? 0) . ')'
						: '';
				@endphp
				<li class="col cat-list{{ $itemClass }} mb-0 px-4">
					<span>
						{!! $catIcon !!}<a href="{{ urlGen()->category($cat) }}">{{ $catName }}</a>{{ $catCountPosts }}
					</span>
				</li>
			@endforeach
		</ul>
	</div>
@endif
