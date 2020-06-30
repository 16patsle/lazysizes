import { decode } from 'blurhash/dist/decode';

const blurhashImages = document.querySelectorAll('img[data-blurhash]');

blurhashImages.forEach(image => {
	const pixels = decode(image.dataset.blurhash);
	image.classList.remove('lazyload');

	const canvas = document.createElement('canvas');
	const ctx = canvas.getContext('2d');
	const imageData = ctx.createImageData(561, 411);
	imageData.data.set(pixels);
	ctx.putImageData(imageData, 0, 0);

	canvas.toBlob(blob => {
  	const url = URL.createObjectURL(blob);
	  image.src = url;
	});
})
