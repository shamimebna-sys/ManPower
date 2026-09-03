import type { Application } from 'express';
import helmet from 'helmet';
import cors from 'cors';
import express from 'express';
import { getEnv } from '../config/env.js';

/**
 * Registers security middleware on the Express app.
 *
 * Includes:
 *  - Helmet: secure HTTP response headers
 *  - CORS: origin whitelisting from env
 *  - Body size limits: prevent payload attacks
 *  - Trust proxy: correct IP when behind Nginx/load balancer
 *
 * M1 SCOPE: No authentication middleware yet (implemented in M2).
 */
export function applySecurityMiddleware(app: Application): void {
  const env = getEnv();

  // Trust the first proxy (Nginx in production)
  app.set('trust proxy', 1);

  // ── Helmet — security headers ─────────────────────────────────────────
  app.use(
    helmet({
      // Content Security Policy — tightened for API responses (no HTML)
      contentSecurityPolicy: {
        directives: {
          defaultSrc: ["'none'"],
          frameAncestors: ["'none'"],
        },
      },
      // Hide X-Powered-By (Express removes it, Helmet adds extra protection)
      hidePoweredBy: true,
      // Prevent MIME type sniffing
      noSniff: true,
      // HSTS: only send over HTTPS
      hsts:
        env.NODE_ENV === 'production'
          ? { maxAge: 31536000, includeSubDomains: true, preload: true }
          : false,
      // Prevent clickjacking
      frameguard: { action: 'deny' },
      // XSS filter (legacy browsers)
      xssFilter: true,
    })
  );

  // ── CORS ──────────────────────────────────────────────────────────────
  const allowedOrigins = env.CORS_ORIGINS;

  app.use(
    cors({
      origin: (origin, callback) => {
        // Allow requests with no origin (server-to-server, health checks)
        if (!origin) {
          callback(null, true);
          return;
        }
        if (allowedOrigins.includes(origin)) {
          callback(null, true);
        } else {
          callback(new Error(`CORS: origin '${origin}' not allowed`));
        }
      },
      methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization', 'X-Request-Id'],
      exposedHeaders: ['X-Request-Id'],
      credentials: true,
      // Preflight cache: 2 minutes
      maxAge: 120,
    })
  );

  // ── Body parsing + limits ─────────────────────────────────────────────
  // JSON body — max 1MB (increase per-route for file-heavy endpoints)
  app.use(express.json({ limit: '1mb' }));
  // URL-encoded form body — max 256KB
  app.use(express.urlencoded({ extended: true, limit: '256kb' }));

  // Remove X-Powered-By header explicitly
  app.disable('x-powered-by');
}
