/**
 Usage:
 To scroll the document to a specific position:
 
 scrollToElement(document.documentElement, 0, 100, {
	 gap: {x: 0, y: 10},
	 animation: {
		 duration: 500,
		 complete: function() {
		    console.log('Scrolling complete');
		 },
		 step: function(progress) {
		    console.log('Scrolling step', progress);
		 }
	 }
 });
 
 To scroll a specific element:
 
 document.getElementById('myElement').scrollToElement(0, 100, {
	 gap: {x: 0, y: 10},
	 animation: {
		 duration: 500,
		 complete: function() {
		    console.log('Scrolling complete');
		 },
		 step: function(progress) {
		    console.log('Scrolling step', progress);
		 }
	 }
 });
 */
(function () {
	function scrollTo(element, x, y, options) {
		if (!(element instanceof Element)) {
			element = document.documentElement;
		}
		
		options = Object.assign({
			gap: {
				x: 0,
				y: 0
			},
			animation: {
				easing: 'swing',
				duration: 600,
				complete: function () {
				},
				step: function () {
				}
			}
		}, options);
		
		const startX = element.scrollLeft,
			startY = element.scrollTop,
			startTime = performance.now();
		
		const targetX = !isNaN(Number(x)) ? x : document.querySelector(x).offsetLeft + options.gap.x;
		const targetY = !isNaN(Number(y)) ? y : document.querySelector(y).offsetTop + options.gap.y;
		
		function easeOutQuad(t) {
			return t * (2 - t);
		}
		
		function animateScroll() {
			var now = performance.now();
			var time = Math.min(1, ((now - startTime) / options.animation.duration));
			var timeFunction = easeOutQuad(time);
			
			element.scrollLeft = (timeFunction * (targetX - startX)) + startX;
			element.scrollTop = (timeFunction * (targetY - startY)) + startY;
			
			options.animation.step(time);
			
			if (time < 1) {
				requestAnimationFrame(animateScroll);
			} else {
				options.animation.complete();
			}
		}
		
		requestAnimationFrame(animateScroll);
	}
	
	// Expose as a global function
	window.scrollToElement = scrollTo;
	
	// Optionally, add to HTMLElement prototype
	HTMLElement.prototype.scrollToElement = function (x, y, options) {
		scrollTo(this, x, y, options);
	};
})();
