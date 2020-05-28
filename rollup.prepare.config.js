import resolve from '@rollup/plugin-node-resolve';
import {terser} from 'rollup-plugin-terser';

export default {
	input: [
		'lazysizes',
		'lazysizes/plugins/aspectratio/ls.aspectratio.js',
		'lazysizes/plugins/native-loading/ls.native-loading.js',
		'lazysizes/plugins/unveilhooks/ls.unveilhooks.js'
	],
	output: [
		{
			dir: 'js/src/lib',
			entryFileNames: '[name].js',
			format: 'es'
		},
		{
			dir: 'js',
			entryFileNames: '[name].js',
			format: 'cjs'
		},
		{
			dir: 'js',
			entryFileNames: '[name].min.js',
			format: 'cjs',
			plugins: [terser()]
		}
	],
	plugins: [
		resolve()
	]
  };
