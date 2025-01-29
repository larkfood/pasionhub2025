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

namespace App\Http\Resources;

use App\Models\ThreadMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->id)) return [];
		
		$entity = [
			'id' => $this->id,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		$entity['updated_at'] = $this->updated_at ?? null;
		$entity['latest_message'] = $this->latest_message ?? null;
		$entity['p_is_unread'] = $this->p_is_unread ?? null;
		$entity['p_creator'] = $this->p_creator ?? [];
		$entity['p_is_important'] = $this->p_is_important ?? null;
		
		$embed = explode(',', request()->input('embed'));
		
		$authUser = auth('sanctum')->check() ? auth('sanctum')->user() : null;
		
		if (in_array('user', $embed)) {
			if (!empty($authUser)) {
				$entity['user'] = new UserResource($authUser);
			}
		}
		
		if (in_array('post', $embed)) {
			$entity['post'] = new PostResource($this->whenLoaded('post'));
		}
		
		if (in_array('messages', $embed) && str_contains(currentRouteAction(), 'Api\ThreadController@show')) {
			// Get the Thread's Messages
			$messages = collect();
			if (!empty($authUser) && isset($authUser->id)) {
				$messages = ThreadMessage::query()
					->notDeletedByUser($authUser->id)
					->where('thread_id', $this->id)
					->with('user')
					->orderByDesc('id');
			}
			$messages = $messages->paginate(request()->input('perPage', 10));
			
			$messagesCollection = new EntityCollection('ThreadMessageController', $messages);
			$message = ($messages->count() <= 0) ? t('no_messages_found') : null;
			$entity['messages'] = apiResponse()->withCollection($messagesCollection, $message)->getData(true);
		}
		
		if (in_array('participants', $embed)) {
			$entity['participants'] = UserResource::collection($this->whenLoaded('users'));
		}
		
		return $entity;
	}
}
