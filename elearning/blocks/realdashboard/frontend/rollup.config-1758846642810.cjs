'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var resolve = require('@rollup/plugin-node-resolve');
var commonjs = require('@rollup/plugin-commonjs');
var typescript = require('@rollup/plugin-typescript');
var postcss = require('rollup-plugin-postcss');
var rollupPluginTerser = require('rollup-plugin-terser');
var json = require('@rollup/plugin-json');

function jsonPlugin() {
  return {
    name: 'json-plugin',
    transform(code, id) {
      if (!id.endsWith('.json')) return null;

      try {
        const parsed = JSON.parse(code);
        const exportCode = `export default ${JSON.stringify(parsed, null, 2)};`;
        return {
          code: exportCode,
          map: null // No source map needed for JSON
        };
      } catch (error) {
        this.error(`Failed to parse JSON in ${id}: ${error.message}`);
      }
    }
  };
}

const production = !process.env.ROLLUP_WATCH;

var rollup_config = {
  input: './src/index.ts',
  output: {
    file: 'dist/index_001.js',
    format: 'es',
    sourcemap: true,
  },
  plugins: [
    resolve(),
    commonjs(),
    postcss({
      extract: 'styles.css',
      minimize: production,
      plugins: [
        require('postcss-import'),
        require('@tailwindcss/postcss'),
        require('autoprefixer'),
      ],
    }),
    typescript({ 
        include: ['src/**/*.ts'],
        tsconfig: './tsconfig.json',
     }),
    production && rollupPluginTerser.terser({ format: { comments: false } }),
   json(
      {
        compact: true,
        preferConst: true,
        indent: '  ',
        namedExports: false, // Use default export for JSON
      }
    ),
  ],
};

exports.default = rollup_config;
