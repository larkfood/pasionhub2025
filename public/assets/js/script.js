/* Prevent errors if these variables are missing. */

/* Categories Parameters */
if (typeof maxSubCats === 'undefined') {
	var maxSubCats = 3;
}

// Modernizr touch event detect
function isFromTouchDevice() {
	return 'ontouchstart' in window;
}

var isTouchDevice = isFromTouchDevice();

/* console.log('is touch device : ',isTouchDevice); */

onDocumentReady((event) => {
	
	/* Enable tooltips everywhere */
	initElementTooltips(getHtmlElement()); /* Default trigger: 'hover focus' */
	initElementTooltips(getHtmlElement(), {trigger: 'hover'}, 'tooltipHover');
	
	/* Enable poppers everywhere */
	initElementPopovers(getHtmlElement(), {html: true});
	
	/* Change a tooltip size in Bootstrap 4.x */
	const locSearchEl = document.getElementById('locSearch');
	if (locSearchEl) {
		const tooltipEvents = ['mouseover', 'mouseenter', 'mouseleave', 'mousemove'];
		tooltipEvents.forEach((event) => {
			locSearchEl.addEventListener(event, applyTooltipStyles);
		});
	}
	
	/* Check if RTL or LTR */
	let htmlEl = getHtmlElement();
	let isRTLEnabled = (htmlEl.getAttribute('dir') === 'rtl');
	
	/* SET HEADER HEIGHT AS PADDING-TOP to WRAPPER */
	
	let wrapper = $('#wrapper');
	let navbarSite = $('.navbar-site');
	let headerHeight = navbarSite.height();
	
	function setWrapperHeight() {
		wrapper.css('padding-top', headerHeight + 'px');
	}
	
	setWrapperHeight();
	
	/* ON SCROLL FADE OUT */
	
	function fadeOnScroll(target) {
		let targetEl = $('' + target + ''),
			targetElHeight = targetEl.outerHeight();
		$(document).scroll(function () {
			let scrollPercent = (targetElHeight - window.scrollY) / targetElHeight;
			scrollPercent >= 0 && (target.css("background-color", "rgba(0,0,0," + (1.1 - scrollPercent) + ")"))
		});
	}
	
	if (!isTouchDevice) {
		fadeOnScroll('.layer-bg');
	}
	
	
	/*==================================
	 Ajax Tab || CATEGORY PAGE
	 ==================================*/
	
	$(".nav-tabs li > a").click(function () {
		let thisEl = $(this);
		thisEl.closest('ul').find('li').removeClass('active');
		thisEl.parent('li').addClass('active');
	});
	
	/*
	 * IMPORTANT: Don't use this example ajax tab in production; this code is demo purpose.
	 * IMPORTANT: Don't use this example ajax tab in production; this code is demo purpose.
	 * ...
	 */
	
	/*==================================
	 List view clickable || CATEGORY
	 ==================================*/
	
	// List view, Grid view and compact view
	
	// var selector doesn't work on ajax tab category.hhml. This variables elements disable for V1.6
	// var listItem = $('.item-list');
	// var addDescBox = $('.item-list .add-desc-box');
	// var addsWrapper = $('.posts-wrapper');
	// ...
	
	
	if ($(this).width() < 767) {
		$(".event-category-list .event-item-col").each(function (index, element) {
			var eventFooter = $(this).find('.card-footer');
			var eventInfo = $(this).find('.card-event-info');
			//  $(this).find('.card-body').append(footer);
			$(this).find('.badge.price-tag').clone().insertAfter(eventInfo);
			eventFooter.clone().insertAfter(eventInfo);
		});
	}
	
	
	/*==================================
	 Global Plugins ||
	 ==================================*/
	hideMaxListItems('.long-list', {
		max: 8,
		speed: 500,
		moreText: langLayout.hideMaxListItems.moreText + ' ([COUNT])',
		lessText: langLayout.hideMaxListItems.lessText
	});
	hideMaxListItems('.long-list-user', {
		max: 12,
		speed: 500,
		moreText: langLayout.hideMaxListItems.moreText + ' ([COUNT])',
		lessText: langLayout.hideMaxListItems.lessText
	});
	hideMaxListItems('.long-list-home', {
		max: maxSubCats,
		speed: 500,
		moreText: langLayout.hideMaxListItems.moreText + ' ([COUNT])',
		lessText: langLayout.hideMaxListItems.lessText
	});
	
	/* Bootstrap Collapse + jQuery hideMaxListItem fix on mobile */
	$('.btn-cat-collapsed').click(function () {
		var targetSelector = $(this).data('target');
		var isExpanded = $(this).attr('aria-expanded');
		
		if (typeof isExpanded === 'undefined') {
			return false;
		}
		
		$(targetSelector).toggle('slow');
		
		if (isExpanded == 'true') {
			$('.cat-list ' + targetSelector).next('.maxlist-more').hide();
		} else {
			$('.cat-list ' + targetSelector).next('.maxlist-more').show();
		}
	});
	
	$(".niceselecter").niceSelect({ /* category list Short by */
		// customClass: "select-sort-by"
	});
	
	$(".scrollbar").niceScroll();  /* customs scroll plugin */
	
	// smooth scroll to the ID
	$(document).on('click', 'a.scrollto', function (event) {
		event.preventDefault();
		$('html, body').animate({
			scrollTop: $($.attr(this, 'href')).offset().top
		}, 500);
	});
	
	
	/*=======================================================================================
	 cat-collapse Homepage Category Responsive view
	 =======================================================================================*/
	
	var catCollapse = $('.cat-collapse');
	
	$(window).bind('resize load', function () {
		
		if ($(this).width() < 767) {
			catCollapse.collapse('hide');
			catCollapse.on('show.bs.collapse', function () {
				$(this).prev('.cat-title').find('.icon-down-open-big').addClass("active-panel");
			});
			
			catCollapse.on('hide.bs.collapse', function () {
				$(this).prev('.cat-title').find('.icon-down-open-big').removeClass("active-panel");
			})
			
		} else {
			$('#bd-docs-nav').collapse('show');
			catCollapse.collapse('show');
		}
		
	});
	
	/* DEMO PREVIEW */
	
	$(".tbtn").click(function () {
		$('.themeControll').toggleClass('active')
	});
	
	/* Jobs */
	
	$("input:radio").click(function () {
		if ($('input:radio#job-seeker:checked').length > 0) {
			$('.forJobSeeker').removeClass('hide');
			$('.forJobFinder').addClass('hide');
		} else {
			$('.forJobFinder').removeClass('hide');
			$('.forJobSeeker').addClass('hide')
		}
	});
	
	/* Change Direction based on template dir="RTL"  or dir="LTR" */
	
	var sidebarDirection = {};
	var sidebarDirectionClose = {};
	
	if (isRTLEnabled) {
		sidebarDirection = {right: '-251px'};
		sidebarDirectionClose = {right: '0'};
	} else {
		sidebarDirection = {left: '-251px'};
		sidebarDirectionClose = {left: '0'};
	}
	
	$(".filter-toggle").click(function () {
		$('.mobile-filter-sidebar')
		.prepend("<div class='closeFilter'>X</div>")
		.animate(sidebarDirectionClose, 250, "linear", function () {
		});
		$('.menu-overly-mask').addClass('is-visible');
	});
	
	$(".menu-overly-mask").click(function () {
		$(".mobile-filter-sidebar").animate(sidebarDirection, 250, "linear", function () {
		});
		$('.menu-overly-mask').removeClass('is-visible');
	});
	
	$(document).on('click', '.closeFilter', function () {
		$(".mobile-filter-sidebar").animate(sidebarDirection, 250, "linear", function () {
		});
		$('.menu-overly-mask').removeClass('is-visible');
	});
	
	/* cityName will replace with selected location/area from location modal */
	
	$('#browseLocations').on('shown.bs.modal', function (e) {
		$("ul.list-link li a").click(function () {
			$('ul.list-link li a').removeClass('active');
			$(this).addClass('active');
			$(".cityName").text($(this).text());
			$('#browseLocations').modal('hide');
		});
	});
	
	$("#checkAll").click(function () {
		$('.add-img-selector input:checkbox').not(this).prop('checked', this.checked);
	});
	
	var stickyScroller = function () {
		var intialscroll = 0;
		$(window).scroll(function (event) {
			var windowScroll = $(this).scrollTop();
			if (windowScroll > intialscroll) {
				/* downward-scrolling */
				navbarSite.addClass('stuck');
			} else {
				/* upward-scrolling */
				navbarSite.removeClass('stuck');
			}
			if (windowScroll < 450) {
				/* downward-scrolling */
				navbarSite.removeClass('stuck');
			}
			intialscroll = windowScroll;
		});
	};
	
	if (!isTouchDevice) {
		stickyScroller();
	}
	
	$('.dropdown-clear-filter').click(function (e) {
		let thisEl = $(this);
		thisEl.closest('.dropdown-menu').find('input[type="radio"]').prop('checked', false);
		thisEl.closest('.dropdown-menu').find('input[type="checkbox"]').prop('checked', false);
		e.stopPropagation();
	});
	
	$('.dropdown-menu.stay').click(function (e) {
		e.stopPropagation();
	});
	
	
	/* INBOX MESSAGE */
	/* Check 'assets/js/app/messenger.js' */
	
	/* Check New Messages */
	/* 60000 = 60 seconds (Timer) */
	if (typeof timerNewMessagesChecking !== 'undefined') {
		checkNewMessages();
		if (timerNewMessagesChecking > 0) {
			setInterval(() => checkNewMessages(), timerNewMessagesChecking);
		}
	}
	
	/* Toggle (Show|hide) Password Field Value */
	const togglePasswordLinkEls = document.querySelectorAll('.toggle-password-link');
	if (togglePasswordLinkEls.length > 0) {
		togglePasswordLinkEls.forEach((element) => {
			element.addEventListener('click', (e) => {
				e.preventDefault();
				togglePassword(e.target);
			});
		});
	}
	
	/* Data loading-mask pre-configuration */
	$.busyLoadSetup({
		background: 'rgba(0, 0, 0, 0.05)',
		animation: 'fade',
		spinner: 'pump',
		color: '#666',
		textPosition: 'left'
	});
});

jQuery.event.special.touchstart = {
	setup: function (_, ns, handle) {
		if (ns.includes("noPreventDefault")) {
			this.addEventListener("touchstart", handle, {passive: false});
		} else {
			this.addEventListener("touchstart", handle, {passive: true});
		}
	}
};

function createCustomSpinnerEl() {
	return $('<div>', {
		class: 'spinner-border',
		css: {'width': '30px', 'height': '30px'}
	});
}

/**
 * Change a tooltip size in Bootstrap 4.x
 */
function applyTooltipStyles() {
	const tooltipInnerEls = document.querySelectorAll('.tooltip-inner');
	if (tooltipInnerEls.length > 0) {
		tooltipInnerEls.forEach((element) => {
			element.style.width = "300px";
			element.style.maxWidth = "300px";
		});
	}
}

/**
 * Set Country Phone Code
 * @param countryCode
 * @param countries
 * @returns {boolean}
 */
function setCountryPhoneCode(countryCode, countries) {
	if (typeof countryCode === "undefined" || typeof countries === "undefined") return false;
	if (typeof countries[countryCode] === "undefined") return false;
	
	const phoneCountryEl = document.getElementById('phoneCountry');
	if (phoneCountryEl) {
		phoneCountryEl.innerHTML = countries[countryCode]['phone'];
	}
}

/**
 * Check Threads with New Messages
 */
function checkNewMessages() {
	let oldValue = $('.dropdown-toggle .count-threads-with-new-messages').html();
	if (typeof oldValue === 'undefined') {
		return false;
	}
	
	/* Make ajax call */
	let ajax = $.ajax({
		method: 'POST',
		url: siteUrl + '/account/messages/check-new',
		data: {
			'languageCode': languageCode,
			'oldValue': oldValue,
			'_token': $('input[name=_token]').val()
		}
	});
	ajax.done(function (data) {
		if (typeof data.logged === 'undefined') {
			return false;
		}
		
		/* Guest Users - Need to Log In */
		if (data.logged === 0 || data.logged === '0' || data.logged === '') {
			return false;
		}
		
		let counterBoxes = $('.count-threads-with-new-messages');
		
		/* Logged Users - Notification */
		if (data.countThreadsWithNewMessages > 0) {
			if (data.countThreadsWithNewMessages >= data.countLimit) {
				counterBoxes.html(data.countLimit + '+');
			} else {
				counterBoxes.html(data.countThreadsWithNewMessages);
			}
			counterBoxes.show();
		} else {
			counterBoxes.html('0').hide();
		}
		
		return false;
	});
}

/**
 * Get the Laravel CSRF Token
 * @param formFieldEl
 * @returns {string|null}
 */
function getCsrfToken(formFieldEl = null) {
	let token = null;
	
	/* Find the token from the _token hidden field */
	const _tokenEl = document.querySelector("input[name=_token]");
	if (_tokenEl) {
		token = _tokenEl.value;
	}
	
	/*
	 * If the token is not found, search it through the form data attribute
	 * Note: The form element can be handled by giving one of its fields
	 */
	if (isEmpty(token)) {
		if (isDomElement(formFieldEl)) {
			const tokenFormEl = formFieldEl.closest('form');
			if (tokenFormEl) {
				token = tokenFormEl.dataset.csrfToken || tokenFormEl.dataset.token || null;
			}
		}
	}
	
	return token;
}

/**
 * Toggle (Show|hide) Password Field Value
 * @param togglePasswordLinkIconEl
 */
function togglePassword(togglePasswordLinkIconEl) {
	const togglePasswordLinkEl = togglePasswordLinkIconEl.parentElement;
	const inputGroup = togglePasswordLinkEl.closest('.toggle-password-wrapper');
	const passwordFieldEls = inputGroup.querySelectorAll('input[type="password"], .is-password-field');
	
	if (passwordFieldEls.length > 0) {
		passwordFieldEls.forEach((element) => {
			if (element.type === 'password') {
				element.classList.add('is-password-field');
				element.type = 'text';
				togglePasswordLinkEl.innerHTML = '<i class="fa-regular fa-eye"></i>';
			} else {
				element.type = 'password';
				togglePasswordLinkEl.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
				element.classList.remove('is-password-field');
			}
		});
	}
}
