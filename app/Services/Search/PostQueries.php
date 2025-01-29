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

namespace App\Services\Search;

use App\Enums\PostType;
use App\Helpers\DBTool;
use App\Jobs\GeneratePostCollectionThumbnails;
use App\Services\Search\Traits\Filters;
use App\Services\Search\Traits\GroupBy;
use App\Services\Search\Traits\Having;
use App\Services\Search\Traits\OrderBy;
use App\Services\Search\Traits\Relations;
use App\Services\Search\Traits\Select;
use App\Http\Resources\EntityCollection;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostQueries
{
	use Select, Relations, Filters, GroupBy, Having, OrderBy;
	
	private static bool $dbModeStrict = false;
	protected static int $cacheExpiration = 300; // 5mn (60s * 5)
	
	public $country;
	public $lang;
	
	// Default Inputs (op, perPage, cacheExpiration & orderBy)
	// These inputs need to have a default value
	protected array $input = [];
	
	// Pre-Search Objects
	private array $preSearch;
	public $cat = null;
	public $city = null;
	public $citiesIds = [];
	public $admin = null;
	
	// Default Columns Selected
	protected $select = [];
	protected $groupBy = [];
	protected $having = [];
	protected $orderBy = [];
	
	protected $posts;
	protected string $postsTable;
	
	// 'queryStringKey' => ['name' => 'column', 'order' => 'direction']
	public array $orderByParametersFields = [];
	
	private array $webGlobalQueries = ['countryCode', 'languageCode'];
	private array $webQueriesPerController = [
		'CategoryController' => ['op', 'c', 'sc'],
		'CityController'     => ['op', 'l', 'location', 'r'],
		'TagController'      => ['op', 'tag'],
		'UserController'     => ['op', 'userId', 'username'],
		'CompanyController'  => ['op', 'companyId'],
		'SearchController'   => ['op'],
		'PostsController'    => ['op'], // Account\
	];
	
	/**
	 * PostQueries constructor.
	 *
	 * @param array $input
	 * @param array $preSearch
	 */
	public function __construct(array $input = [], array $preSearch = [])
	{
		self::$dbModeStrict = config('database.connections.' . config('database.default') . '.strict');
		
		// Input
		$this->input = $this->bindValidValuesForInput($input);
		
		// Pre-Search (category, city or admin. division)
		$this->cat = !empty($preSearch['cat']) ? $preSearch['cat'] : null;
		$this->city = !empty($preSearch['city']) ? $preSearch['city'] : null;
		$this->citiesIds = !empty($preSearch['citiesIds']) ? $preSearch['citiesIds'] : [];
		$this->admin = !empty($preSearch['admin']) ? $preSearch['admin'] : null;
		
		// Save preSearch
		$this->preSearch = $preSearch;
		
		// Init. Builder
		$this->posts = Post::query();
		$this->postsTable = (new Post())->getTable();
		
		// Add Default Select Columns
		$this->setSelect();
		
		// Relations
		$this->setRelations();
	}
	
	/**
	 * Get the results
	 *
	 * @param array|null $queriesToRemove
	 * @return array
	 */
	public function fetch(?array $queriesToRemove = null): array
	{
		// Apply Requested Filters
		$this->applyFilters();
		
		// Apply Aggregation & Reorder Statements
		$this->applyGroupBy();
		$this->applyHaving();
		$this->applyOrderBy();
		
		// Get Count PostTypes Results
		$count = (config('settings.listing_form.show_listing_type')) ? $this->countFetch() : [];
		
		// Get Results
		$perPage = data_get($this->input, 'perPage');
		$posts = $this->posts->paginate((int)$perPage);
		
		// Generate listings images thumbnails
		GeneratePostCollectionThumbnails::dispatch($posts);
		
		// Remove Distance from Request
		$this->removeDistanceFromRequest();
		
		// If the request is made from the app's Web environment,
		// use the Web URL as the pagination's base URL
		$posts = setPaginationBaseUrl($posts);
		
		// Add eventual web queries to $queriesToRemove
		$queriesToRemove = array_merge($queriesToRemove, $this->webGlobalQueries);
		$webController = null;
		if (request()->hasHeader('X-WEB-CONTROLLER')) {
			$webController = request()->header('X-WEB-CONTROLLER');
		}
		if (!empty($webController)) {
			$webQueries = $this->webQueriesPerController[$webController] ?? [];
			$queriesToRemove = array_merge($queriesToRemove, $webQueries);
		}
		
		// Append request queries in the pagination links
		$query = !empty($queriesToRemove) ? request()->except($queriesToRemove) : request()->query();
		$query = collect($query)->map(fn ($item) => (is_null($item) ? '' : $item))->toArray();
		$posts->appends($query);
		
		// Get Count Results
		$totalCountId = 0;
		$count[$totalCountId] = $posts->total();
		if (config('settings.listing_form.show_listing_type')) {
			$postTypeId = request()->input('type');
			if (!empty($postTypeId) && isset($count[$postTypeId])) {
				$total = 0;
				foreach ($count as $typeId => $countItems) {
					if ($typeId != $totalCountId) {
						$total += $countItems;
					}
				}
				$count[$totalCountId] = $total;
			}
		}
		
		// Wrap the listings for API calls
		$postsCollection = new EntityCollection('PostController', $posts);
		$message = ($posts->count() <= 0) ? t('no_posts_found') : null;
		$postsResult = $postsCollection->toResponse(request())->getData(true);
		
		// Add 'user' object in preSearch (If available)
		$this->preSearch['user'] = null;
		$searchBasedOnUser = request()->anyFilled(['userId', 'username']);
		if ($searchBasedOnUser) {
			$this->preSearch['user'] = data_get($postsResult, 'data.0.user');
		}
		
		if (request()->filled('tag')) {
			$this->preSearch['tag'] = request()->input('tag');
		}
		
		$this->preSearch['distance'] = [
			'default' => self::$defaultDistance,
			'current' => self::$distance,
			'max'     => self::$maxDistance,
		];
		
		// Results Data
		return [
			'message'   => $message,
			'count'     => $count,
			'posts'     => $postsResult,
			'distance'  => self::$distance,
			'preSearch' => $this->preSearch,
			'tags'      => $this->getPostsTags($posts),
		];
	}
	
	/**
	 * Count the results
	 *
	 * @return array
	 */
	private function countFetch(): array
	{
		$count = [];
		
		$postTypes = PostType::all();
		if (empty($postTypes)) {
			return $count;
		}
		
		// Count entries by post type
		$pattern = '/`post_type_id`\s*=\s*[\d\']+\s+/ui';
		foreach ($postTypes as $postType) {
			$postTypeId = data_get($postType, 'id');
			$iPosts = clone $this->posts;
			
			$sql = DBTool::getRealSql($iPosts->toSql(), $iPosts->getBindings());
			
			if (preg_match($pattern, $sql)) {
				$sql = preg_replace($pattern, '`post_type_id` = ' . $postTypeId . ' ', $sql);
			} else {
				$iPosts->where('post_type_id', $postTypeId);
				$sql = DBTool::getRealSql($iPosts->toSql(), $iPosts->getBindings());
			}
			
			try {
				$sql = 'SELECT COUNT(*) AS total FROM (' . $sql . ') AS x';
				$result = DB::select($sql);
			} catch (\Throwable $e) {
				// dd($e->getMessage()); // Debug!
				$result = null;
			}
			
			$count[$postTypeId] = isset($result[0]) ? (int)$result[0]->total : 0;
		}
		
		return $count;
	}
	
	/**
	 * Get found listings' tags (per page)
	 *
	 * @param $posts
	 * @return array|string|null
	 */
	private function getPostsTags($posts): array|string|null
	{
		if (!config('settings.listings_list.show_listings_tags')) {
			return null;
		}
		
		if ($posts->count() > 0) {
			$tags = [];
			foreach ($posts as $post) {
				if (!empty($post->tags)) {
					$tags = array_merge($tags, $post->tags);
				}
			}
			
			return tagCleaner($tags);
		}
		
		return null;
	}
	
	/**
	 * Bind valid values for the input's elements
	 *
	 * @param array $array
	 * @return array
	 */
	private function bindValidValuesForInput(array $array = []): array
	{
		// cacheExpiration
		$cacheExpiration = data_get($array, 'cacheExpiration');
		$cacheExpirationIsValid = !empty($cacheExpiration) && is_numeric($cacheExpiration);
		if (!$cacheExpirationIsValid) {
			$array['cacheExpiration'] = self::$cacheExpiration;
		}
		
		// op
		$array['op'] = data_get($array, 'op', 'default');
		
		// perPage
		$array['perPage'] = getNumberOfItemsPerPage('posts', data_get($array, 'perPage'));
		
		// orderBy
		// Avoid to set an arbitrary orderBy value (set value to null instead)
		// $orderBy = data_get($array, 'orderBy');
		// $array['orderBy'] = !empty($orderBy) ? $orderBy : null;
		
		return $array;
	}
}
