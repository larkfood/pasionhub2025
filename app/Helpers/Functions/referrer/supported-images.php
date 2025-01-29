<?php

return [
	
	/*
	 * PHP Gd & Imagick extensions supported formats (through Intervention)
	 *
	 * More information about format:
	 * Intervention: https://image.intervention.io/v3/introduction/formats
	 * GD: https://www.php.net/manual/en/intro.image.php
	 * ImageMagick: https://imagemagick.org/script/formats.php
	 *
	 * How to set up AVIF with GD properly: https://php.watch/versions/8.1/gd-avif
	 */
	'server'          => [
		\Intervention\Image\Drivers\Gd\Driver::class => [
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'  => 'image/gif',
			'png'  => 'image/png',
			'avif' => 'image/avif',
			'bmp'  => 'image/bmp',
			'webp' => 'image/webp', // 'Animated WebP' is not supported
		],
		
		\Intervention\Image\Drivers\Imagick\Driver::class => [
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'  => 'image/gif',
			'png'  => 'image/png',
			'avif' => 'image/avif',
			'bmp'  => 'image/bmp',
			'webp' => 'image/webp',
			'tiff' => 'image/tiff',
			'tif'  => 'image/tiff',
			'jp2'  => 'image/jp2',
			'j2c'  => 'image/x-jp2-codestream',
			'j2k'  => 'image/x-jp2-codestream',
			'heic' => 'image/heic',
			'heif' => 'image/heif',
		],
	],
	
	/*
	 * Browsers supported formats
	 */
	'client'          => [
		/*
	     * Extensions to be kept during uploads. All the other types will be converted to the fallbackExtension.
	     * For best compatibility across browsers and platforms, JPEG, GIF, PNG, WebP, and AVIF are recommended for web use.
	     */
		'extensions' => [
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'  => 'image/gif',
			'png'  => 'image/png',
			'avif' => 'image/avif',
			'webp' => 'image/webp',
		],
		
		'fallbackExtension' => 'jpg',
	],
	
	/*
	 * List of all image types formats
	 */
	'imageExtensions' => [
		'jpg'  => 'JPEG',
		'jpeg' => 'JPEG',
		'jpe'  => 'JPEG',
		'jif'  => 'JPEG',
		'jfif' => 'JPEG',
		'jfi'  => 'JPEG',
		'png'  => 'Portable Network Graphics',
		'gif'  => 'Graphics Interchange Format',
		'bmp'  => 'Bitmap Image',
		'dib'  => 'Device Independent Bitmap',
		'webp' => 'WebP Image',
		'tif'  => 'Tagged Image File Format',
		'tiff' => 'Tagged Image File Format',
		'ico'  => 'Icon File',
		'cur'  => 'Cursor File',
		'svg'  => 'Scalable Vector Graphics',
		'heif' => 'High Efficiency Image Format',
		'heic' => 'High Efficiency Image Coding',
		'raw'  => 'RAW Image',
		'arw'  => 'Sony RAW Image',
		'cr2'  => 'Canon RAW Image',
		'nef'  => 'Nikon RAW Image',
		'orf'  => 'Olympus RAW Image',
		'rw2'  => 'Panasonic RAW Image',
		'dng'  => 'Digital Negative (DNG)',
		'pef'  => 'Pentax RAW Image',
		'psd'  => 'Adobe Photoshop Document',
		'ai'   => 'Adobe Illustrator Document',
		'eps'  => 'Encapsulated PostScript',
		'pdf'  => 'Portable Document Format',
		'tga'  => 'Targa Image',
		'exr'  => 'OpenEXR (High Dynamic Range)',
		'3fr'  => 'Hasselblad RAW Image',
		'jp2'  => 'JPEG 2000',
		'j2k'  => 'JPEG 2000 Codestream',
		'jpf'  => 'JPEG 2000 Part 2',
		'jpm'  => 'JPEG 2000 Part 6 (Compound Image)',
		'jpg2' => 'JPEG 2000 (Alternative)',
		'j2c'  => 'JPEG 2000 Codestream',
		'jpc'  => 'JPEG 2000 Codestream',
		'jpx'  => 'JPEG 2000 Extended',
		'pbm'  => 'Portable Bitmap Image',
		'pgm'  => 'Portable Graymap Image',
		'ppm'  => 'Portable Pixmap Image',
		'pnm'  => 'Portable Anymap Image',
		'xpm'  => 'X PixMap Image',
		'icns' => 'Apple Icon Image',
		'avif' => 'AV1 Image File Format',
		'sr2'  => 'Sony RAW Image',
		'srf'  => 'Sony RAW Image',
		'mrw'  => 'Minolta RAW Image',
		'raf'  => 'Fujifilm RAW Image',
		'erf'  => 'Epson RAW Image',
		'kc2'  => 'Konica Camera RAW Image',
		'dds'  => 'DirectDraw Surface Image',
		'pcx'  => 'PC Paintbrush Image',
		'sgi'  => 'Silicon Graphics Image',
		'rgb'  => 'Silicon Graphics RGB Image',
		'rle'  => 'Run Length Encoded Bitmap',
		'qoi'  => 'Quite OK Image Format',
		'pict' => 'Macintosh Picture File',
		'emf'  => 'Enhanced Metafile',
		'wmf'  => 'Windows Metafile',
		'hdr'  => 'High Dynamic Range Image',
		'pfm'  => 'Portable FloatMap Image',
	],

];
