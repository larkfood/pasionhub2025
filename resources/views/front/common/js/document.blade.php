<script>
	/**
	 * Execute callback function after page is loaded
	 *
	 * @param callback
	 * @param isFullyLoaded
	 */
	if (!window.onDocumentReady) {
		function onDocumentReady(callback, isFullyLoaded = true) {
			switch (document.readyState) {
				case "loading":
					/* The document is still loading, attach the event listener */
					document.addEventListener("DOMContentLoaded", callback);
					break;
				case "interactive": {
					if (!isFullyLoaded) {
						/*
						 * The document has finished loading, and we can access DOM elements.
						 * Sub-resources such as scripts, images, stylesheets and frames are still loading.
						 * Call the callback (on next available tick (in 500 milliseconds))
						 */
						setTimeout(callback, 500);
					}
					break;
				}
				case "complete":
					/* The page is fully loaded, call the callback directly */
					callback();
					break;
				default:
					document.addEventListener("DOMContentLoaded", callback);
			}
		}
	}
</script>
