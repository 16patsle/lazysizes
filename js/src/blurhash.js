import decode from 'blurhash/dist/decode';

function blurhashLoad() {
	const blurhashImages = document.querySelectorAll('img[data-blurhash]');

	blurhashImages.forEach(processImage)

	if (!'MutationObserver' in window) {
		return;
	}

	// Prepare MutationObserver
	const config = { childList: true, subtree: true };

	// Callback to execute when mutations are observed
	const callback = function(mutationsList) {
    	for(let mutation of mutationsList) {
    	    mutation.addedNodes.forEach(processImage);
    	}
	};

	const observer = new MutationObserver(callback);
	observer.observe(document.body, config);
}

function processImage(image) {
	if(image.nodeName !== 'IMG' || !image.dataset.blurhash || image.classList.contains('blurhashed')) {
		return;
	}

	let width = image.width;
	let height = image.height;

	if(width <= 1 || height <= 1) {
		if(image.dataset.aspectratio) {
			const aspectratio = image.dataset.aspectratio.split('/');
			width = parseInt(aspectratio[0], 10);
			height = parseInt(aspectratio[1], 10);

			if(width <= 25 || height <= 25) {
				// Probably an actual aspect ratio, we can't handel that yet.
				return;
			}
		} else {
			return;
		}
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

	canvas.toBlob(blob => {
	const url = URL.createObjectURL(blob);
	  image.src = url;
	  image.classList.add('blurhashed');
	});
}

document.addEventListener('DOMContentLoaded', blurhashLoad);
