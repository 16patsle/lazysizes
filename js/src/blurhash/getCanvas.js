/**
 * @type {CanvasDataObject[]}
 */
const canvases = [];

/**
 * Object containing a canvas and related objects and data
 * @typedef {Object} CanvasDataObject
 * @property {HTMLCanvasElement} element The canvas element
 * @property {CanvasRenderingContext2D} ctx The canvas rendering context
 * @property {ImageData} imageData The ImageData object used to render images
 * @property {boolean} used Whether this canvas is currently used
 */

/**
 * Get a canvas from the pool, or create a new one if all are in use.
 * @param {number} width The requested canvas width
 * @param {number} height The requested canvas height
 * @returns {CanvasDataObject} An object with a usable canvas
 */
export default function getCanvas(width, height) {
	const unusedCanvases = canvases.filter(
		(canvas) => canvas && canvas.used === false
	);
	let canvas = unusedCanvases[0];
	if (canvas) {
		canvas.ctx.clearRect(0, 0, canvas.element.width, canvas.element.height);
	} else {
		canvas = {
			element: document.createElement('canvas'),
		};
		canvas.ctx = canvas.element.getContext('2d');
		canvases.push(canvas);
	}
	canvas.element.width = width;
	canvas.element.height = height;
	canvas.imageData = canvas.ctx.createImageData(width, height);
	canvas.used = true;
	return canvas;
}
