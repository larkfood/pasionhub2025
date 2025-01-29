<?php
/*
 * ================================================================================
 * Laravel Captcha package
 * ================================================================================
 *
 * --------------------------------------------------------------------------------
 * Update Info
 * --------------------------------------------------------------------------------
 * @copyright Copyright (c) 2024 BeDigit
 * @date: 2024-09-03
 * @compatibility: Laravel 11+
 * @author: BeDigit
 * @website: https://bedigit.com
 *
 * --------------------------------------------------------------------------------
 * Creation Info
 * --------------------------------------------------------------------------------
 * @copyright Copyright (c) 2015 MeWebStudio
 * @version 2.x
 * @compatibility: Laravel 5 & 6
 * @author Muharrem ERİN
 * @contact me@mewebstudio.com
 * @web http://www.mewebstudio.com
 * @date 2015-04-03
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * ================================================================================
 */

namespace Larapen\Captcha;

use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image as FacadeImage;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Image;
use Illuminate\Session\Store as Session;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Captcha
{
	protected Filesystem $files;
	protected Repository $config;
	protected Session $session;
	protected Hasher $hasher;
	protected Str $str;
	
	protected ImageInterface $canvas;
	protected Image $image;
	
	protected array $backgrounds = [];
	protected array $fonts = [];
	protected array $fontColors = [];
	protected int $length = 5;
	protected int $width = 120;
	protected int $height = 36;
	protected int $angle = 15;
	protected int $lines = 3;
	protected array|string $characters;
	protected array|string $text;
	protected int $contrast = 0;
	protected int $quality = 90;
	protected int $sharpen = 0;
	protected int $blur = 0;
	protected bool $bgImage = true;
	protected string $bgColor = '#ffffff';
	protected bool $invert = false;
	protected bool $sensitive = false;
	protected bool $math = false;
	protected int $textLeftPadding = 4;
	protected string $fontsDirectory;
	protected int $expire = 60;
	protected bool $encrypt = true;
	
	/**
	 * @param \Illuminate\Filesystem\Filesystem $files
	 * @param \Illuminate\Contracts\Config\Repository $config
	 * @param \Illuminate\Session\Store $session
	 * @param \Illuminate\Hashing\BcryptHasher $hasher
	 * @param \Illuminate\Support\Str $str
	 */
	public function __construct(Filesystem $files, Repository $config, Session $session, Hasher $hasher, Str $str)
	{
		$this->files = $files;
		$this->config = $config;
		$this->session = $session;
		$this->hasher = $hasher;
		$this->str = $str;
		
		$defaultFontsDirectory = dirname(__DIR__) . '/assets/fonts';
		$fontsDirectory = config('captcha.fontsDirectory', $defaultFontsDirectory);
		$this->fontsDirectory = is_string($fontsDirectory) ? $fontsDirectory : $defaultFontsDirectory;
		
		$defaultCharacters = ['1', '2', '3', '4', '6', '7', '8', '9'];
		$characters = config('captcha.characters', $defaultCharacters);
		$this->characters = (is_array($characters) || is_string($characters)) ? $characters : $defaultCharacters;
	}
	
	/**
	 * @param string $config
	 * @return void
	 */
	protected function configure(string $config): void
	{
		if ($this->config->has('captcha.' . $config)) {
			foreach ($this->config->get('captcha.' . $config) as $key => $val) {
				$this->{$key} = $val;
			}
		}
	}
	
	/**
	 * Create captcha image
	 *
	 * @param string $config
	 * @param bool $api
	 * @return string|array
	 * @throws \Exception
	 */
	public function create(string $config = 'default', bool $api = false): string|array
	{
		try {
			
			$this->backgrounds = $this->files->files(__DIR__ . '/../assets/backgrounds');
			$this->fonts = $this->files->files($this->fontsDirectory);
			
			$this->fonts = array_map(fn ($file) => $file->getPathName(), $this->fonts);
			$this->fonts = array_values($this->fonts); // reset fonts array index
			
			$this->configure($config);
			
			$generator = $this->generate();
			$this->text = $generator['value'];
			
			$this->canvas = FacadeImage::create($this->width, $this->height)->fill($this->bgColor);
			
			if ($this->bgImage) {
				$this->image = FacadeImage::read($this->background())->resize($this->width, $this->height);
				$this->canvas->place($this->image);
			} else {
				$this->image = $this->canvas;
			}
			
			if ($this->contrast != 0) {
				$this->image->contrast($this->contrast);
			}
			
			$this->text();
			
			$this->lines();
			
			if ($this->sharpen) {
				$this->image->sharpen($this->sharpen);
			}
			if ($this->invert) {
				$this->image->invert();
			}
			if ($this->blur) {
				$this->image->blur($this->blur);
			}
			
			if ($api) {
				Cache::put($this->getCacheKey($generator['key']), $generator['value'], $this->expire);
			}
			
			$encodedImage = $this->image->encodeByExtension('png', quality: $this->quality);
			
			return $api ? [
				'sensitive' => $generator['sensitive'],
				'key'       => $generator['key'],
				'img'       => $encodedImage->toDataUri(),
			] : $encodedImage->toString();
			
		} catch (\Throwable $e) {
			abort(500, $e->getMessage());
		}
	}
	
	/**
	 * Image backgrounds
	 *
	 * @return string
	 */
	protected function background(): string
	{
		return $this->backgrounds[rand(0, count($this->backgrounds) - 1)];
	}
	
	/**
	 * Generate captcha text
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function generate(): array
	{
		$characters = is_string($this->characters) ? str_split($this->characters) : $this->characters;
		
		$bag = [];
		
		if ($this->math) {
			$x = random_int(10, 30);
			$y = random_int(1, 9);
			$bag = "$x + $y = ";
			$key = $x + $y;
			$key .= '';
		} else {
			for ($i = 0; $i < $this->length; $i++) {
				$char = $characters[rand(0, count($characters) - 1)];
				$bag[] = $this->sensitive ? $char : $this->str->lower($char);
			}
			$key = implode('', $bag);
		}
		
		$hash = $this->hasher->make($key);
		// $hash = $key; // DEBUG!
		if ($this->encrypt) $hash = Crypt::encrypt($hash);
		
		$this->session->put('captcha', [
			'sensitive' => $this->sensitive,
			'key'       => $hash,
			'encrypt'   => $this->encrypt,
		]);
		
		return [
			'value'     => $bag,
			'sensitive' => $this->sensitive,
			'key'       => $hash,
		];
	}
	
	/**
	 * Writing captcha text
	 *
	 * @return void
	 */
	protected function text(): void
	{
		if ($this->math) {
			$marginTop = 4;
		} else {
			$marginTop = $this->image->height() / $this->length;
		}
		
		$imageImagickDriver = \Intervention\Image\Drivers\Imagick\Driver::class;
		if (config('image.driver') == $imageImagickDriver) {
			$marginTop = 0;
		}
		
		$text = $this->text;
		$text = is_string($text) ? str_split($text) : $text;
		
		$i = 0;
		foreach ($text as $key => $char) {
			if ($this->math) {
				$marginLeft = 4 + $i;
			} else {
				$marginLeft = $this->textLeftPadding + ($key * ($this->image->width() - $this->textLeftPadding) / $this->length);
				$marginLeft = (int)$marginLeft;
			}
			
			$this->image->text($char, $marginLeft, $marginTop, function (FontFactory $font) {
				$font->filename($this->font());
				$font->size($this->fontSize());
				$font->color($this->fontColor());
				$font->align('left');
				$font->valign('top');
				$font->angle($this->angle());
			});
			
			$i += 20;
		}
	}
	
	/**
	 * Image fonts
	 *
	 * @return string
	 */
	protected function font(): string
	{
		return $this->fonts[rand(0, count($this->fonts) - 1)];
	}
	
	/**
	 * Random font size
	 *
	 * @return int
	 */
	protected function fontSize(): int
	{
		return rand($this->image->height() - 10, $this->image->height());
	}
	
	/**
	 * Random font color
	 *
	 * @return string
	 */
	protected function fontColor(): string
	{
		if (!empty($this->fontColors)) {
			$color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
		} else {
			$color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
		}
		
		return $color;
	}
	
	/**
	 * Angle
	 *
	 * @return int
	 */
	protected function angle(): int
	{
		return rand((-1 * $this->angle), $this->angle);
	}
	
	/**
	 * Random image lines
	 *
	 * @return Image
	 */
	protected function lines(): Image
	{
		for ($i = 0; $i <= $this->lines; $i++) {
			$x1 = rand(0, $this->image->width()) + $i * rand(0, $this->image->height());
			$y1 = rand(0, $this->image->height());
			$x2 = rand(0, $this->image->width());
			$y2 = rand(0, $this->image->height());
			
			$this->image->drawLine(function (LineFactory $line) use ($x1, $y1, $x2, $y2) {
				$line->from($x1, $y1);
				$line->to($x2, $y2);
				$line->color($this->fontColor());
				$line->width(5);
			});
		}
		
		return $this->image;
	}
	
	/**
	 * Captcha check
	 *
	 * @param string $value
	 * @return bool
	 */
	public function check(string $value): bool
	{
		if (!$this->session->has('captcha')) {
			return false;
		}
		
		$key = $this->session->get('captcha.key');
		$sensitive = $this->session->get('captcha.sensitive');
		$encrypt = $this->session->get('captcha.encrypt');
		
		if (!$sensitive) {
			$value = $this->str->lower($value);
		}
		
		if ($encrypt) $key = Crypt::decrypt($key);
		$check = $this->hasher->check($value, $key);
		// if verify pass,remove session
		if ($check) {
			$this->session->remove('captcha');
		}
		
		return $check;
	}
	
	/**
	 * Returns the md5 short version of the key for cache
	 *
	 * @param string $key
	 * @return string
	 */
	protected function getCacheKey(string $key): string
	{
		return 'captcha_' . md5($key);
	}
	
	/**
	 * Captcha check
	 *
	 * @param string $value
	 * @param string $key
	 * @param string $config
	 * @return bool
	 */
	public function checkApi(string $value, string $key, string $config = 'default'): bool
	{
		if (!Cache::pull($this->getCacheKey($key))) {
			return false;
		}
		
		$this->configure($config);
		
		if (!$this->sensitive) $value = $this->str->lower($value);
		if ($this->encrypt) $key = Crypt::decrypt($key);
		
		return $this->hasher->check($value, $key);
	}
	
	/**
	 * Generate captcha image source
	 *
	 * @param string $config
	 * @param bool $api
	 * @return string
	 */
	public function src(string $config = 'default', bool $api = false): string
	{
		$prefix = isAdminPanel() ? config('larapen.admin.route', 'admin') . '/' : '';
		$path = $api ? 'captcha/api/' : 'captcha/';
		
		return url($prefix . $path . $config) . '?' . $this->str->random(8);
	}
	
	/**
	 * Generate captcha image html tag
	 *
	 * @param string $config
	 * @param array $attributes
	 * $attrs -> HTML attributes supplied to the image tag where key is the attribute and the value is the attribute value
	 * @return string
	 */
	public function img(string $config = 'default', array $attributes = []): string
	{
		$attrStr = '';
		foreach ($attributes as $key => $value) {
			if ($key == 'src') {
				// Neglect src attribute
				continue;
			}
			
			$attrStr .= $key . '="' . $value . '" ';
		}
		$attrStr = trim($attrStr);
		$attrStr = !empty($attrStr) ? ' ' . $attrStr : '';
		
		return new HtmlString('<img src="' . $this->src($config) . '"' . $attrStr . '>');
	}
}
