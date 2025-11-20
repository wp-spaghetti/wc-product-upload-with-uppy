import globals from 'globals';
import pluginJs from '@eslint/js';

import eslintPluginUnicorn from 'eslint-plugin-unicorn';

/** @type {import('eslint').Linter.Config[]} */
export default [
  {
    files: ['**/*.{js,mjs,cjs,ts}'],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.builtin,
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
