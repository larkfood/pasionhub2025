{{-- Fix dark mode view when skin is applied --}}
<style>
@if (isset($primaryBgColor) && !empty($primaryBgColor))
/* === Skin === */
	{{-- ========= START BTN ========= --}}
	{{-- .btn-primary --}}
	html[theme="dark"] .skin .btn-primary {
		color: {{ $primaryColor }};
		background-color: {{ $primaryBgColor }};
		border-color: {{ $primaryBgColor }};
	}
	html[theme="dark"] .skin .btn-primary:hover,
	html[theme="dark"] .skin .btn-primary:focus,
	html[theme="dark"] .skin .btn-primary:active,
	html[theme="dark"] .skin .btn-primary:active:focus,
	html[theme="dark"] .skin .btn-primary.active,
	html[theme="dark"] .skin .btn-primary.active:focus,
	html[theme="dark"] .skin .show > .btn-primary.dropdown-toggle,
	html[theme="dark"] .skin .open .dropdown-toggle.btn-primary {
		color: {{ $primaryColor }};
		background-color: {{ $primaryBgColor10 }};
		border-color: {{ $primaryBgColor10 }};
		background-image: none;
	}
	
	{{-- .btn-primary-dark --}}
	html[theme="dark"] .skin .btn-primary-dark {
		color: {{ $primaryDarkColor }};
		background-color: {{ $primaryDarkBgColor }};
		border-color: {{ $primaryDarkBgColor }};
	}
	html[theme="dark"] .skin .btn-primary-dark:hover,
	html[theme="dark"] .skin .btn-primary-dark:focus,
	html[theme="dark"] .skin .btn-primary-dark:active,
	html[theme="dark"] .skin .btn-primary-dark:active:focus,
	html[theme="dark"] .skin .btn-primary-dark.active,
	html[theme="dark"] .skin .btn-primary-dark.active:focus,
	html[theme="dark"] .skin .show > .btn-primary-dark.dropdown-toggle,
	html[theme="dark"] .skin .open .dropdown-toggle.btn-primary-dark {
		color: {{ $primaryDarkColor }};
		background-color: {{ $primaryDarkBgColor10 }};
		border-color: {{ $primaryDarkBgColor10 }};
		background-image: none;
	}
	
	{{-- .btn-outline-primary --}}
	html[theme="dark"] .skin .btn-outline-primary {
		color: {{ $primaryBgColor }};
		background-color: {{ $primaryColor }};
		border-color: {{ $primaryBgColor }};
	}
	html[theme="dark"] .skin .btn-outline-primary:hover,
	html[theme="dark"] .skin .btn-outline-primary:focus,
	html[theme="dark"] .skin .btn-outline-primary:active,
	html[theme="dark"] .skin .btn-outline-primary:active:focus,
	html[theme="dark"] .skin .btn-outline-primary.active,
	html[theme="dark"] .skin .btn-outline-primary.active:focus,
	html[theme="dark"] .skin .show > .btn-outline-primary.dropdown-toggle,
	html[theme="dark"] .skin .open .dropdown-toggle.btn-outline-primary {
		color: {{ $primaryColor }};
		background-color: {{ $primaryBgColor }};
		border-color: {{ $primaryBgColor }};
		background-image: none;
	}
	
	{{-- .btn-primary.btn-gradient --}}
	html[theme="dark"] .skin .btn-primary.btn-gradient {
		color: {{ $primaryColor }};
		background: -webkit-linear-gradient(292deg, {{ $primaryBgColor20d }} 44%, {{ $primaryBgColor }} 85%);
		background: -moz-linear-gradient(292deg, {{ $primaryBgColor20d }} 44%, {{ $primaryBgColor }} 85%);
		background: -o-linear-gradient(292deg, {{ $primaryBgColor20d }} 44%, {{ $primaryBgColor }} 85%);
		background: linear-gradient(158deg, {{ $primaryBgColor20d }} 44%, {{ $primaryBgColor }} 85%);
		border-color: {{ $primaryBgColor20d }};
		-webkit-transition: all 0.25s linear;
		-moz-transition: all 0.25s linear;
		-o-transition: all 0.25s linear;
		transition: all 0.25s linear;
	}
	html[theme="dark"] .skin .btn-primary.btn-gradient:hover,
	html[theme="dark"] .skin .btn-primary.btn-gradient:focus,
	html[theme="dark"] .skin .btn-primary.btn-gradient:active,
	html[theme="dark"] .skin .btn-primary.btn-gradient:active:focus,
	html[theme="dark"] .skin .btn-primary.btn-gradient.active,
	html[theme="dark"] .skin .btn-primary.btn-gradient.active:focus,
	html[theme="dark"] .skin .show > .btn-primary.btn-gradient.dropdown-toggle,
	html[theme="dark"] .skin .open .dropdown-toggle.btn-primary.btn-gradient {
		color: {{ $primaryColor }};
		background-color: {{ $primaryBgColor }};
		border-color: {{ $primaryBgColor }};
		background-image: none;
	}
	html[theme="dark"] .skin .btn-check:focus+.btn-primary.btn-gradient,
	html[theme="dark"] .skin .btn-primary.btn-gradient:focus,
	html[theme="dark"] .skin .btn-primary.btn-gradient.focus {
		box-shadow: 0 0 0 2px {{ $primaryBgColor50 }};
	}
@endif
</style>
