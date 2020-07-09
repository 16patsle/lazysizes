import babel from '@rollup/plugin-babel';
import commonjs from '@rollup/plugin-commonjs';
import postcss from 'rollup-plugin-postcss';
import resolve from '@rollup/plugin-node-resolve';
import {terser} from 'rollup-plugin-terser';
import visualizer from 'rollup-plugin-visualizer';
import inputs from './js/src/entrypoints.json';

const defaultConfigJS = {
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

const defaultConfigCSS = {
	input: '',
	output: [
		{
			dir: 'css/build',
			entryFileNames: '[name].js',
			format: 'esm',
		},
	],
	plugins: [postcss({ extract: true })],
};

const configs = [
	{
		input: './js/admin/src/lazysizes-attachment-details.js',
		output: [
			{
				file: './js/admin/build/lazysizes-attachment-details.js',
				format: 'iife',
			},
			{
				file: './js/admin/build/lazysizes-attachment-details.min.js',
				format: 'iife',
				plugins: [terser()],
			},
		],
		plugins: [
			babel({ babelHelpers: 'bundled', exclude: 'node_modules/**' }),
			resolve(),
			commonjs(),
			visualizer({ filename: './js/admin/build/stats.html' }),
		],
	},
];

inputs.forEach((val) => {
	const config = Object.assign({}, defaultConfigJS);
	config.input = val;
	configs.push(config);
});

const inputsCSS = [
	'css/src/fadein.js',
	'css/src/fadein-blurhash.js',
	'css/src/spinner.js',
];

inputsCSS.forEach((val) => {
	// Default unmified CSS build
	const config = Object.assign({}, defaultConfigCSS);
	config.input = val;
	configs.push(config);

	// Minified build
	const configMin = Object.assign({}, defaultConfigCSS);
	const split = val.split('/');
	configMin.plugins = [
		postcss({
			extract: split[split.length - 1].replace('.js', '.min.css'),
			minimize: true,
		}),
	];
	configMin.input = val;
	configs.push(configMin);
});

export default configs;
