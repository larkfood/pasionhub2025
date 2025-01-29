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

namespace App\Helpers\Categories\Traits;

use App\Helpers\Arr;
use App\Helpers\DBTool;
use Illuminate\Support\Facades\DB;

trait DepthTrait
{
	/**
	 * Find and Set the nodes depth
	 *
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function setNodesDepth(): void
	{
		$this->checkTablesAndColumns();
		
		// Finding the Depth of the nodes
		$sql = 'SELECT node.id, node.name, (COUNT(parent.name) - 1) AS depth
				FROM ' . DBTool::table($this->nestedTable) . ' AS node,
						' . DBTool::table($this->nestedTable) . ' AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
				GROUP BY node.id, node.name
				ORDER BY node.id;';
		$items = DB::select($sql);
		
		if (is_array($items) && count($items) > 0) {
			foreach ($items as $item) {
				$itemArray = Arr::fromObject($item);
				
				if (!isset($itemArray[$this->colPrimaryKey])) {
					continue;
				}
				
				$newArray = [
					'depth' => $itemArray['depth'],
				];
				
				// Set the item's depth
				$affected = DB::table($this->nestedTable)
					->where($this->colPrimaryKey, $itemArray[$this->colPrimaryKey])
					->update($newArray);
			}
		}
	}
}
