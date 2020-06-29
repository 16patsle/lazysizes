const { promisify } = require('util');
const fs = require('fs');
const readFileAsync = promisify(fs.readFile);
const writeFileAsync = promisify(fs.writeFile);

const getCombination = require('./getCombinations');

const scripts = ['unveilhooks', 'autoload', 'aspectratio', 'nativeloading', 'blurhash'];
const n = scripts.length;

const scriptCombinations = [
	...getCombination(scripts, n, 1),
	...getCombination(scripts, n, 2),
	...getCombination(scripts, n, 3),
	...getCombination(scripts, n, 4),
	...getCombination(scripts, n, 5)
];

const entryPoints = ['js/src/lazysizes.js'];

scriptCombinations.forEach(combination => {
	const fileName = `lazysizes.${combination.join('-')}.js`;
	const contents = combination.reduce((acc, val) => {
		return acc += `import './${val}';\n`;
	}, `import './lib/lazysizes';\n`)
	entryPoints.push(`js/src/${fileName}`);

	writeFileAsync('./js/src/' + fileName, contents)
});

writeFileAsync('./js/src/entrypoints.json', JSON.stringify(entryPoints, null, 2));
