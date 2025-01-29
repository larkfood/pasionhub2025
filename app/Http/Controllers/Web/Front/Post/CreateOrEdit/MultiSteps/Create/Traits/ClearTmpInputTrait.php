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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits;

trait ClearTmpInputTrait
{
	/**
	 * Clear Temporary Inputs & Files
	 */
	public function clearTemporaryInput(): void
	{
		if (session()->has('postInput')) {
			session()->forget('postInput');
		}
		
		if (session()->has('picturesInput')) {
			$picturesInput = (array)session('picturesInput');
			if (!empty($picturesInput)) {
				try {
					foreach ($picturesInput as $filePath) {
						$this->removePictureWithItsThumbs($filePath);
					}
				} catch (\Throwable $e) {
					if (!empty($e->getMessage())) {
						flash($e->getMessage())->error();
					}
				}
				session()->forget('picturesInput');
			}
		}
		
		if (session()->has('paymentInput')) {
			session()->forget('paymentInput');
		}
		
		if (session()->has('uid')) {
			session()->forget('uid');
		}
	}
}
