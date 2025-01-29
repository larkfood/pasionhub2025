@php
	$sectionOptions = $categoriesOptions ?? [];
	$sectionData ??= [];
	$categories = (array)data_get($sectionData, 'categories');
	$subCategories = (array)data_get($sectionData, 'subCategories');
	$countPostsPerCat = (array)data_get($sectionData, 'countPostsPerCat');
	$countPostsPerCat = collect($countPostsPerCat)->keyBy('id')->toArray();
	
	$hideOnMobile = (data_get($sectionOptions, 'hide_on_mobile') == '1') ? ' hidden-sm' : '';
	
	$catDisplayType = data_get($sectionOptions, 'cat_display_type');
	$maxSubCats = (int)data_get($sectionOptions, 'max_sub_cats');
@endphp

@include('front.sections.spacer', ['hideOnMobile' => $hideOnMobile])

<div class="container{{ $hideOnMobile }}">
	<div class="col-12 content-box layout-section">
		<div class="row row-featured row-featured-category">
			<div class="col-12 box-title no-border">
				<div class="inner">
					<h2>
						<span class="title-3">
							{{ t('Browse by') }} <span class="fw-bold">{{ t('category') }}</span>
						</span>
						<a href="{{ urlGen()->sitemap() }}" class="sell-your-item">
							{{ t('View more') }} <i class="fa-solid fa-bars"></i>
						</a>
					</h2>
				</div>
			</div>
			<div class="col-12">
				@if ($catDisplayType == 'c_picture_list')
					
					@include('front.sections.home.categories.c-picture-list')
				
				@elseif ($catDisplayType == 'c_bigIcon_list')
					
					@include('front.sections.home.categories.c-big-icon-list')
				
				@elseif (in_array($catDisplayType, ['cc_normal_list', 'cc_normal_list_s']))
					
					@include('front.sections.home.categories.cc-normal-list')
				
				@elseif (in_array($catDisplayType, ['c_normal_list', 'c_border_list']))
					
					@include('front.sections.home.categories.c-normal-list')
				
				@else
					
					{{-- Called only when issue occurred --}}
					@include('front.sections.home.categories.c-big-icon-list')
				
				@endif
			</div>
		</div>
	</div>
</div>

@section('before_scripts')
	@parent
	@if ($maxSubCats >= 0)
		<script>
			var maxSubCats = {{ $maxSubCats }};
		</script>
	@endif
@endsection
