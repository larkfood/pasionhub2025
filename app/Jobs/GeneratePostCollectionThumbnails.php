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

namespace App\Jobs;

use App\Services\Thumbnail\PostThumbnail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/*
 * Running the Queue Worker
 * Doc: https://laravel.com/docs/11.x/queues#running-the-queue-worker
 * php artisan queue:work
 * php artisan queue:work -v
 */

class GeneratePostCollectionThumbnails implements ShouldQueue
{
	use Queueable;
	
	protected LengthAwarePaginator|Collection|EloquentCollection $posts;
	
	/**
	 * Create a new job instance.
	 *
	 * @param \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection $posts
	 */
	public function __construct(LengthAwarePaginator|Collection|EloquentCollection $posts)
	{
		$this->posts = $posts;
		
		$this->onQueue('thumbs');
	}
	
	/**
	 * Execute the job.
	 *
	 * @param \App\Services\Thumbnail\PostThumbnail $thumbnailService
	 * @return void
	 * @throws \Throwable
	 */
	public function handle(PostThumbnail $thumbnailService): void
	{
		$thumbnailService->generateForCollection($this->posts);
	}
}
