import babel from '@rollup/plugin-babel';
import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import {terser} from 'rollup-plugin-terser';
import inputs from './js/src/entrypoints.json';

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
	],
	plugins: [
		commonjs(),
		resolve()
	]
};
const configs = [{
	input: './js/admin/src/lazysizes-attachment-details.js',
	output: [
		{
			file: './js/admin/build/lazysizes-attachment-details.js',
			format: 'iife'
		},
		{
			file: './js/admin/build/lazysizes-attachment-details.min.js',
			format: 'iife',
			plugins: [terser()]
		}
	],
	plugins: [
		babel({ babelHelpers: 'bundled', exclude: 'node_modules/**' }),
		resolve(),
		commonjs(),
	]
}];

inputs.forEach(val => {
	const config = Object.assign({}, defaultConfig);
	config.input = val;
	configs.push(config);
})

export default configs;
