// @ts-check

/**
 * @type {WorkerDataObject[]}
 */
const workers = [];

let workerUrl;

/**
 * Sets the worker URL
 * @param {string} url The url to the worker file
 */
export function setWorkerUrl(url) {
	workerUrl = url;
}

/**
 * Object containing a worker and related objects and data
 * @typedef {Object} WorkerDataObject
 * @property {Worker} [worker] The worker object
 * @property {boolean} [used] Whether this canvas is currently used
 */

/**
 * Get a worker from the pool, or create a new one if all are in use.
 * @param {EventListenerOrEventListenerObject} [onError] Function to assign as error handler
 * @returns {WorkerDataObject} An object with a usable worker
 */
export default function getWorker(onError) {
	const unusedWorkers = workers.filter(
		(worker) => worker && worker.used === false
	);
	let worker = unusedWorkers[0];
	if (!worker) {
		worker = {};
		worker.worker = new Worker(workerUrl);
		if (onError) {
			worker.worker.addEventListener('error', onError, false);
		}
		workers.push(worker);
	}
	worker.used = true;
	return worker;
}
