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
const configs = [];

inputs.forEach(val => {
	const config = Object.assign({}, defaultConfig);
	config.input = val;
	configs.push(config);
})

export default configs;
