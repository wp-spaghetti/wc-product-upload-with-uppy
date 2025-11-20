import globals from 'globals';
import pluginJs from '@eslint/js';

import eslintPluginUnicorn from 'eslint-plugin-unicorn';

/** @type {import('eslint').Linter.Config[]} */
export default [
  {
    files: ['build.js'],
    languageOptions: {
      globals: {
        ...globals.node,
      },
    },
    plugins: {
      unicorn: eslintPluginUnicorn,
    },
    rules: {
      ...eslintPluginUnicorn.configs.recommended.rules,
    },
  },
  {
    files: ['resources/js/**/*.js'],
    languageOptions: {
      globals: {
        ...globals.browser,
        jQuery: 'readonly',
        WPSPAGHETTI_WCPUWU: 'readonly',
      },
    },
    plugins: {
      unicorn: eslintPluginUnicorn,
    },
    rules: {
      ...eslintPluginUnicorn.configs.recommended.rules,
    },
  },
  pluginJs.configs.recommended,
];
