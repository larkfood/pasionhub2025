@php
	$categories ??= [];
	$isCountPostsEnabled = (config('settings.listings_list.count_categories_listings') == '1');
	$isShowingCategoryIconEnabled = in_array(config('settings.listings_list.show_category_icon'), [2, 6, 7, 8]);
@endphp
@if (!empty($categories))
	<div class="row row-cols-lg-6 row-cols-md-4 row-cols-sm-3 row-cols-2">
		@foreach($categories as $cat)
			@php
				$catId = data_get($cat, 'id', 0);
				$catIconClass = $isShowingCategoryIconEnabled ? data_get($cat, 'icon_class', 'fa-solid fa-folder') : '';
				$catIcon = !empty($catIconClass) ? '<i class="' . $catIconClass . '"></i>' : '';
				$catName = data_get($cat, 'name', '--');
				
				$catCountPosts = $isCountPostsEnabled
					? '(' . ($countPostsPerCat[$catId]['total'] ?? 0) . ')'
					: '';
				$catDisplayName = !empty($catCountPosts) ? $catName . ' ' . $catCountPosts : $catName;
			@endphp
			<div class="col f-category">
				<a href="{{ urlGen()->category($cat) }}">
					{!! $catIcon !!}
					<h6>{{ $catDisplayName }}</h6>
				</a>
			</div>
		@endforeach
	</div>
@endif
