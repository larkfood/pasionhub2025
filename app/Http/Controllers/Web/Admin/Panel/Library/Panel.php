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

namespace App\Http\Controllers\Web\Admin\Panel\Library;

use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Access;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\AutoFocus;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\AutoSet;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Buttons;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Columns;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Create;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Delete;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\FakeColumns;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\FakeFields;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Fields;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Filters;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Query;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Read;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Reorder;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Search;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Update;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class Panel
{
	use Access, Read, Search, Create, Update, Delete, Columns, Fields, Query, Reorder, Buttons, AutoSet, AutoFocus, FakeColumns, FakeFields, Filters;
	
	// ----------------
	// xPanel variables
	// ----------------
	// These variables are passed to the PANEL views, inside the $panel variable.
	// All variables are public, so they can be modified from your EntityController.
	// All functions and methods are also public, so they can be used in your EntityController to modify these variables.
	
	public Model $model; // what's the namespace for your entity's model
	public string $route = ''; // what route have you defined for your entity? used for links.
	public string $entityName = 'entry'; // what name will show up on the buttons, in singural (ex: Add entity)
	public string $entityNamePlural = 'entries'; // what name will show up on the buttons, in plural (ex: Delete 5 entities)
	
	public bool $parentEntity = false;
	public string $parentRoute = '';
	public ?string $parentKeyField = null;
	public string $parentEntityName = 'entry';
	public string $parentEntityNamePlural = 'entries';
	
	public Request $request;
	
	public array $access = ['list', 'create', 'update', 'delete', /*'show'*/];
	
	public bool $reorder = false;
	public $reorderLabel = false;
	public int $reorderMaxLevel = 3;
	
	public bool $details_row = false;
	public bool $exportButtons = false;
	public bool $hideSearchBar = false;
	
	public array $columns = []; // Define the columns for the table view as an array;
	public array $createFields = []; // Define the fields for the "Add new entry" view as an array;
	public array $updateFields = []; // Define the fields for the "Edit entry" view as an array;
	
	public Builder $query;
	public $entry;
	public $buttons;
	public array $dbColumnTypes = [];
	public int|bool $defaultPageLength = false;
	
	// TONE FIELDS - TODO: find out what he did with them, replicate or delete
	public array $sort = [];
	
	public bool $disableSyncPivot = false;
	
	// The following methods are used in CrudController or your EntityController to manipulate the variables above.
	
	// ------------------------------------------------------
	// BASICS - model, route, entityName, entityNamePlural
	// ------------------------------------------------------
	
	/**
	 * This function binds the CRUD to its corresponding Model (which extends Eloquent).
	 * All Create-Read-Update-Delete operations are done using that Eloquent Collection.
	 *
	 * @param $modelNamespace
	 */
	public function setModel($modelNamespace): void
	{
		if (!class_exists($modelNamespace)) {
			abort(500, "The model '{$modelNamespace}' does not exist.");
		}
		
		$this->model = new $modelNamespace();
		$this->query = $this->model->select('*');
	}
	
	/**
	 * Get the corresponding Eloquent Model for the CrudController, as defined with the setModel() function;.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getModel(): Model
	{
		return $this->model;
	}
	
	/**
	 * Get the database connection, as specified in the .env file or overwritten by the property on the model.
	 *
	 * @return \Illuminate\Database\Schema\Builder
	 */
	private function getSchema(): \Illuminate\Database\Schema\Builder
	{
		return Schema::setConnection($this->getModel()->getConnection());
	}
	
	/**
	 * Set the route for this CRUD.
	 * Ex: admin/article.
	 *
	 * @param [string] Route name.
	 * @param [array] Parameters.
	 */
	public function setRoute($route): void
	{
		$this->route = $route;
		$this->initButtons();
	}
	
	public function setParentRoute($route): void
	{
		$this->parentRoute = $route;
		$this->initButtons();
	}
	
	/**
	 * @param $field
	 */
	public function setParentKeyField($field): void
	{
		$this->parentKeyField = $field;
	}
	
	/**
	 * @return string|null
	 */
	public function getParentKeyField(): ?string
	{
		return $this->parentKeyField;
	}
	
	/**
	 * @param $id
	 * @param $childId
	 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
	 */
	public function getEntryWithParentAndChildKeys($id, $childId)
	{
		$entry = null;
		
		$parentKeyField = $this->getParentKeyField();
		if (!empty($parentKeyField)) {
			try {
				$entry = $this->model->query()
					->where($parentKeyField, $id)
					->where($this->model->getKeyName(), $childId)
					->first();
			} catch (\Throwable $e) {
				abort(500, $e->getMessage());
			}
			
			if (empty($entry)) {
				abort(404, 'Entry not found!');
			}
		}
		
		return $entry;
	}
	
	/**
	 * Set the route for this CRUD using the route name.
	 * Ex: admin.article.
	 *
	 * @param $route
	 * @param array $parameters
	 * @return void
	 * @throws \Exception
	 */
	public function setRouteName($route, array $parameters = []): void
	{
		$completeRoute = $route . '.index';
		
		if (!Route::has($completeRoute)) {
			throw new \Exception('There are no routes for this route name.', 404);
		}
		
		$this->route = route($completeRoute, $parameters);
		$this->initButtons();
	}
	
	/**
	 * Get the current CrudController route.
	 *
	 * Can be defined in the CrudController with:
	 * - $this->crud->setRoute(admin_uri('article'))
	 * - $this->crud->setRouteName(admin_uri().'.article')
	 * - $this->crud->route = admin_uri("article")
	 *
	 * @return string
	 */
	public function getRoute(): string
	{
		return $this->route;
	}
	
	/**
	 * Set the entity name in singular and plural.
	 * Used all over the CRUD interface (header, add button, reorder button, breadcrumbs).
	 *
	 * @param $singular
	 * @param $plural
	 */
	public function setEntityNameStrings($singular, $plural): void
	{
		$this->entityName = $singular;
		$this->entityNamePlural = $plural;
	}
	
	public function setParentEntityNameStrings($singular, $plural): void
	{
		$this->parentEntityName = $singular;
		$this->parentEntityNamePlural = $plural;
	}
	
	/**
	 * Disable syncPivot() feature in the update() method
	 */
	public function disableSyncPivot(): void
	{
		$this->disableSyncPivot = true;
	}
	
	/**
	 * @return bool
	 */
	public function isEnabledSyncPivot(): bool
	{
		return !($this->disableSyncPivot == true);
	}
	
	// ----------------------------------
	// Miscellaneous functions or methods
	// ----------------------------------
	
	/**
	 * Return the first element in an array that has the given 'type' attribute.
	 *
	 * @param $type
	 * @param $array
	 * @return \Closure|mixed|null
	 */
	public function getFirstOfItsTypeInArray($type, $array)
	{
		return Arr::first($array, function ($item) use ($type) {
			return $item['type'] == $type;
		});
	}
	
	// ------------
	// TONE FUNCTIONS - UNDOCUMENTED, UNTESTED, SOME MAY BE USED IN THIS FILE
	// ------------
	//
	// TODO:
	// - figure out if they are really needed
	// - comments inside the function to explain how they work
	// - write docblock for them
	// - place in the correct section above (CREATE, READ, UPDATE, DELETE, ACCESS, MANIPULATION)
	
	public function sync($type, $fields, $attributes): void
	{
		if (!empty($this->{$type})) {
			$this->{$type} = array_map(function ($field) use ($fields, $attributes) {
				if (in_array($field['name'], (array)$fields)) {
					$field = array_merge($field, $attributes);
				}
				
				return $field;
			}, $this->{$type});
		}
	}
	
	public function setSort($items, $order): void
	{
		$this->sort[$items] = $order;
	}
	
	public function sort($items)
	{
		if (array_key_exists($items, $this->sort)) {
			$elements = [];
			
			foreach ($this->sort[$items] as $item) {
				if (is_numeric($key = array_search($item, array_column($this->{$items}, 'name')))) {
					$elements[] = $this->{$items}[$key];
				}
			}
			
			return $this->{$items} = array_merge($elements, array_filter($this->{$items}, function ($item) use ($items) {
				return !in_array($item['name'], $this->sort[$items]);
			}));
		}
		
		return $this->{$items};
	}
	
	/**
	 * Get the Eloquent Model name from the given relation definition string.
	 *
	 * @param $relationString String Relation string. A dot notation can be used to chain multiple relations.
	 *
	 * @return string relation model name
	 * @example For a given string 'company' and a relation between App/Models/User and App/Models/Company, defined by a
	 *          company() method on the user model, the 'App/Models/Company' string will be returned.
	 *
	 * @example For a given string 'company.address' and a relation between App/Models/User, App/Models/Company and
	 *          App/Models/Address defined by a company() method on the user model and an address() method on the
	 *          company model, the 'App/Models/Address' string will be returned.
	 *
	 */
	private function getRelationModel($relationString): string
	{
		$result = array_reduce(explode('.', $relationString), function ($obj, $method) {
			return $obj->$method()->getRelated();
		}, $this->model);
		
		return get_class($result);
	}
}
