import decode from 'blurhash/dist/decode';

function blurhashLoad() {
	const blurhashImages = document.querySelectorAll('img[data-blurhash]');

	blurhashImages.forEach(image => {
		const width = image.width;
		const height = image.height;

		if(width <= 1 || height <= 1) {
			return;
		}

		const pixels = decode(image.dataset.blurhash, width, height);
		console.log(image.dataset.blurhash, width, height);
		image.classList.remove('lazyload');

		const canvas = document.createElement('canvas');
		const ctx = canvas.getContext('2d');
		const imageData = ctx.createImageData(width, height);
		imageData.data.set(pixels);
		ctx.putImageData(imageData, 0, 0);

		canvas.toBlob(blob => {
	  	const url = URL.createObjectURL(blob);
		  image.src = url;
		});
	})
}

document.addEventListener('DOMContentLoaded', blurhashLoad);
addEventListener('load', blurhashLoad);
