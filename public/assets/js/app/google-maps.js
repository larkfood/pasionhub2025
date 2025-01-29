/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

if (typeof geocodingApiKey === 'undefined') {
	var geocodingApiKey = "";
}
if (typeof locationAddress === 'undefined') {
	var locationAddress = "New York City, United States";
}
if (typeof locationMapElId === 'undefined') {
	var locationMapElId = "googleMaps";
}
if (typeof locationMapId === 'undefined') {
	var locationMapId = "YOUR_MAP_ID_HERE";
}

// Initialize and add the map
let mapObj;

async function initGoogleMap() {
	const locationMapEl = document.getElementById(locationMapElId);
	if (!locationMapEl) return false;
	
	const result = await getCityCoordinatesWithGoogle(geocodingApiKey, locationAddress);
	
	if (!result.error) {
		const { Map } = await google.maps.importLibrary("maps");
		const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
		
		const {lat, lng} = result;
		const coordinates = {lat, lng};
		
		// Center the map at the location (city) coordinates
		mapObj = new Map(locationMapEl, {
			center: coordinates,
			zoom: 9,
			zoomControl: true,
			mapTypeControl: false,
			scaleControl: false,
			streetViewControl: false,
			rotateControl: false,
			fullscreenControl: true,
			mapId: locationMapId,
		});
		
		// Add a marker positioned at the location (city)
		const marker = new AdvancedMarkerElement({
			map: mapObj,
			position: coordinates,
			title: locationAddress,
		});
	} else {
		console.error("Could not load the map due to missing coordinates.");
		showErrorMapMessage(locationMapEl, result.error);
	}
}

async function getCityCoordinatesWithGoogle(geocodingApiKey, locationAddress) {
	locationAddress = encodeURIComponent(locationAddress);
	const geocodingUrl = `https://maps.googleapis.com/maps/api/geocode/json?key=${geocodingApiKey}&address=${locationAddress}`;
	
	let result = {error: null};
	
	try {
		const response = await fetch(geocodingUrl);
		const data = await response.json();
		
		if (data.status === "OK" && data.results.length > 0) {
			const coordinates = data.results[0].geometry.location; // { lat: ..., lng: ... }
			result = {...result, ...coordinates};
		} else {
			result.error = data.error_message ?? data.status;
			console.error("Geocoding failed:", result.error);
		}
	} catch (error) {
		result.error = error;
		console.error("Error fetching geocoding data:", result.error);
	}
	
	return result;
}

/**
 * Show the Map error message
 * @param locationMapEl
 * @param message
 */
function showErrorMapMessage(locationMapEl, message)
{
	if (!locationMapEl) return;
	
	locationMapEl.textContent = message;
	locationMapEl.style.textAlign = 'center'; // Center text horizontally
	locationMapEl.style.lineHeight = '1.5'; // Prevent character overlap by setting proper line height
	locationMapEl.style.display = 'flex';
	locationMapEl.style.justifyContent = 'center';
	locationMapEl.style.alignItems = 'center';
	locationMapEl.style.padding = '10px'; // Optional: Add padding for better appearance
	locationMapEl.style.wordWrap = 'break-word'; // Ensures long words break to fit the width
	locationMapEl.style.overflowX = 'hidden'; // Ensures no overflow if text is too long
	locationMapEl.style.overflowY = 'auto'; // Enable vertical scrolling if content overflows
	locationMapEl.style.flexDirection = 'column'; // Aligns the text as a single column
}
