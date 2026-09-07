import { beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

const mocks = vi.hoisted(() => ({
  replace: vi.fn(),
  user: {
    id: 'user-id',
    email: 'owner@example.com',
    username: 'owner',
    displayName: 'Owner',
    status: 'ACTIVE' as const,
    roles: ['owner'],
    permissions: [
      'overseas.medical.read',
      'overseas.medical.manage',
      'overseas.labour_contract.read',
      'operations.license.read',
      'operations.license.manage',
    ],
  },
}));

vi.mock('next/link', () => ({
  default: ({ href, children }: { href: string; children: React.ReactNode }) => <a href={href}>{children}</a>,
}));
vi.mock('next/navigation', () => ({
  useRouter: () => ({ replace: mocks.replace }),
}));
vi.mock('../src/lib/api-client', () => ({
  apiClient: { get: vi.fn().mockResolvedValue({ items: [] }), post: vi.fn(), patch: vi.fn() },
  ApiClientError: class ApiClientError extends Error {
    constructor(
      public statusCode: number,
      public code: string,
      message: string
    ) {
      super(message);
    }
  },
}));
vi.mock('../src/components/auth-provider', () => ({
  useAuth: () => ({ user: mocks.user, loading: false, login: vi.fn(), logout: vi.fn(), refresh: vi.fn() }),
}));

import { AppHeader } from '../src/components/app-header';
import OverseasPage from '../src/app/app/overseas/page';
import LicensesPage from '../src/app/app/licenses/page';

describe('M6 overseas UI', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => ({ success: true, data: { items: [] } }),
      })
    );
  });

  it('shows overseas and license navigation when permitted', () => {
    render(<AppHeader />);
    expect(screen.getByRole('link', { name: 'Overseas' })).toHaveAttribute('href', '/app/overseas');
    expect(screen.getByRole('link', { name: 'Licenses' })).toHaveAttribute('href', '/app/licenses');
  });

  it('renders overseas and license pages', async () => {
    render(<OverseasPage />);
    expect(await screen.findByRole('heading', { name: 'Overseas processing' })).toBeInTheDocument();
    render(<LicensesPage />);
    expect(await screen.findByRole('heading', { name: 'Licenses' })).toBeInTheDocument();
  });
});
