// @ts-check

/**
 * @type {Function[]}
 */
const processingQueue = [];
let currentlyProcessing = 0;
const maxConcurrent = 2;

/**
 * Queue of actions to perform, with a limit on how many at the same time.
 * Calling this function with a parameter add to the queue.
 * Calling it without one will continue execution of the queue.
 * @param {Function} [actionCallback] Function add to queue, and call when possible. Must call runAction without params when finished.
 */
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
