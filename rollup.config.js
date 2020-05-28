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
	'js/src/core-unveilhooks.js',
	'js/src/core-unveilhooks-autoload.js',
	'js/src/core-unveilhooks-autoload-aspectratio.js',
	'js/src/core-unveilhooks-autoload-aspectratio-nativeloading.js',
	'js/src/core-unveilhooks-autoload-nativeloading.js',
	'js/src/core-unveilhooks-aspectratio.js',
	'js/src/core-unveilhooks-aspectratio-nativeloading.js',
	'js/src/core-unveilhooks-nativeloading.js',
	'js/src/core-autoload.js',
	'js/src/core-autoload-aspectratio.js',
	'js/src/core-autoload-aspectratio-nativeloading.js',
	'js/src/core-autoload-nativeloading.js',
	'js/src/core-aspectratio.js',
	'js/src/core-aspectratio-nativeloading.js',
	'js/src/core-nativeloading.js',
]

inputs.forEach(val => {
	const config = Object.assign({}, defaultConfig);
	config.input = val;
	configs.push(config);
})

export default configs;
