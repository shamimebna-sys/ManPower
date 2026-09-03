/**
 * Web application foundation tests
 *
 * Tests the public status pages and configuration.
 * API client and config module tests do not require a browser or real API.
 */
import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

// ── Mock Next.js navigation (not needed in M1 page) ──────────────────────
vi.mock('next/link', () => ({
  default: ({ href, children }: { href: string; children: React.ReactNode }) => (
    <a href={href}>{children}</a>
  ),
}));

import NotFoundPage from '../src/app/not-found';

describe('NotFoundPage', () => {
  it('renders the 404 page', () => {
    render(<NotFoundPage />);
    expect(screen.getByText('404')).toBeInTheDocument();
    expect(screen.getByText('Page not found')).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'Go home' })).toHaveAttribute('href', '/');
  });
});

describe('API client config', () => {
  it('uses NEXT_PUBLIC_API_URL from environment', async () => {
    const { config } = await import('../src/lib/config');
    expect(config.apiUrl).toBe('http://localhost:4000');
  });

  it('sets environment from NODE_ENV', async () => {
    const { config } = await import('../src/lib/config');
    expect(config.environment).toBe('test');
  });
});
