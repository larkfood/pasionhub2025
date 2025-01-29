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

class Settings
{
    public string $color = '#000000';
    public string $backgroundColor = '#FFFFFF';
    public ?string $fontFamily = null;
	public ?string $boldFontFamily = null;
    public int $fontSize = 12;
	public int $padding = 5;
	public bool $shadowEnabled = false;
	public string $shadowColor = '#000000';
	public int $shadowOffsetX = 2;
	public int $shadowOffsetY = 2;
    public int $quality = 90;
	public bool $retinaEnabled = false;
    public string $format = 'png';
    public int $blur = 0;
    public int $pixelate = 0;
    
    public static function createFromIni($iniFile): Settings
    {
        $settings = new Settings();
        
        // Cannot find settings file
        if (!realpath($iniFile)) return $settings;
        
        // Parse config file
        $properties = @parse_ini_file($iniFile);
        if (empty($properties)) return $settings;
        
        $settings->assignProperties($properties);
        
        return $settings;
    }
    
    public function assignProperties($properties): void
    {
        if (empty($properties) || !is_array($properties)) {
            return;
        }
        
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                continue;
            }
            
            $this->$name = $value;
        }
    }
}
