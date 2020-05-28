import resolve from '@rollup/plugin-node-resolve';

export default {
	input: [
		'lazysizes',
		'lazysizes/plugins/aspectratio/ls.aspectratio.js',
		'lazysizes/plugins/native-loading/ls.native-loading.js',
		'lazysizes/plugins/unveilhooks/ls.unveilhooks.js'
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
