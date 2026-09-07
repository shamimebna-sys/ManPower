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
    permissions: ['finance.wallet.read', 'finance.read', 'finance.payment_request.create', 'finance.fx_rate.manage'],
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
import FinanceWalletsPage from '../src/app/app/finance/page';
import FxRatesPage from '../src/app/app/finance/fx/page';

describe('M7 finance UI', () => {
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

  it('shows finance navigation when permitted', () => {
    render(<AppHeader />);
    expect(screen.getByRole('link', { name: 'Finance' })).toHaveAttribute('href', '/app/finance');
  });

  it('renders wallets and FX pages', async () => {
    render(<FinanceWalletsPage />);
    expect(await screen.findByRole('heading', { name: 'Finance wallets' })).toBeInTheDocument();
    render(<FxRatesPage />);
    expect(await screen.findByRole('heading', { name: 'Administrative FX rates' })).toBeInTheDocument();
  });
});
