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

/*
 * Usage
 * -----
 * // Call the function with the desired selector and options
 * hideMaxListItems('ul.your-list-class', {
 *      max: 3,
 *      speed: 1000,
 *      moreText: 'READ MORE',
 *      lessText: 'READ LESS'
 * });
 */

(function () {
	function hideMaxListItems(element, options) {
		// OPTIONS
		const defaults = {
			max: 3,
			speed: 1000,
			moreText: 'READ MORE',
			lessText: 'READ LESS',
			moreHTML: '<p class="maxlist-more"><a href="#"></a></p>'
		};
		const settings = Object.assign({}, defaults, options);
		
		// FOR EACH MATCHED ELEMENT
		const list = element;
		const totalListItems = list.children.length;
		let speedPerLI;
		
		// Get animation speed per LI; Divide the total speed by num of LIs.
		// Avoid dividing by 0 and make it at least 1 for small numbers.
		if (totalListItems > 0 && settings.speed > 0) {
			speedPerLI = Math.round(settings.speed / totalListItems);
			if (speedPerLI < 1) {
				speedPerLI = 1;
			}
		} else {
			speedPerLI = 0;
		}
		
		// If list has more than the "max" option
		if (totalListItems > 0 && totalListItems > settings.max) {
			// Initial Page Load: Hide each LI element over the max
			Array.from(list.children).forEach(function (item, index) {
				if ((index + 1) > settings.max) {
					item.style.display = 'none';
				} else {
					item.style.display = 'block';
				}
			});
			
			// Replace [COUNT] in "moreText" or "lessText" with number of items beyond max
			const howManyMore = totalListItems - settings.max;
			let newMoreText = settings.moreText;
			let newLessText = settings.lessText;
			
			if (howManyMore > 0) {
				newMoreText = newMoreText.replace("[COUNT]", howManyMore.toString());
				newLessText = newLessText.replace("[COUNT]", howManyMore.toString());
			}
			
			// Add "Read More" button, or unhide it if it already exists
			const nextElement = list.nextElementSibling;
			let moreButton;
			if (nextElement && nextElement.classList.contains("maxlist-more")) {
				moreButton = nextElement;
				moreButton.style.display = 'block';
			} else {
				moreButton = document.createElement('div');
				moreButton.innerHTML = settings.moreHTML;
				list.parentNode.insertBefore(moreButton.firstChild, list.nextSibling);
				moreButton = list.nextElementSibling;
			}
			
			// READ MORE - add text within button, register click event that slides the items up and down
			const moreLink = moreButton.querySelector("a");
			moreLink.innerHTML = newMoreText;
			moreLink.addEventListener("click", function (e) {
				e.preventDefault();
				const listElements = Array.from(list.children).slice(settings.max);
				
				function toggleElements(items, i, show, callback) {
					if (i < items.length && i >= 0) {
						items[i].style.display = show ? 'block' : 'none';
						setTimeout(function () {
							toggleElements(items, show ? i + 1 : i - 1, show, callback);
						}, speedPerLI);
					} else {
						callback();
					}
				}
				
				if (moreLink.innerHTML === newMoreText) {
					moreLink.innerHTML = newLessText;
					toggleElements(listElements, 0, true, function () {
					});
				} else {
					moreLink.innerHTML = newMoreText;
					toggleElements(listElements, listElements.length - 1, false, function () {
					});
				}
			});
		} else {
			// LIST HAS LESS THAN THE MAX
			// Hide "Read More" button if it's there
			const nextElement = list.nextElementSibling;
			if (nextElement && nextElement.classList.contains("maxlist-more")) {
				nextElement.style.display = 'none';
			}
			// Show all list items that may have been hidden
			Array.from(list.children).forEach(function (item) {
				item.style.display = 'block';
			});
		}
	}
	
	// Adding the function to the global scope to use it similarly to jQuery plugin
	window.hideMaxListItems = function (selector, options) {
		document.querySelectorAll(selector).forEach((element) => hideMaxListItems(element, options));
	};
})();
