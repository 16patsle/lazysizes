// @ts-check
import decode from '../lib/decode';
import getCanvas from './getCanvas';

/**
 * Decode Blurhash string and return as blob.
 * @param {string} blurhash An encoded Blurhash string
 * @param {number} width The width of the image
 * @param {number} height The height of the image
 * @param {getBlurhashCallback} callback Function to call with the decoded image blob when finished
 */
export default function getBlurhash(blurhash, width, height, callback) {
	const pixels = decode(blurhash, width, height);

	const canvas = getCanvas(width, height);
	canvas.imageData.data.set(pixels);
	canvas.ctx.putImageData(canvas.imageData, 0, 0);

	if (
		typeof HTMLCanvasElement !== 'undefined' &&
		canvas.element instanceof HTMLCanvasElement
	) {
		// @ts-ignore
		if (canvas.element.msToBlob) {
			// @ts-ignore
			callback(canvas.element.msToBlob());
		} else {
			canvas.element.toBlob((blob) => {
				canvas.used = false;
				callback(blob);
			});
		}
	} else if (
		typeof OffscreenCanvas !== 'undefined' &&
		canvas.element instanceof OffscreenCanvas
	) {
		canvas.element.convertToBlob().then((blob) => {
			canvas.used = false;
			callback(blob);
		});
	}
}

/**
 * Callback for when Blurhash image creation is complete
 * @callback getBlurhashCallback
 * @param {Blob} blob The decoded image blob
 */
