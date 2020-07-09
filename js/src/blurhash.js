import decode from './lib/decode';

function blurhashLoad() {
	const blurhashImages = document.querySelectorAll('img[data-blurhash]');

	blurhashImages.forEach(processImage);

	if (!'MutationObserver' in window) {
		return;
	}

	// Prepare MutationObserver
	const config = { childList: true, subtree: true };

	// Callback to execute when mutations are observed
	const callback = function (mutationsList) {
		for (let mutation of mutationsList) {
			mutation.addedNodes.forEach(processImage);
		}
	};

	const observer = new MutationObserver(callback);
	observer.observe(document.body, config);
}

function processImage(image) {
	if (
		image.nodeName !== 'IMG' ||
		!image.dataset.blurhash ||
		image.classList.contains('blurhashed')
	) {
		return;
	}

	let width = parseInt(image.getAttribute('width'), 10) || 1;
	let height = parseInt(image.getAttribute('height'), 10) || 1;

	if (width <= 1 || height <= 1) {
		if (image.dataset.aspectratio) {
			const aspectratio = image.dataset.aspectratio.split('/');
			width = parseInt(aspectratio[0], 10);
			height = parseInt(aspectratio[1], 10);

			if (width <= 25 || height <= 25) {
				// Probably an actual aspect ratio, we can't handel that yet.
				return;
			}
		} else {
			return;
		}
	}

	const useFancySetup = image.parentNode.classList.contains('wp-block-image');
	let newImage;

	if (useFancySetup) {
		image.parentNode.style.position = 'relative';
		image.parentNode.classList.add('blurhash-container');

		newImage = image.cloneNode();

		newImage.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

		newImage.classList.add('blurhashing');
		newImage.classList.remove('lazyload');
		newImage.classList.remove('lazyloading');
		newImage.removeAttribute('srcset');

		if('loading' in newImage) {
			newImage.loading = 'eager';
		}

		const { direction } = getComputedStyle(image);
		const alignSide = direction === 'ltr' ? 'left' : 'right';

		newImage.style[alignSide] = 0;
		image.after(newImage);
	} else {
		image.classList.add('blurhash');
		image.classList.add('blurhashing');
	}

	const pixels = decode(image.dataset.blurhash, width, height);
	console.log(image.dataset.blurhash, width, height);

	const canvas = document.createElement('canvas');
	canvas.width = width;
	canvas.height = height;
	const ctx = canvas.getContext('2d');
	const imageData = ctx.createImageData(width, height);
	imageData.data.set(pixels);
	ctx.putImageData(imageData, 0, 0);

	canvas.toBlob((blob) => {
		const url = URL.createObjectURL(blob);
		if (useFancySetup) {
			newImage.src = url;

			// To trigger fade transition
			newImage.classList.add('blurhashed');
		} else {
			image.src = url;
			image.classList.remove('blurhashing');
			image.classList.add('blurhashed');
		}
	});
}

document.addEventListener('DOMContentLoaded', blurhashLoad);
