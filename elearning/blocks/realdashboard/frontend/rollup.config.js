import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import typescript from '@rollup/plugin-typescript';
import postcss from 'rollup-plugin-postcss';
import { terser } from 'rollup-plugin-terser';
import json from '@rollup/plugin-json';
import jsonPlugin from './plugins/json-plugin.js';

const production = !process.env.ROLLUP_WATCH;

export default {
  input: './src/index.ts',
  output: {
    file: 'dist/index_002.js',
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
    production && terser({ format: { comments: false } }),
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