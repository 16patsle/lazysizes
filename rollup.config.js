import {terser} from 'rollup-plugin-terser';

const defaultConfig = {
	input: '',
	output: [
		{
			dir: 'js/build',
			entryFileNames: 'lazysizes.[name].js',
	  		format: 'iife'
		},
		{
			dir: 'js/build',
			entryFileNames: 'lazysizes.[name].min.js',
			format: 'iife',
			plugins: [terser()]
		}
	]
};
const configs = [];
const inputs = [
	'js/src/core.js',
	'js/src/core-unveilhooks.js'
]

inputs.forEach(val => {
	const config = Object.assign({}, defaultConfig);
	config.input = val;
	configs.push(config);
})

export default configs;
