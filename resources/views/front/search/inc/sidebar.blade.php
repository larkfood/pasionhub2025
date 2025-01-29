<!-- this (.mobile-filter-sidebar) part will be position fixed in mobile version -->
<div class="col-md-3 page-sidebar mobile-filter-sidebar pb-4">
	<aside>
		<div class="sidebar-modern-inner enable-long-words">
			
			@include('front.search.inc.sidebar.fields')
			@include('front.search.inc.sidebar.categories')
            @include('front.search.inc.sidebar.cities')
			@if (!config('settings.listings_list.hide_date'))
				@include('front.search.inc.sidebar.date')
			@endif
			@include('front.search.inc.sidebar.price')
			
		</div>
	</aside>
</div>

@section('after_scripts')
    @parent
    <script>
        var baseUrl = '{{ request()->url() }}';
    </script>
@endsection
