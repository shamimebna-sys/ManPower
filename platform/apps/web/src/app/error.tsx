'use client';

import { useEffect } from 'react';

interface ErrorPageProps {
  error: Error & { digest?: string };
  reset: () => void;
}

/**
 * Error boundary for the root segment.
 * Catches runtime errors in page components and displays a recovery UI.
 */
export default function ErrorPage({ error, reset }: ErrorPageProps) {
  useEffect(() => {
    // Log to error monitoring service (e.g. Sentry) — configured in M14
    console.error('Unhandled page error:', error);
  }, [error]);

  return (
    <div style={{ padding: '2rem', maxWidth: '500px', margin: '4rem auto', textAlign: 'center' }}>
      <h2 style={{ fontSize: '1.25rem', fontWeight: 600, marginBottom: '0.5rem', color: '#dc3545' }}>
        Something went wrong
      </h2>
      <p style={{ color: '#6c757d', marginBottom: '1.5rem', fontSize: '0.9rem' }}>
        {process.env['NODE_ENV'] !== 'production' ? error.message : 'An unexpected error occurred.'}
      </p>
      <button
        onClick={reset}
        style={{
          padding: '0.5rem 1.5rem',
          background: '#0070f3',
          color: '#fff',
          border: 'none',
          borderRadius: '4px',
          cursor: 'pointer',
          fontSize: '0.9rem',
        }}
      >
        Try again
      </button>
    </div>
  );
}
