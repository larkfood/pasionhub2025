<?php

use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/Web/Post/DetailsController.php'));
	File::delete(resource_path('views/post/details.blade.php'));
	File::delete(resource_path('views/post/inc/fields.blade.php'));
	File::delete(resource_path('views/post/inc/fields-values.blade.php'));
	File::delete(resource_path('views/post/inc/pictures-slider.blade.php'));
	File::delete(resource_path('views/post/inc/pictures-slider/bootstrap-carousel.blade.php'));
	File::delete(resource_path('views/post/inc/pictures-slider/bxslider-horizontal.blade.php'));
	File::delete(resource_path('views/post/inc/pictures-slider/bxslider-vertical.blade.php'));
	File::delete(resource_path('views/post/inc/pictures-slider/swiper-horizontal.blade.php'));
	File::delete(resource_path('views/post/inc/pictures-slider/swiper-vertical.blade.php'));
	File::delete(resource_path('views/post/inc/security-tips.blade.php'));
	
} catch (\Throwable $e) {
}
