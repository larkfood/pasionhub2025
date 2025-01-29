@php
	$userStats ??= [];
	
	$countThreads = data_get($userStats, 'threads.all') ?? 0;
	$postsVisits = data_get($userStats, 'posts.visits') ?? 0;
	$countPosts = (data_get($userStats, 'posts.published') ?? 0)
		+ (data_get($userStats, 'posts.archived') ?? 0)
		+ (data_get($userStats, 'posts.pendingApproval') ?? 0);
	$countFavoritePosts = data_get($userStats, 'posts.favourite') ?? 0;
	
	$countThreads = (int)$countThreads;
	$postsVisits = (int)$postsVisits;
	$countPosts = (int)$countPosts;
	$countFavoritePosts = (int)$countFavoritePosts;
@endphp

<div class="inner-box default-inner-box">
	<div class="row">
		<div class="col-md-5 col-sm-4 col-12">
			<h3 class="no-padding text-center-480 useradmin">
				<a href="">
					<img id="userImg" class="userImg" src="{{ $authUser->photo_url }}" alt="user">&nbsp;
					{{ $authUser->name }}
				</a>
			</h3>
		</div>
		<div class="col-md-7 col-sm-8 col-12">
			<div class="header-data text-center-xs">
				{{-- Threads Stats --}}
				<div class="hdata">
					<div class="mcol-left">
						<i class="fa-solid fa-envelope ln-shadow"></i>
					</div>
					<div class="mcol-right">
						{{-- Number of messages --}}
						<p>
							<a href="{{ url('account/messages') }}">
								{{ \App\Helpers\Num::short($countThreads) }}
								<em>
									{{ trans_choice('global.count_mails', getPlural($countThreads), [], config('app.locale')) }}
								</em>
							</a>
						</p>
					</div>
					<div class="clearfix"></div>
				</div>
				
				{{-- Traffic Stats --}}
				<div class="hdata">
					<div class="mcol-left">
						<i class="fa-regular fa-eye ln-shadow"></i>
					</div>
					<div class="mcol-right">
						{{-- Number of visitors --}}
						<p>
							<a href="{{ url('account/posts/list') }}">
								{{ \App\Helpers\Num::short($postsVisits) }}
								<em>
									{{ trans_choice('global.count_visits', getPlural($postsVisits), [], config('app.locale')) }}
								</em>
							</a>
						</p>
					</div>
					<div class="clearfix"></div>
				</div>
				
				{{-- Listings Stats --}}
				<div class="hdata">
					<div class="mcol-left">
						<i class="fa-solid fa-bullhorn ln-shadow"></i>
					</div>
					<div class="mcol-right">
						{{-- Number of listings --}}
						<p>
							<a href="{{ url('account/posts/list') }}">
								{{ \App\Helpers\Num::short($countPosts) }}
								<em>
									{{ trans_choice('global.count_listings', getPlural($countPosts), [], config('app.locale')) }}
								</em>
							</a>
						</p>
					</div>
					<div class="clearfix"></div>
				</div>
				
				{{-- Favorites Stats --}}
				<div class="hdata">
					<div class="mcol-left">
						<i class="fa-regular fa-user ln-shadow"></i>
					</div>
					<div class="mcol-right">
						{{-- Number of favorites --}}
						<p>
							<a href="{{ url('account/posts/favourite') }}">
								{{ \App\Helpers\Num::short($countFavoritePosts) }}
								<em>
									{{ trans_choice('global.count_favorites', getPlural($countFavoritePosts), [], config('app.locale')) }}
								</em>
							</a>
						</p>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
</div>
