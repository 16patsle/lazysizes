import getBlurhash from './blurhash/getBlurhash';

onmessage = (event) => {
	const [blurhash, width, height] = event.data;
	getBlurhash(blurhash, width, height, (blob) => {
		postMessage(blob);
	});
};
