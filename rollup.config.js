import {terser} from 'rollup-plugin-terser';

const defaultConfig = {
	input: '',
	output: [
		{
			dir: 'js/build',
			entryFileNames: '[name].js',
	  		format: 'iife'
		},
		{
			dir: 'js/build',
			entryFileNames: '[name].min.js',
			format: 'iife',
			plugins: [terser()]
		}
	]
};
const configs = [];
const inputs = [
	'js/src/lazysizes.js',
	'js/src/lazysizes.unveilhooks.js',
	'js/src/lazysizes.unveilhooks-autoload.js',
	'js/src/lazysizes.unveilhooks-autoload-aspectratio.js',
	'js/src/lazysizes.unveilhooks-autoload-aspectratio-nativeloading.js',
	'js/src/lazysizes.unveilhooks-autoload-nativeloading.js',
	'js/src/lazysizes.unveilhooks-aspectratio.js',
	'js/src/lazysizes.unveilhooks-aspectratio-nativeloading.js',
	'js/src/lazysizes.unveilhooks-nativeloading.js',
	'js/src/lazysizes.autoload.js',
	'js/src/lazysizes.autoload-aspectratio.js',
	'js/src/lazysizes.autoload-aspectratio-nativeloading.js',
	'js/src/lazysizes.autoload-nativeloading.js',
	'js/src/lazysizes.aspectratio.js',
	'js/src/lazysizes.aspectratio-nativeloading.js',
	'js/src/lazysizes.nativeloading.js',
]

inputs.forEach(val => {
	const config = Object.assign({}, defaultConfig);
	config.input = val;
	configs.push(config);
})

export default configs;
