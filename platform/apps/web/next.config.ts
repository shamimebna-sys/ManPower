import type { NextConfig } from 'next';

const config: NextConfig = {
  // Transpile workspace packages so Next.js can handle their TypeScript source
  transpilePackages: ['@manpower/shared'],

  // Strict mode for React — highlights potential problems
  reactStrictMode: true,

  // Disable the X-Powered-By header
  poweredByHeader: false,

  // Security headers applied on every response
  async headers() {
    return [
      {
        source: '/(.*)',
        headers: [
          { key: 'X-Frame-Options', value: 'DENY' },
          { key: 'X-Content-Type-Options', value: 'nosniff' },
          { key: 'Referrer-Policy', value: 'strict-origin-when-cross-origin' },
          {
            key: 'Permissions-Policy',
            value: 'camera=(), microphone=(), geolocation=()',
          },
        ],
      },
    ];
  },

  // Environment variables exposed to the browser (prefix NEXT_PUBLIC_)
  // These are validated in src/lib/config.ts
  env: {},
};

export default config;
