import type { Metadata } from 'next';
import Link from 'next/link';

export const metadata: Metadata = {
  title: 'Page Not Found',
};

/**
 * Global 404 page.
 */
export default function NotFoundPage() {
  return (
    <div style={{ padding: '2rem', maxWidth: '400px', margin: '4rem auto', textAlign: 'center' }}>
      <h1 style={{ fontSize: '3rem', fontWeight: 700, color: '#dee2e6', marginBottom: '0.25rem' }}>
        404
      </h1>
      <h2 style={{ fontSize: '1.25rem', fontWeight: 600, marginBottom: '0.75rem' }}>
        Page not found
      </h2>
      <p style={{ color: '#6c757d', marginBottom: '1.5rem', fontSize: '0.9rem' }}>
        The page you requested does not exist or you do not have permission to view it.
      </p>
      <Link
        href="/"
        style={{
          padding: '0.5rem 1.5rem',
          background: '#0070f3',
          color: '#fff',
          borderRadius: '4px',
          display: 'inline-block',
          fontSize: '0.9rem',
        }}
      >
        Go home
      </Link>
    </div>
  );
}
