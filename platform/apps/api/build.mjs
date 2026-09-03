/**
 * API build script — uses esbuild to bundle the TypeScript API.
 *
 * Produces a single dist/index.js that includes all application code
 * and the shared package (inlined), while leaving native/binary
 * dependencies as externals.
 *
 * Run: node build.mjs
 */
import { build } from 'esbuild';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

const result = await build({
  entryPoints: [resolve(__dirname, 'src/index.ts')],
  bundle: true,
  platform: 'node',
  target: 'node20',
  format: 'cjs',
  outfile: resolve(__dirname, 'dist/index.js'),
  sourcemap: true,
  // tsconfig paths are handled by esbuild's tsconfig support
  tsconfig: resolve(__dirname, 'tsconfig.json'),
  // External: packages with native binaries or that must be resolved at runtime
  external: [
    '@prisma/client',
    '@prisma/engines',
    'pino-pretty', // optional dev transport
  ],
  // Log all warnings
  logLevel: 'info',
});

if (result.errors.length > 0) {
  console.error('Build failed:', result.errors);
  process.exit(1);
}

console.log('✅ API build complete → dist/index.js');
