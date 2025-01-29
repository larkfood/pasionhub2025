@php
	$categories ??= [];
	$isCountPostsEnabled = (config('settings.listings_list.count_categories_listings') == '1');
@endphp
@if (!empty($categories))
	<div class="row row-cols-lg-6 row-cols-md-4 row-cols-sm-3 row-cols-2">
		@foreach($categories as $cat)
			@php
				$catId = data_get($cat, 'id', 0);
				$catImgUrl = data_get($cat, 'image_url', '');
				$catName = data_get($cat, 'name', '--');
				
				$catCountPosts = $isCountPostsEnabled
					? '(' . ($countPostsPerCat[$catId]['total'] ?? 0) . ')'
					: '';
				$catDisplayName = !empty($catCountPosts) ? $catName . ' ' . $catCountPosts : $catName;
			@endphp
			<div class="col f-category">
				<a href="{{ urlGen()->category($cat) }}">
					<img src="{{ $catImgUrl }}" class="lazyload img-fluid" alt="{{ $catName }}">
					<h6>{{ $catDisplayName }}</h6>
				</a>
			</div>
		@endforeach
	</div>
@endif
