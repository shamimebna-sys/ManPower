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
    permissions: ['candidate.read'],
  },
}));

vi.mock('next/link', () => ({
  default: ({ href, children }: { href: string; children: React.ReactNode }) => (
    <a href={href}>{children}</a>
  ),
}));
vi.mock('next/navigation', () => ({
  useRouter: () => ({ replace: mocks.replace }),
  useParams: () => ({ id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' }),
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

import NewCandidatePage from '../src/app/app/candidates/new/page';
import { CandidateProfileSections } from '../src/components/candidate-profile-sections';

describe('candidate UI foundation', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders the create-candidate form for an authenticated user', () => {
    render(<NewCandidatePage />);
    expect(screen.getByRole('heading', { name: 'Create candidate' })).toBeInTheDocument();
    expect(screen.getByLabelText('Name')).toBeInTheDocument();
    expect(screen.getByLabelText('Passport number')).toBeInTheDocument();
  });

  it('renders supporting-domain tabs on the candidate profile panel', async () => {
    render(
      <CandidateProfileSections
        candidateId="aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa"
        onForbidden={vi.fn()}
      />
    );
    expect(screen.getByRole('heading', { name: 'Profile records' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Education' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Experience' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Skills' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Languages' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Training' })).toBeInTheDocument();
    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Add education' })).toBeInTheDocument();
    });
  });
});
