const { promisify } = require('util');
const fs = require('fs');
const writeFileAsync = promisify(fs.writeFile);

const getCombination = require('./getCombinations');

// Script combinations
const scripts = ['unveilhooks', 'autoload', 'aspectratio', 'nativeloading', 'blurhash'];
const n = scripts.length;

const scriptCombinations = [
	...getCombination(scripts, n, 1),
	...getCombination(scripts, n, 2),
	...getCombination(scripts, n, 3),
	...getCombination(scripts, n, 4),
	...getCombination(scripts, n, 5)
];

const scriptEntryPoints = ['js/src/lazysizes.js'];

scriptCombinations.forEach(combination => {
	const fileName = `lazysizes.${combination.join('-')}.js`;
	const contents = combination.reduce((acc, val) => {
		return acc += `import './${val}';\n`;
	}, `import './lib/lazysizes';\n`)
	scriptEntryPoints.push(`js/src/${fileName}`);

	writeFileAsync('./js/src/' + fileName, contents)
});

writeFileAsync('./js/src/entrypoints.json', JSON.stringify(scriptEntryPoints, null, 2));

// Style combinations
const styles = ['fadein', 'fadeblurhash', 'spinner'];
const m = styles.length;

const styleCombinations = [
	...getCombination(styles, m, 1),
	...getCombination(styles, m, 2),
	...getCombination(styles, m, 3)
];

const styleEntryPoints = [];

styleCombinations.forEach(combination => {
	const fileName = `lazysizes.${combination.join('-')}.js`;
	const contents = combination.reduce((acc, val) => {
		return acc += `import './${val}.css';\n`;
	}, '')
	styleEntryPoints.push(`css/src/${fileName}`);

	writeFileAsync('./css/src/' + fileName, contents)
});

writeFileAsync('./css/src/entrypoints.json', JSON.stringify(styleEntryPoints, null, 2));
