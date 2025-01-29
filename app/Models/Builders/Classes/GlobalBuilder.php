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

namespace App\Models\Builders\Classes;

use Illuminate\Database\Eloquent\Builder;

class GlobalBuilder extends Builder
{
	/**
	 * @param string $column
	 * @return $this
	 */
	public function columnIsEmpty(string $column): static
	{
		$this->where(function (self $query) use ($column) {
			$query->where($column, '')->orWhere($column, 0)->orWhereNull($column);
		});
		
		return $this;
	}
	
	/**
	 * @param string $column
	 * @return $this
	 */
	public function columnIsNotEmpty(string $column): static
	{
		$this->where(function (self $query) use ($column) {
			$query->where($column, '!=', '')->where($column, '!=', 0)->whereNotNull($column);
		});
		
		return $this;
	}
	
	/**
	 * @param string $column
	 * @return $this
	 */
	public function orColumnIsEmpty(string $column): static
	{
		$this->orWhere(fn (self $query) => $query->columnIsEmpty($column));
		
		return $this;
	}
	
	/**
	 * @param string $column
	 * @return $this
	 */
	public function orColumnIsNotEmpty(string $column): static
	{
		$this->orWhere(fn (self $query) => $query->columnIsNotEmpty($column));
		
		return $this;
	}
}
