const { promisify } = require('util');
const fs = require('fs');
const readFileAsync = promisify(fs.readFile);
const writeFileAsync = promisify(fs.writeFile);

const getCombination = require('./getCombinations');

const scripts = ['unveilhooks', 'autoload', 'aspectratio', 'nativeloading'];
const n = scripts.length;

const scriptCombinations = [
	...getCombination(scripts, n, 1),
	...getCombination(scripts, n, 2),
	...getCombination(scripts, n, 3),
	...getCombination(scripts, n, 4)
];

scriptCombinations.forEach(combination => {
	const fileName = `core-${combination.join('-')}.js`;
	const contents = combination.reduce((acc, val) => {
		return acc += `import './${val}';\n`;
	}, `import './lib/lazysizes';\n`)

	writeFileAsync('./js/src/' + fileName, contents)
});
