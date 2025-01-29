@php
	$titleSlug ??= '';
@endphp
{{-- bxSlider - Vertical Thumbnails --}}
<div class="gallery-container">
	<div class="slider-left">
		<div class="bxslider">
			@forelse($pictures as $key => $image)
				<div class="bx-item">
					@php
						$src = data_get($image, 'url.large');
						$webpSrc = data_get($image, 'url.webp.large');
						$alt = $titleSlug . '-big-' . $key;
						echo generateImageHtml($src, $alt, $webpSrc);
					@endphp
				</div>
			@empty
				<div class="bx-item">
					<img src="{{ thumbParam(config('larapen.media.picture'))->url() }}" alt="img" class="default-picture"/>
				</div>
			@endforelse
		</div>
	</div>
	<div class="bxslider-pager scrollbar">
		@forelse($pictures as $key => $image)
			<a class="bx-thumb-item" data-slide-index="{{ $key }}" href="">
				@php
					$src = data_get($image, 'url.small');
					$webpSrc = data_get($image, 'url.webp.small');
					$alt = $titleSlug . '-small-' . $key;
					echo generateImageHtml($src, $alt, $webpSrc);
				@endphp
			</a>
		@empty
			<a class="bx-thumb-item" data-slide-index="0" href="">
				<img src="{{ thumbParam(config('larapen.media.picture'))->setOption('picture-sm')->url() }}"
				     alt="img"
				     class="default-picture"
				>
			</a>
		@endforelse
	</div>
</div>

@section('after_styles')
	@parent
	@if (config('lang.direction') == 'rtl')
		<link href="{{ url('assets/plugins/bxslider/jquery.bxslider.rtl.css') }}" rel="stylesheet"/>
	@else
		<link href="{{ url('assets/plugins/bxslider/jquery.bxslider.css') }}" rel="stylesheet"/>
	@endif
	
	<link href="{{ url('assets/plugins/bxslider/bxslider-custom.css') }}" rel="stylesheet"/>
	<link href="{{ url('assets/plugins/bxslider/bxslider-vertical-thumbs.css') }}" rel="stylesheet"/>
	@if (config('lang.direction') == 'rtl')
		<link href="{{ url('assets/plugins/bxslider/bxslider-vertical-thumbs-rtl.css') }}" rel="stylesheet"/>
	@endif
@endsection
@section('after_scripts')
	@parent
	<script src="{{ url('assets/plugins/bxslider/jquery.bxslider.min.js') }}"></script>
	<script>
		let totalSlides = {{ is_array($pictures) ? count($pictures) : 0 }};
		
		/* Enable touch events for Mobile Browsers */
		let touchDevice = false;
		if (navigator.userAgent.match(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/)) {
			touchDevice = (totalSlides > 1);
		}
		
		onDocumentReady((event) => {
			$('.bxslider').bxSlider({
				touchEnabled: touchDevice,
				speed: 300,
				pagerCustom: '.bxslider-pager',
				adaptiveHeight: true,
				nextText: '{{ t('bxslider.nextText') }}',
				prevText: '{{ t('bxslider.prevText') }}',
				startText: '{{ t('bxslider.startText') }}',
				stopText: '{{ t('bxslider.stopText') }}'
			});
			
			/* Full Size Images Gallery */
			$(document).on('mousedown', '.bxslider img', function (e) {
				e.preventDefault();
				
				let currentSrc = $(this).attr('src');
				let imgTitle = "{{ data_get($post, 'title') }}";
				
				let wrapperSelector = '.bxslider img:not(.default-picture)';
				let imgSrcArray = getFullSizeSrcOfAllImg(wrapperSelector, currentSrc);
				if (imgSrcArray === undefined || imgSrcArray.length === 0) {
					return false;
				}
				
				{{-- Load full size pictures slides dynamically --}}
				let swipeboxItems = formatImgSrcArrayForSwipebox(imgSrcArray, imgTitle);
				let swipeboxOptions = {
					hideBarsDelay: (1000 * 60 * 5),
					loopAtEnd: false
				};
				$.swipebox(swipeboxItems, swipeboxOptions);
			});
		});
	</script>
@endsection
