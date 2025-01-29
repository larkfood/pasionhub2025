<script>
	/**
	 * Show Success Message
	 * @param {string} message
	 */
	function showSuccessMessage(message) {
		let errorEl = document.getElementById('uploadError');
		let successEl = document.getElementById('uploadSuccess');
		
		if (errorEl) {
			errorEl.style.display = 'none';
			errorEl.innerHTML = '';
			errorEl.classList.remove('alert', 'alert-block', 'alert-danger');
		}
		
		if (successEl) {
			successEl.innerHTML = '<ul></ul>';
			successEl.style.display = 'none';
			successEl.querySelector('ul').innerHTML = message;
			fadeIn(successEl, 'fast'); /* 'fast' corresponds to 200ms, 'slow' to 600ms */
		}
	}
	
	/**
	 * Show Errors Message
	 * @param {string} message
	 */
	function showErrorMessage(message) {
		jsAlert(message, 'error', false);
		
		let errorEl = document.getElementById('uploadError');
		let successEl = document.getElementById('uploadSuccess');
		
		if (successEl) {
			successEl.innerHTML = '';
			successEl.style.display = 'none';
		}
		
		if (errorEl) {
			errorEl.innerHTML = '<ul></ul>';
			errorEl.style.display = 'none';
			errorEl.classList.add('alert', 'alert-block', 'alert-danger');
			errorEl.querySelector('ul').innerHTML = message;
			fadeIn(errorEl, 'fast'); /* 'fast' corresponds to 200ms, 'slow' to 600ms */
		}
	}
	
	/**
	 * Fade in an element
	 * @param {HTMLElement} element
	 * @param {string} speed
	 */
	function fadeIn(element, speed) {
		let duration = speed === 'fast' ? 200 : (speed === 'slow' ? 600 : 200);
		element.style.opacity = '0';
		element.style.display = 'block';
		
		let last = +new Date();
		let tick = function() {
			let newOpacity = +element.style.opacity + (new Date() - last) / duration;
			element.style.opacity = newOpacity.toString();
			last = +new Date();
			
			if (newOpacity < 1) {
				(window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16);
			}
		};
		
		tick();
	}
</script>
