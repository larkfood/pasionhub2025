@php
	$authUser ??= null;
@endphp
@if (doesUserHavePermission($authUser, 'language-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ admin_url('languages') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.languages') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'section-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ admin_url('sections') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.homepage') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'meta-tag-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ admin_url('meta_tags') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.meta tags') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'package-list')|| userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="#packages" class="sidebar-link has-arrow">
			<i class="mdi mdi-adjust"></i> <span class="hide-menu">{{ trans('admin.packages') }}</span>
		</a>
		<ul aria-expanded="false" class="collapse second-level">
			<li class="sidebar-item">
				<a href="{{ admin_url('packages/promotion') }}" class="sidebar-link">
					<i class="mdi mdi-adjust"></i>
					<span class="hide-menu">{{ trans('admin.promotion') }}</span>
				</a>
			</li>
			<li class="sidebar-item">
				<a href="{{ admin_url('packages/subscription') }}" class="sidebar-link">
					<i class="mdi mdi-adjust"></i>
					<span class="hide-menu">{{ trans('admin.subscription') }}</span>
				</a>
			</li>
			<li class="sidebar-item">&nbsp;</li>
		</ul>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'payment-method-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ admin_url('payment_methods') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.payment methods') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'advertising-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ admin_url('advertisings') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.advertising') }}</span>
		</a>
	</li>
@endif
@if (
	doesUserHavePermission($authUser, 'country-list')
	|| doesUserHavePermission($authUser, 'currency-list')
	|| userHasSuperAdminPermissions()
)
	<li class="sidebar-item">
		<a href="#international" class="sidebar-link has-arrow">
			<i class="fa-solid fa-globe"></i> <span class="hide-menu">{{ trans('admin.international') }}</span>
		</a>
		<ul aria-expanded="false" class="collapse second-level">
			@if (doesUserHavePermission($authUser, 'country-list') || userHasSuperAdminPermissions())
				<li class="sidebar-item">
					<a href="{{ admin_url('countries') }}" class="sidebar-link">
						<i class="mdi mdi-adjust"></i>
						<span class="hide-menu">{{ trans('admin.countries') }}</span>
					</a>
				</li>
			@endif
			@if (doesUserHavePermission($authUser, 'currency-list') || userHasSuperAdminPermissions())
				<li class="sidebar-item">
					<a href="{{ admin_url('currencies') }}" class="sidebar-link">
						<i class="mdi mdi-adjust"></i>
						<span class="hide-menu">{{ trans('admin.currencies') }}</span>
					</a>
				</li>
			@endif
			<li class="sidebar-item">&nbsp;</li>
		</ul>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'blacklist-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ admin_url('blacklists') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.blacklist') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'report-type-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ admin_url('report_types') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.report types') }}</span>
		</a>
	</li>
@endif
