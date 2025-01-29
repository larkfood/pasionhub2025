<script>
	onDocumentReady((event) => {
		let cacheDriverEl = document.querySelector("select[name=cache_driver].select2_from_array");
		if (cacheDriverEl) {
			getCacheDriverFields(cacheDriverEl);
			$(cacheDriverEl).on("change", e => getCacheDriverFields(e.target));
		}
		
		let queueDriverEl = document.querySelector("select[name=queue_driver].select2_from_array");
		if (queueDriverEl) {
			getQueueDriverFields(queueDriverEl);
			$(queueDriverEl).on("change", e => getQueueDriverFields(e.target));
		}
	});
	
	function getCacheDriverFields(driverEl) {
		setElementsVisibility("show", ".cache-enabled");
		if (driverEl.value === "array") {
			setElementsVisibility("hide", ".cache-enabled");
		}
		
		setElementsVisibility("hide", ".memcached");
		if (driverEl.value === "memcached") {
			setElementsVisibility("show", ".memcached");
		}
	}
	
	function getQueueDriverFields(driverEl) {
		setElementsVisibility("hide", ".sqs");
		if (driverEl.value === "sqs") {
			setElementsVisibility("show", ".sqs");
		}
	}
</script>
