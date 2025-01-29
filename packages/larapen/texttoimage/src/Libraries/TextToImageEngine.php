<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace Larapen\TextToImage\Libraries;

use App\Exceptions\Custom\CustomException;
use Intervention\Image\Laravel\Facades\Image as FacadeImage;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Image;
use Intervention\Image\Interfaces\EncodedImageInterface;

class TextToImageEngine
{
	/**
	 * @var \Larapen\TextToImage\Libraries\Settings
	 */
	protected Settings $settings;
	
	/**
	 * @var \Intervention\Image\Image
	 */
	protected Image $image;
	
	protected int $retinaUpsize = 1;
	
	/**
	 * @param \Larapen\TextToImage\Libraries\Settings $settings
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
		
		$this->retinaUpsize = $this->settings->retinaEnabled ? 2 : 1;
		if (!empty($this->settings->boldFontFamily)) {
			$this->settings->fontFamily = $this->settings->boldFontFamily;
		}
	}
	
	/**
	 * @param string|null $string
	 * @return \Larapen\TextToImage\Libraries\TextToImageEngine
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function setText(?string $string): static
	{
		$string = strval($string);
		
		$retina = $this->retinaUpsize;
		$backgroundColor = $this->settings->backgroundColor;
		$padding = $this->settings->padding;
		$fontSize = $this->settings->fontSize * $retina;
		$color = $this->settings->color;
		$fontFamily = $this->settings->fontFamily;
		$shadowEnabled = $this->settings->shadowEnabled;
		$shadowColor = $this->settings->shadowColor;
		$shadowOffsetX = $this->settings->shadowOffsetX;
		$shadowOffsetY = $this->settings->shadowOffsetY;
		
		$bounds = $this->getTextBounds($string);
		
		// $backgroundColor = 'f00'; // debug!
		$pixelLost = 0;
		$width = ($bounds->width + $pixelLost);
		$height = ($bounds->height + $pixelLost);
		
		try {
			
			// Create a canvas
			$image = FacadeImage::create($width, $height)->fill($backgroundColor);
			
			// Text positions
			$x = ($padding > 0) ? $padding : 0;
			if (str_contains($string, '(') || str_contains($string, ')')) {
				$y = ($padding > 0) ? ($padding + ($padding / 2)) : ($retina * 2);
			} else {
				$y = ($padding > 0) ? $padding : 0;
			}
			
			if ($shadowEnabled) {
				// Draw the shadow first
				$image->text(
					$string,
					$x + $shadowOffsetX,
					$y + $shadowOffsetY,
					function (FontFactory $font) use ($fontFamily, $fontSize, $shadowColor, $width) {
						$font->filename($fontFamily);
						$font->size($fontSize);
						$font->color($shadowColor);
						$font->align('left');
						$font->valign('top');
						// $font->wrap($width);
					});
				
				// Draw the main text over the shadow
				// (below)
			}
			
			// Draw the text on the image at the calculated vertical position
			$image->text($string, $x, $y, function (FontFactory $font) use ($fontFamily, $fontSize, $color, $width) {
				$font->filename($fontFamily);
				$font->size($fontSize);
				$font->color($color);
				$font->align('left');
				$font->valign('top');
				// $font->wrap($width);
			});
			
			// Apply additional effects if necessary
			if ((float)$this->settings->blur > 0) {
				$image->blur($this->settings->blur);
			}
			
			if ((float)$this->settings->pixelate > 0) {
				$image->pixelate($this->settings->pixelate);
			}
			
			// $this->debugImage($image, 'debug-2'); // debug!
			
			$this->image = $image;
			
		} catch (\Throwable $e) {
			throw new CustomException($e->getMessage());
		}
		
		return $this;
	}
	
	/**
	 * Get the physical size of text with a given string and font settings
	 *
	 * @param string|null $string
	 * @return \Larapen\TextToImage\Libraries\BoundingBox
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function getTextBounds(?string $string): BoundingBox
	{
		$string = strval($string);
		
		$retina = $this->retinaUpsize;
		$fontFamily = $this->settings->fontFamily;
		$fontSize = $this->settings->fontSize * $retina;
		$padding = $this->settings->padding;
		$shadowEnabled = $this->settings->shadowEnabled;
		$shadowOffsetX = $this->settings->shadowOffsetX;
		$shadowOffsetY = $this->settings->shadowOffsetY;
		
		try {
			// Create a fake canvas (with fake background color)
			$draftImage = FacadeImage::create(600, 300)->fill('fff');
			
			// Create a draft image of the text
			$draftImage->text($string, 0, $fontSize, function (FontFactory $font) use ($fontFamily, $fontSize) {
				$font->filename($fontFamily);
				$font->size($fontSize);
				$font->color('000'); // With fake text color (But must be a color opposite to the background color)
			});
			
			// Remove border areas of the image on all sides that have a similar color
			// That allows to get the text's filled dimensions (so avoids to get the one of the fake canvas)
			$draftImage->trim(20);
			
			// $this->debugImage($draftImage, 'debug-1'); // debug!
			
			// Calculate width and height with padding
			$width = $draftImage->width() + ($padding * 2);
			$height = $draftImage->height() + ($padding * 2);
			if ($shadowEnabled) {
				$width = $width + $shadowOffsetX;
				$height = $height + $shadowOffsetY;
			}
			
			unset($draftImage);
		} catch (\Throwable $e) {
			throw new CustomException($e->getMessage());
		}
		
		return new BoundingBox($width, $height, $padding);
	}
	
	/**
	 * Get image as base64 string
	 *
	 * @return string
	 */
	public function getEmbeddedImage(): string
	{
		$height = $this->settings->fontSize;
		$height = (!$this->settings->retinaEnabled)
			? ($height + ($this->retinaUpsize * 2))
			: ($height + ($this->retinaUpsize / 2));
		$heightInPx = $height . 'px';
		
		$encodedImage = $this->getEncodedImage();
		$stringFormat = '<img src="%s" style="width: auto; height: %s;">';
		
		return sprintf($stringFormat, $encodedImage->toDataUri(), $heightInPx);
	}
	
	/**
	 * @return \Intervention\Image\Interfaces\EncodedImageInterface
	 */
	public function getEncodedImage(): EncodedImageInterface
	{
		$extension = SupportedFormat::getFormat($this->settings->format);
		$encodedImage = $this->image->encodeByExtension($extension, quality: $this->settings->quality);
		
		// Free memory
		$this->destroyImage();
		
		return $encodedImage;
	}
	
	/**
	 * @return \Intervention\Image\Image
	 */
	public function getImage(): Image
	{
		return $this->image;
	}
	
	/**
	 * Free memory
	 *
	 * @return void
	 */
	public function destroyImage(): void
	{
		if (isset($this->image)) {
			unset($this->image);
		}
	}
	
	/**
	 * @param \Intervention\Image\Image $encodedImage
	 * @param string|null $filename
	 * @return void
	 */
	private function debugImage(Image $encodedImage, ?string $filename = null): void
	{
		$filename = !empty($filename) ? $filename : 'debug-image';
		$imageFileName = storage_path($filename . '.png');
		file_put_contents($imageFileName, $encodedImage->encodeByExtension('png')->toString());
	}
}
