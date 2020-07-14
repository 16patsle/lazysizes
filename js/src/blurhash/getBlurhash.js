import decode from '../lib/decode';
import getCanvas from './getCanvas';

export default function getBlurhash(blurhash, width, height, callback) {
	const pixels = decode(blurhash, width, height);

	const canvas = getCanvas(width, height);
	canvas.imageData.data.set(pixels);
	canvas.ctx.putImageData(canvas.imageData, 0, 0);

	canvas.element.toBlob((blob) => {
		canvas.used = false;
		callback(blob);
	});
}
