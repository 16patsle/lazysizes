const canvases = [];

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
