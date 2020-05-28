import {terser} from 'rollup-plugin-terser';

export default {
	input: ['js/src/core.js'],
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
