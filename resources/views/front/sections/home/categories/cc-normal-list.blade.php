@php
	$categories ??= [];
	$isCountPostsEnabled = (config('settings.listings_list.count_categories_listings') == '1');
	$isShowingCategoryIconEnabled = in_array(config('settings.listings_list.show_category_icon'), [2, 6, 7, 8]);
	
	$catDisplayType ??= 'cc_normal_list';
	$styled = ($catDisplayType == 'cc_normal_list_s') ? ' styled' : '';
@endphp

<div style="clear: both;"></div>

@if (!empty($categories))
	<div class="list-categories-children{{ $styled }}">
		<div class="row row-cols-lg-3 row-cols-md-2 row-cols-sm-1 row-cols-1 px-3">
			@foreach ($categories as $key => $iCat)
				@php
					$randomId = '-' . substr(uniqid(rand(), true), 5, 5);
					$itemClass = (count($categories) == $key + 1) ? ' last-column' : '';
					
					$catId = data_get($iCat, 'id', 0);
					$catIconClass = $isShowingCategoryIconEnabled ? data_get($iCat, 'icon_class', 'fa-solid fa-check') : '';
					$catIcon = !empty($catIconClass) ? '<i class="' . $catIconClass . '"></i> ' : '';
					$catName = data_get($iCat, 'name', '--');
					
					$catCountPosts = $isCountPostsEnabled
						? ' (' . ($countPostsPerCat[$catId]['total'] ?? 0) . ')'
						: '';
					$catDisplayName = !empty($catCountPosts) ? $catName . ' ' . $catCountPosts : $catName;
				@endphp
				<div class="col cat-list{{ $itemClass }}">
					<h3 class="cat-title rounded">
						{!! $catIcon !!}<a href="{{ urlGen()->category($iCat) }}">{{ $catDisplayName }}</a>
						<span class="btn-cat-collapsed collapsed"
						      data-bs-toggle="collapse"
						      data-bs-target=".cat-id-{{ $catId . $randomId }}"
						      aria-expanded="false"
						>
							<span class="icon-down-open-big"></span>
						</span>
					</h3>
					<ul class="cat-collapse collapse show cat-id-{{ $catId . $randomId }} long-list-home">
						@if (isset($subCategories[$catId]))
							@php
								$catSubCats = $subCategories[$catId];
							@endphp
							@foreach ($catSubCats as $iSubCat)
								@php
									$subCatId = data_get($iSubCat, 'id', 0);
									$subCatName = data_get($iSubCat, 'name', '--');
									$subCatCountPosts = $isCountPostsEnabled
										? ' (' . ($countPostsPerCat[$subCatId]['total'] ?? 0) . ')'
										: '';
								@endphp
								<li>
									<a href="{{ urlGen()->category($iSubCat) }}">{{ $subCatName }}</a>{{ $subCatCountPosts }}
								</li>
							@endforeach
						@endif
					</ul>
				</div>
			@endforeach
		</div>
	</div>
	
	<div style="clear: both;"></div>
@endif
