@php
	$sectionOptions = $latestListingsOptions ?? [];
	$sectionData ??= [];
	$widget = (array)data_get($sectionData, 'latest');
	$widgetType = (data_get($sectionOptions, 'items_in_carousel') == '1') ? 'carousel' : 'normal';
@endphp
@include('front.search.inc.posts.widget.' . $widgetType, [
	'widget'         => $widget,
	'sectionOptions' => $sectionOptions
])
