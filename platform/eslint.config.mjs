import js from '@eslint/js';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import prettierConfig from 'eslint-config-prettier';
import globals from 'globals';

/** @type {import('eslint').Linter.FlatConfig[]} */
export default [
  // Base JS rules (for .js/.mjs files only)
  {
    files: ['**/*.js', '**/*.mjs', '**/*.cjs'],
    ...js.configs.recommended,
    languageOptions: {
      globals: { ...globals.node },
    },
  },

  // TypeScript files — all packages
  {
    files: ['**/*.ts', '**/*.tsx'],
    languageOptions: {
      parser: tsParser,
      parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
      // Browser + Node globals available to all TS files
      // (TypeScript's own type checker handles undeclared names more accurately
      //  than ESLint's no-undef, so we provide broad globals here)
      globals: {
        ...globals.node,
        ...globals.browser,
      },
    },
    plugins: {
      '@typescript-eslint': tsPlugin,
    },
    rules: {
      ...tsPlugin.configs['recommended'].rules,
      // Disable no-undef for TypeScript — TypeScript's type checker handles this
      // more accurately (e.g. RequestInit, NodeJS.ProcessEnv, React types)
      'no-undef': 'off',
      '@typescript-eslint/no-explicit-any': 'error',
      '@typescript-eslint/no-unused-vars': [
        'error',
        { argsIgnorePattern: '^_', varsIgnorePattern: '^_' },
      ],
      '@typescript-eslint/consistent-type-imports': ['error', { prefer: 'type-imports' }],
      '@typescript-eslint/no-non-null-assertion': 'warn',
    },
  },

  // Prettier must be last to override formatting rules
  prettierConfig,

  // Global ignores
  {
    ignores: [
      '**/dist/**',
      '**/.next/**',
      '**/node_modules/**',
      '**/build/**',
      '**/*.d.ts',        // Generated declaration files
      '**/*.d.ts.map',
      'eslint.config.mjs',
    ],
  },
];
