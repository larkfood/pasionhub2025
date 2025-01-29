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

namespace App\Http\Controllers\Web\Setup\Update\Traits;

use App\Helpers\DotenvEditor;

trait EnvTrait
{
	/**
	 * Update the current version to last version
	 *
	 * @param $last
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function setCurrentVersion($last): void
	{
		DotenvEditor::setKey('APP_VERSION', $last);
		DotenvEditor::save();
	}
}
