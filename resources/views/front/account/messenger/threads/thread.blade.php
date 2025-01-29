@php
	$thread ??= [];
@endphp
<div class="list-group-item{{ data_get($thread, 'p_is_unread') ? '' : ' seen' }}">
	<div class="form-check">
		<div class="custom-control ps-0">
			<input type="checkbox" name="entries[]" value="{{ data_get($thread, 'id') }}">
			<label class="control-label" for="entries"></label>
		</div>
	</div>
	
	<a href="{{ url('account/messages/' . data_get($thread, 'id')) }}" class="list-box-user">
		<img src="{{ url(data_get($thread, 'p_creator.photo_url', '')) }}" alt="{{ data_get($thread, 'p_creator.name') }}">
	</a>
	<a href="{{ url('account/messages/' . data_get($thread, 'id')) }}" class="list-box-content">
		<span class="title">{{ data_get($thread, 'subject') }}</span>
		<span class="name">
			@php
				$userIsOnline = isUserOnline(data_get($thread, 'p_creator')) ? 'online' : 'offline';
			@endphp
			<i class="fa-solid fa-circle {{ $userIsOnline }}"></i> {{ data_get($thread, 'p_creator.name') }}
		</span>
		<div class="message-text">
			{{ str(data_get($thread, 'latest_message.body') ?? '')->limit(125) }}
		</div>
		<div class="time text-muted">{{ data_get($thread, 'created_at_formatted') }}</div>
	</a>
	
	<div class="list-box-action">
		@if (data_get($thread, 'p_is_important'))
			<a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsNotImportant') }}"
			   data-bs-toggle="tooltip"
			   data-bs-placement="top"
			   class="markAsNotImportant"
			   title="{{ t('Mark as not important') }}"
			>
				<i class="fa-solid fa-star"></i>
			</a>
		@else
			<a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsImportant') }}"
			   data-bs-toggle="tooltip"
			   data-bs-placement="top"
			   class="markAsImportant"
			   title="{{ t('Mark as important') }}"
			>
				<i class="fa-regular fa-star"></i>
			</a>
		@endif
		<a href="{{ url('account/messages/' . data_get($thread, 'id') . '/delete') }}"
		   data-bs-toggle="tooltip"
		   data-bs-placement="top"
		   title="{{ t('Delete') }}"
		>
			<i class="fa-solid fa-trash"></i>
		</a>
		@if (data_get($thread, 'p_is_unread'))
			<a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsRead') }}"
			   class="markAsRead"
			   data-bs-toggle="tooltip"
			   data-bs-placement="top"
			   title="{{ t('Mark as read') }}"
			>
				<i class="fa-solid fa-envelope"></i>
			</a>
		@else
			<a href="{{ url('account/messages/' . data_get($thread, 'id') . '/actions?type=markAsUnread') }}"
			   class="markAsRead"
			   data-bs-toggle="tooltip"
			   data-bs-placement="top"
			   title="{{ t('Mark as unread') }}"
			>
				<i class="fa-solid fa-envelope-open"></i>
			</a>
		@endif
	</div>
</div>
