<script>
	const honeypotElSelector = "input[type=checkbox][name=honeypot_enabled]";
	const validFromTimestampElSelector = "input[type=checkbox][name=honeypot_valid_from_timestamp]";
	const captchaElSelector = "select[name=captcha].select2_from_array";
	const recaptchaVersionElSelector = "select[name=recaptcha_version].select2_from_array";
	
	onDocumentReady((event) => {
		/* Honeypot */
		let honeypotEl = document.querySelector(honeypotElSelector);
		if (honeypotEl) {
			enableHoneypot(honeypotEl);
			honeypotEl.addEventListener("change", e => enableHoneypot(e.target));
		}
		
		let validFromTimestampEl = document.querySelector(validFromTimestampElSelector);
		if (validFromTimestampEl) {
			enableValidFromTimestamp(validFromTimestampEl);
			validFromTimestampEl.addEventListener("change", e => enableValidFromTimestamp(e.target));
		}
		
		/* Captcha */
		let captchaEl = document.querySelector(captchaElSelector);
		if (captchaEl) {
			getCaptchaFields(captchaEl);
			$(captchaEl).on("change", e => getCaptchaFields(e.target));
		}
		
		/* Captcha version */
		let recaptchaVersionEl = document.querySelector(recaptchaVersionElSelector);
		if (recaptchaVersionEl) {
			getReCaptchaFields(recaptchaVersionEl);
			$(recaptchaVersionEl).on("change", e => getReCaptchaFields(e.target));
		}
	});
	
	function enableHoneypot(honeypotEl) {
		if (!honeypotEl) return;
		
		if (honeypotEl.checked) {
			setElementsVisibility("show", ".honeypot-el");
			
			let validFromTimestampEl = document.querySelector(validFromTimestampElSelector);
			enableValidFromTimestamp(validFromTimestampEl);
		} else {
			setElementsVisibility("hide", ".honeypot-el");
		}
	}
	
	function enableValidFromTimestamp(validFromTimestampEl) {
		if (!validFromTimestampEl) return;
		
		let honeypotEl = document.querySelector(honeypotElSelector);
		if (!honeypotEl) return;
		
		let action = (honeypotEl.checked && validFromTimestampEl.checked) ? "show" : "hide";
		setElementsVisibility(action, ".honeypot-timestamp-el");
	}
	
	function getCaptchaFields(captchaEl) {
		if (!captchaEl) return;
		
		let captchaElValue = captchaEl.value;
		
		if (captchaElValue === "") {
			setElementsVisibility("hide", [".s-captcha", ".recaptcha"]);
		}
		if (
			captchaElValue === "default"
			|| captchaElValue === "math"
			|| captchaElValue === "flat"
			|| captchaElValue === "mini"
			|| captchaElValue === "inverse"
		) {
			setElementsVisibility("hide", [".recaptcha", ".s-captcha-custom"]);
			setElementsVisibility("show", ".s-captcha:not(.s-captcha-custom)");
		}
		if (captchaElValue === "custom") {
			setElementsVisibility("hide", ".recaptcha");
			setElementsVisibility("show", ".s-captcha");
		}
		if (captchaElValue === "recaptcha") {
			setElementsVisibility("hide", ".s-captcha");
			setElementsVisibility("show", ".recaptcha");
			
			let recaptchaVersionEl = document.querySelector(recaptchaVersionElSelector);
			getReCaptchaFields(recaptchaVersionEl);
		}
	}
	
	function getReCaptchaFields(recaptchaVersionEl) {
		if (!recaptchaVersionEl) return;
		
		let recaptchaVersionElValue = recaptchaVersionEl.value;
		
		let captchaEl = document.querySelector(captchaElSelector);
		let captchaElValue = captchaEl.value;
		
		if (captchaElValue === "recaptcha") {
			setElementsVisibility("hide", ".s-captcha");
			setElementsVisibility("show", ".recaptcha");
			
			if (recaptchaVersionElValue === "v3") {
				setElementsVisibility("hide", ".recaptcha-v2");
				setElementsVisibility("show", ".recaptcha-v3");
			} else {
				setElementsVisibility("hide", ".recaptcha-v3");
				setElementsVisibility("show", ".recaptcha-v2");
			}
		} else {
			setElementsVisibility("hide", ".recaptcha");
		}
	}
</script>
