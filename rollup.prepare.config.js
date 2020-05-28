import resolve from '@rollup/plugin-node-resolve';

export default {
	input: [
		'lazysizes',
		'lazysizes/plugins/aspectratio/ls.aspectratio.js'
	],
	output: {
		dir: 'js/src/lib',
		entryFileNames: '[name].js',
		format: 'es'
	},
	plugins: [
		resolve()
	]
  };
