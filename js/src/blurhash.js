// @ts-check
import runAction from './blurhash/runAction';
import getBlurhash from './blurhash/getBlurhash';
import getWorker, { setWorkerUrl } from './blurhash/getWorker';

let useWorker =
	'Worker' in window &&
	'OffscreenCanvas' in window &&
	'convertToBlob' in OffscreenCanvas.prototype;

function workerErrorListener(error) {
	console.log(error);
	useWorker = false;
}

function installWorker() {
	if (useWorker) {
		const script = document.currentScript;

		if (
			script === null ||
			script.getAttribute('src').indexOf('lazysizes') === -1
		) {
			useWorker = false;
			return;
		}

		const scriptSrcSplit = script.getAttribute('src').split('/');
		scriptSrcSplit.pop();
		const workerUrl = scriptSrcSplit.join('/') + '/blurhash-worker.min.js';
		setWorkerUrl(workerUrl);

		const worker = new Worker(workerUrl);
		worker.addEventListener('error', workerErrorListener, false);
		worker.terminate();
	}
}

installWorker();

function blurhashLoad() {
	if (
		!(
			'toBlob' in HTMLCanvasElement.prototype ||
			'msToBlob' in HTMLCanvasElement.prototype
		)
	) {
		// No support for canvas.toBlob
		return;
	}

	const blurhashImages = document.querySelectorAll('img[data-blurhash]');

	blurhashImages.forEach(processImage);

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
		!image.dataset.blurhash ||
		image.classList.contains('blurhashed')
	) {
		return;
	}

	runAction(function imageAction() {
		let width = parseInt(image.getAttribute('width'), 10) || 1;
		let height = parseInt(image.getAttribute('height'), 10) || 1;

		if (width <= 1 || height <= 1) {
			if (image.dataset.aspectratio) {
				const aspectratio = image.dataset.aspectratio.split('/');
				width = parseInt(aspectratio[0], 10);
				height = parseInt(aspectratio[1], 10);

				if (width <= 25 || height <= 25) {
					// Probably an actual aspect ratio, we can't handle that yet.
					return;
				}
			} else {
				return;
			}
		}

		const {
			position: parentPosition,
			display: parentDisplay,
		} = getComputedStyle(image.parentElement);
		const imageStyles = getComputedStyle(image);
		const { position: imagePosition } = imageStyles;

		let useFancySetup = true;
		if (
			document.body.classList.contains('blurhash-no-fancy') ||
			parentPosition === 'fixed' ||
			parentPosition === 'sticky' ||
			imagePosition === 'fixed' ||
			imagePosition === 'sticky' ||
			// Check if length of parent is more than 1
			Array.prototype.slice
				.call(image.parentNode.children)
				.filter((val) => val.nodeName !== 'NOSCRIPT').length > 1
		) {
			useFancySetup = false;
		}

		/**
		 * @type {HTMLImageElement}
		 */
		let newImage;

		if (useFancySetup) {
			let containerNode = image.parentElement;

			// If image is wrapped in link, use link's parent
			if (containerNode.nodeName === 'A' && parentDisplay === 'inline') {
				containerNode = containerNode.parentElement;
			}
			containerNode.classList.add('blurhash-container');

			// Make sure parent is either relative or absolute
			if (parentPosition !== 'absolute') {
				containerNode.classList.add('blurhash-container-relative');
			}

			// Make sure image is either relative or absolute
			if (imagePosition !== 'absolute') {
				image.classList.add('blurhash-relative');
			}

			// @ts-ignore
			newImage = image.cloneNode();

			newImage.src =
				'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

			newImage.classList.add('blurhashing');
			newImage.classList.remove('blurhash-relative');
			newImage.classList.remove('lazyload');
			newImage.classList.remove('lazyloadnative');
			newImage.classList.remove('lazyloading');

			// Cleanup attributes
			newImage.removeAttribute('srcset');
			newImage.removeAttribute('data-srcset');
			newImage.removeAttribute('data-src');
			newImage.removeAttribute('itemprop');
			newImage.removeAttribute('id');
			newImage.alt = '';

			newImage.removeAttribute('data-aspectratio');
			newImage.removeAttribute('data-blurhash');

			if ('loading' in newImage) {
				// @ts-ignore
				newImage.loading = 'eager';
			}

			const { direction, top } = imageStyles;
			const alignSide = direction === 'ltr' ? 'left' : 'right';

			if (
				imageStyles[alignSide] === '0px' ||
				imageStyles[alignSide] === 'auto'
			) {
				newImage.classList.add(alignSide);
			} else {
				newImage.style[alignSide] = imageStyles[alignSide];
			}

			if (top !== '0px' && top !== 'auto') {
				newImage.style.top = top;
			}

			image.after(newImage);
		} else {
			if (image.classList.contains('lazyloadnative')) {
				image.classList.remove('lazyloadnative');
				image.classList.add('lazyload');
			}
			image.classList.add('blurhash');
			image.classList.add('blurhashing');
		}

		const callback = (blob) => {
			const url = URL.createObjectURL(blob);
			if (useFancySetup) {
				newImage.src = url;

				// To trigger fade transition
				newImage.classList.remove('blurhashing');
				newImage.classList.add('blurhashed');

				// Remove element used for fancy blurhash and revoke url when image is loaded
				function lazyloadedCallback() {
					image.removeEventListener('lazyloaded', lazyloadedCallback);
					// Timeout is used to ensure animation is complete
					setTimeout(() => {
						URL.revokeObjectURL(url);
						newImage.parentNode.removeChild(newImage);
					}, 2000);
				}
				image.addEventListener('lazyloaded', lazyloadedCallback);
			} else {
				image.src = url;
				image.classList.remove('blurhashing');
				image.classList.add('blurhashed');

				// Revoke url when image is loaded
				function lazyloadedCallback() {
					image.removeEventListener('lazyloaded', lazyloadedCallback);
					// Timeout is used to ensure animation is complete
					setTimeout(() => {
						URL.revokeObjectURL(url);
					}, 2000);
				}
				image.addEventListener('lazyloaded', lazyloadedCallback);
			}
			runAction();
		};

		if (useWorker === true) {
			const worker = getWorker(workerErrorListener);
			worker.worker.postMessage([image.dataset.blurhash, width, height]);
			worker.worker.onmessage = ({ data: blob }) => {
				callback(blob);
				worker.used = false;
			};
		} else {
			getBlurhash(image.dataset.blurhash, width, height, callback);
		}
	});
}

document.addEventListener('DOMContentLoaded', blurhashLoad);
