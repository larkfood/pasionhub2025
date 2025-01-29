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
 * BsRowColumnsReorder
 * Reorder the Bootstrap "row columns" grid items vertically
 * (based on the user's screen)
 */

class BsRowColumnsReorder {
	constructor(selector, options = {}) {
		this.selector = selector;
		this.options = Object.assign(
			{
				defaultColumns: 4, // Default number of columns if none are detected
				debounceTime: 50, // Debounce time for resize events
			},
			options
		);
		
		this.gridContainers = [];
		this.originalItemsOrderCache = [];
		this.debounceTimer = null;
		
		this.init();
	}
	
	/**
	 * Initialize the component
	 */
	init() {
		// Process initial containers
		this.updateContainers();
		
		// Bind event listeners
		window.addEventListener('load', () => this.reorderAllGrids());
		window.addEventListener('resize', () => this.debounce(this.reorderAllGrids.bind(this)));
	}
	
	/**
	 * Update the list of containers and cache original order
	 */
	updateContainers() {
		const newContainers = Array.from(document.querySelectorAll(this.selector)).filter(
			container => !this.gridContainers.includes(container)
		);
		
		// Add new containers and cache their order
		newContainers.forEach(container => this.processContainer(container));
		this.gridContainers.push(...newContainers);
	}
	
	/**
	 * Process a single container to cache its original order
	 */
	processContainer(container) {
		// Skip containers without `row-cols-*` class
		if (!this.hasRowColsClass(container)) return;
		
		const items = Array.from(container.children);
		
		// Cache original items order
		this.originalItemsOrderCache.push({ container, items });
	}
	
	/**
	 * Reorder all grids
	 */
	reorderAllGrids() {
		this.gridContainers.forEach(container => this.reorderGrid(container));
	}
	
	/**
	 * Reorder a single grid container
	 */
	reorderGrid(gridContainer) {
		// Skip containers without `row-cols-*` class
		if (!this.hasRowColsClass(gridContainer)) return;
		
		// Find the original order
		const originalOrder = this.originalItemsOrderCache.find(cache => cache.container === gridContainer);
		
		if (!originalOrder) return; // Skip if no original order is found
		
		const columns = this.parseColumnNumber(gridContainer, this.options.defaultColumns);
		
		// Reorder items
		const reorderedItems = this.reorderArrayToColumns(originalOrder.items, columns);
		
		// Clear the container and append reordered items
		gridContainer.innerHTML = '';
		reorderedItems.forEach(item => gridContainer.appendChild(item));
	}
	
	/**
	 * Add a new container dynamically
	 */
	addContainer(container) {
		// Process and cache the new container
		this.processContainer(container);
		
		// Reorder the grid immediately
		this.reorderGrid(container);
	}
	
	/**
	 * Add new containers dynamically
	 */
	addNewContainers(parentElement) {
		const newContainers = Array.from(parentElement.querySelectorAll(this.selector));
		newContainers.forEach(container => {
			this.processContainer(container);
			this.reorderGrid(container);
		});
		
		// Update the list of containers for future resize events
		this.updateContainers();
	}
	
	/**
	 * Parse the number of columns dynamically based on container classes
	 */
	parseColumnNumber(gridContainer, defaultColumns = 4) {
		const className = gridContainer.className;
		
		// Define Bootstrap breakpoints
		const gridBreakpoints = {xs: 0, sm: 576, md: 768, lg: 992, xl: 1200, xxl: 1400};
		const regexWithSize = /row-cols-([a-z]+)-(\d+)/g; // Matches row-cols-{size}-{colNumber}
		const regexWithoutSize = /row-cols-(\d+)/g; // Matches row-cols-{colNumber}
		
		const screenWidth = window.innerWidth;
		
		let columns = defaultColumns;
		let maxMatchedBreakpoint = 0;
		let hasSizeSpecificColumns = false;
		
		// Handle size-specific classes first
		let match;
		while ((match = regexWithSize.exec(className)) !== null) {
			hasSizeSpecificColumns = true;
			
			const colClassInfix = match[1]; // E.g., sm, md, lg
			const colSize = parseInt(match[2], 10);
			const breakpointDimension = gridBreakpoints[colClassInfix] || 0;
			
			if (screenWidth >= breakpointDimension && breakpointDimension >= maxMatchedBreakpoint) {
				maxMatchedBreakpoint = breakpointDimension;
				columns = colSize;
			}
		}
		
		// If no size-specific classes matched or exist, fall back to `row-cols-{colNumber}`
		if (!hasSizeSpecificColumns) {
			match = regexWithoutSize.exec(className);
			if (match) {
				columns = parseInt(match[1], 10);
			}
		}
		
		return columns;
	}
	
	/**
	 * Reorder items into rows and columns vertically
	 */
	reorderArrayToColumns(sortedArray, columns) {
		const rows = Math.ceil(sortedArray.length / columns); // Calculate the number of rows
		const grid = [];
		
		// Split the array into chunks (columns)
		for (let i = 0; i < sortedArray.length; i += rows) {
			grid.push(sortedArray.slice(i, i + rows));
		}
		
		// Adjust to display the array items per column
		const reordered = [];
		for (let i = 0; i < rows; i++) {
			for (let col of grid) {
				if (col[i] !== undefined) {
					reordered.push(col[i]);
				}
			}
		}
		
		return reordered;
	}
	
	/**
	 * Check if a container has `row-cols-*` class
	 */
	hasRowColsClass(container) {
		const regex = /row-cols-([a-z]+-\d+|\d+)/; // Matches both `row-cols-{size}-{colNumber}` and `row-cols-{colNumber}`
		return regex.test(container.className);
	}
	
	/**
	 * Debounce function to limit frequent calls
	 */
	debounce(func) {
		clearTimeout(this.debounceTimer);
		this.debounceTimer = setTimeout(func, this.options.debounceTime);
	}
}
