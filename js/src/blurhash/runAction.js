const processingQueue = [];
let currentlyProcessing = 0;
const maxConcurrent = 2;

// Queue of actions to perform, with a limit on how many at the same time.
export default function runAction(actionCallback) {
	if (typeof actionCallback === 'function') {
		processingQueue.push(actionCallback);
	} else {
		currentlyProcessing--;
	}

	while (
		currentlyProcessing < maxConcurrent &&
		typeof processingQueue[0] === 'function'
	) {
		currentlyProcessing++;
		const upNext = processingQueue.shift();
		upNext();
	}
}
