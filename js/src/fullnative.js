// Check if event listener once is supported
let onceSupported = false;

try {
	const options = {
		get once() {
			// This function will be called when the browser
			//   attempts to access the once property.
			onceSupported = true;
			return false;
		},
	};

	window.addEventListener('test', null, options);
	window.removeEventListener('test', null, options);
} catch (err) {
	onceSupported = false;
}

function loadImages() {
	const nativeLoadingImages = document.querySelectorAll('img.lazyloadnative');

	nativeLoadingImages.forEach(processImage);

	if (!('MutationObserver' in window)) {
		return;
	}

	// Prepare MutationObserver
	/**
	 * @type {MutationObserverInit}
	 */
	const config = { childList: true, subtree: true };

	/**
	 * Callback to execute when mutations are observed
	 * @type {MutationCallback}
	 */
	const callback = function (mutationsList) {
		for (let mutation of mutationsList) {
			mutation.addedNodes.forEach(processImage);
		}
	};

	const observer = new MutationObserver(callback);
	observer.observe(document.body, config);
}

/**
 * Process an image element
 * @param {HTMLImageElement} image The image element being processed
 */
function processImage(image) {
	if (
		image.nodeName !== 'IMG' ||
		!image.classList.contains('lazyloadnative')
	) {
		return;
	}

	if (!('loading' in HTMLImageElement.prototype)) {
		image.classList.remove('lazyloadnative');
		image.classList.add('lazyload');
		return;
	}

	if (image.complete) {
		showImage(image);
	} else {
		image.addEventListener(
			'load',
			()=>showImage(image),
			onceSupported ? { once: true } : false
		);
	}
}

function showImage(image) {
	image.classList.remove('lazyloadnative');
	image.classList.add('lazyloaded');
}

document.addEventListener('DOMContentLoaded', loadImages);
