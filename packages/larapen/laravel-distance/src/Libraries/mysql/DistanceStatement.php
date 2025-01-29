<?php

namespace Larapen\LaravelDistance\Libraries\mysql;

use Larapen\LaravelDistance\Helper;

class DistanceStatement
{
	/**
	 * Get 'SELECT' statement column
	 *
	 * @param string $aLon
	 * @param string $aLat
	 * @param float $bLon
	 * @param float $bLat
	 * @return false|string
	 */
	public static function select(string $aLon, string $aLat, float $bLon, float $bLat)
	{
		// Get the distance calculation formula
		$distanceCalculationFormula = config('distance.functions.default', 'haversine');
		
		// Variables for the SQL statements
		$distanceAggregateAliasName = config('distance.rename', 'distance');
		$countryCode = config('distance.countryCode');
		
		// If the selected MySQL function doesn't exist...
		// If the 'haversine' or 'orthodromy' is selected, use the function formula as inline SQL
		// Else use the cities standard searches
		if (!DistanceHelper::checkIfDistanceCalculationFunctionExists($distanceCalculationFormula)) {
			if (in_array($distanceCalculationFormula, ['haversine', 'orthodromy'])) {
				$point1 = 'POINT(' . $aLon . ', ' . $aLat . ')';
				$point2 = 'POINT(' . $bLon . ', ' . $bLat . ')';
				
				return DistanceHelper::$distanceCalculationFormula($point1, $point2);
			}
			
			return false;
		} else {
			// Call the MySQL function (The result is in Meters)
			$formula = $distanceCalculationFormula . '(POINT(' . $aLon . ', ' . $aLat . '), POINT(' . $bLon . ', ' . $bLat . '))';
			
			// Meters To Km
			$formula = $formula . ' / 1000';
			
			// If the selected Country uses Miles unit, then convert Km To Miles
			if (Helper::isMilesUsingCountry($countryCode)) {
				$formula = '(' . $formula . ') * 0.621371192';
			}
			
			// Get the Distance calculation SQL query
			return '(' . $formula . ') AS ' . $distanceAggregateAliasName;
		}
	}
	
	/**
	 * Get 'HAVING' statement condition
	 *
	 * @param int|null $distance
	 * @return string
	 */
	public static function having(?int $distance = null): string
	{
		$distanceAggregateAliasName = config('distance.rename', 'distance');
		
		$distance = is_null($distance) ? config('distance.defaultDistance', 50) : $distance;
		$distance = (int)$distance;
		$distance = ($distance <= 0) ? 1 : $distance; // Distance needs to be 1 or higher
		
		return $distanceAggregateAliasName . ' <= ' . $distance;
	}
	
	/**
	 * Get 'ORDER BY' rule
	 *
	 * @param string|null $order
	 * @return string
	 */
	public static function orderBy(?string $order = null): string
	{
		$distanceAggregateAliasName = config('distance.rename', 'distance');
		
		$order = is_null($order) ? config('distance.orderBy', 'ASC') : $order;
		$order = in_array(strtoupper($order), ['ASC', 'DESC']) ? $order :  'ASC';
		
		return $distanceAggregateAliasName . ' ' . strtoupper($order);
	}
}
