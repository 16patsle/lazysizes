import resolve from '@rollup/plugin-node-resolve';
import legacy from '@rollup/plugin-legacy';
import {terser} from 'rollup-plugin-terser';

export default {
	input: ['src/core.js'],
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
	],
	plugins: [
		resolve(),
		legacy({ 'lazysizes/lazysizes.js': 'lazysizes' })
	]
  };
