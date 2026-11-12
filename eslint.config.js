'use strict';

const { defineConfig, globalIgnores } = require( 'eslint/config' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );

module.exports = defineConfig( [
	globalIgnores( [
		'i18n/',
		'vendor/'
	] ),

	// resources/ runs in the browser and has its own config below
	{
		ignores: [ 'resources/**' ],
		extends: [ wikimedia( 'server' ) ]
	},

	{
		files: [ 'resources/**' ],
		extends: [ wikimedia( 'client', 'mediawiki', 'jquery' ) ],
		languageOptions: {
			ecmaVersion: 2020,
			sourceType: 'module',
			globals: {
				exports: 'writable'
			}
		},
		rules: {
			'no-jquery/no-parse-html-literal': 'warn',
			'no-jquery/no-global-selector': 'warn',
			'no-console': 'warn',
			'no-jquery/no-class-state': 'warn',
			'no-implicit-globals': 'warn'
		}
	}
] );
