import resolve from '@rollup/plugin-node-resolve';
import legacy from '@rollup/plugin-legacy';

export default {
	input: ['lazysizes'],
	output: {
		dir: 'js/src/lib',
		entryFileNames: '[name].js',
		format: 'es'
	},
	plugins: [
		resolve(),
		legacy({ 'lazysizes/lazysizes.js': 'lazysizes' })
	]
  };
