import babel from '@rollup/plugin-babel';
import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import {terser} from 'rollup-plugin-terser';
import visualizer from 'rollup-plugin-visualizer';
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
		babel({
			babelHelpers: 'bundled',
			include: ['js/src/blurhash.js', 'js/src/lib/decode.js'],
			presets: [
				[
				  '@babel/env',
				  {
					targets: 'extends @wordpress/browserslist-config',
					bugfixes: true
				  }
				]
			  ]
		 }),
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
		visualizer({ filename: './js/admin/build/stats.html' })
	]
}];

inputs.forEach(val => {
	const config = Object.assign({}, defaultConfig);
	config.input = val;
	configs.push(config);
})

export default configs;
