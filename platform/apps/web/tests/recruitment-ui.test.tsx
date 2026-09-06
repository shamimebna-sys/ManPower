import { beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';

const mocks = vi.hoisted(() => ({
  replace: vi.fn(),
  user: {
    id: 'user-id',
    email: 'admin@example.com',
    username: 'admin',
    displayName: 'Administrator',
    status: 'ACTIVE' as const,
    roles: ['super_admin'],
    permissions: ['partners.read', 'partners.manage', 'employer_candidate.read', 'employer_candidate.manage'],
  },
}));

vi.mock('next/link', () => ({
  default: ({ href, children }: { href: string; children: React.ReactNode }) => (
    <a href={href}>{children}</a>
  ),
}));
vi.mock('next/navigation', () => ({
  useRouter: () => ({ replace: mocks.replace }),
  useParams: () => ({ type: 'agent', id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' }),
}));
vi.mock('../src/lib/api-client', () => ({
  apiClient: {
    get: vi.fn().mockResolvedValue({ items: [] }),
    post: vi.fn(),
    patch: vi.fn(),
  },
  ApiClientError: class ApiClientError extends Error {
    constructor(public statusCode: number, public code: string, message: string) {
      super(message);
    }
  },
}));
vi.mock('../src/components/auth-provider', () => ({
  useAuth: () => ({
    user: mocks.user,
    loading: false,
    login: vi.fn(),
    logout: vi.fn(),
    refresh: vi.fn(),
  }),
}));

import PartnersPage from '../src/app/app/partners/page';
import EmployerCandidatesPage from '../src/app/app/employer-candidates/page';

describe('M4 recruitment UI', () => {
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

  it('renders partner type tabs and create controls', async () => {
    render(<PartnersPage />);
    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Recruitment partners' })).toBeInTheDocument();
    });
    expect(screen.getByRole('button', { name: 'agent' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Create' })).toBeInTheDocument();
  });

  it('renders employer assignment purpose and status controls', async () => {
    render(<EmployerCandidatesPage />);
    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Employer assignments' })).toBeInTheDocument();
    });
    expect(screen.getByLabelText('Purpose')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Assign' })).toBeInTheDocument();
  });
});
