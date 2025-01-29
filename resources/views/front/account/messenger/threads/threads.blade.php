<div class="list-group">
	
	@if (!empty($threads) && $totalThreads > 0)
		@foreach($threads as $thread)
			@include('front.account.messenger.threads.thread', ['thread' => $thread])
		@endforeach
	@else
		@include('front.account.messenger.threads.no-threads')
	@endif

</div>
