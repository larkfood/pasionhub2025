@php
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;
	
	$thread ??= [];
	$message ??= [];
	
	$filePath = data_get($message, 'file_path');
@endphp
@if ($authUserId == data_get($message, 'user.id'))
	<div class="chat-item object-me">
		<div class="chat-item-content">
			<div class="msg">
				{!! urlsToLinks(nlToBr(data_get($message, 'body')), ['class' => 'text-light']) !!}
				@if (!empty($filePath) && $disk->exists($filePath))
					@php
						$mt2Class = !empty(trim(data_get($message, 'body'))) ? 'mt-2' : '';
					@endphp
					<div class="{{ $mt2Class }}">
						<i class="fa-solid fa-paperclip" aria-hidden="true"></i>
						<a class="text-light"
						   href="{{ privateFileUrl($filePath, null) }}"
						   target="_blank"
						   data-bs-toggle="tooltip"
						   data-bs-placement="left"
						   title="{{ basename($filePath) }}"
						>
							{{ str($filePath)->basename()->limit(20) }}
						</a>
					</div>
				@endif
			</div>
			<span class="time-and-date">
				{{ data_get($message, 'created_at_formatted') }}
				@php
					$recipient = data_get($message, 'p_recipient');
					
					$threadUpdatedAt = new \Illuminate\Support\Carbon(data_get($thread, 'updated_at'));
					$threadUpdatedAt->timezone(\App\Helpers\Date::getAppTimeZone());
					
					$recipientLastRead = new \Illuminate\Support\Carbon(data_get($recipient, 'last_read'));
					$recipientLastRead->timezone(\App\Helpers\Date::getAppTimeZone());
					
					$threadIsUnreadByThisRecipient = (
						!empty($recipient)
						&& (
							data_get($recipient, 'last_read') === null
							|| $threadUpdatedAt->gt($recipientLastRead)
						)
					);
				@endphp
				@if ($threadIsUnreadByThisRecipient)
					&nbsp;<i class="fa-solid fa-check-double"></i>
				@endif
			</span>
		</div>
	</div>
@else
	<div class="chat-item object-user">
		<div class="object-user-img">
			<a href="{{ urlGen()->user(data_get($message, 'user')) }}">
				<img src="{{ url(data_get($message, 'user.photo_url')) }}" alt="{{ data_get($message, 'user.name') }}">
			</a>
		</div>
		<div class="chat-item-content">
			<div class="chat-item-content-inner">
				<div class="msg bg-white">
					{!! urlsToLinks(nlToBr(data_get($message, 'body'))) !!}
					@if (!empty($filePath) && $disk->exists($filePath))
						@php
							$mt2Class = !empty(trim(data_get($message, 'body'))) ? 'mt-2' : '';
						@endphp
						<div class="{{ $mt2Class }}">
							<i class="fa-solid fa-paperclip" aria-hidden="true"></i>
							<a class=""
							   href="{{ privateFileUrl($filePath, null) }}"
							   target="_blank"
							   data-bs-toggle="tooltip"
							   data-bs-placement="left"
							   title="{{ basename($filePath) }}"
							>
								{{ str($filePath)->basename()->limit(20) }}
							</a>
						</div>
					@endif
				</div>
				@php
					$userIsOnline = isUserOnline(data_get($message, 'user'));
				@endphp
				<span class="time-and-date ms-0">
					@if ($userIsOnline)
						<i class="fa-solid fa-circle color-success"></i>&nbsp;
					@endif
					{{ data_get($message, 'created_at_formatted') }}
				</span>
			</div>
		</div>
	</div>
@endif
