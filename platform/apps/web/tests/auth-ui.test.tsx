import { beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

const mocks = vi.hoisted(() => ({
  login: vi.fn(),
  logout: vi.fn(),
  replace: vi.fn(),
  user: null as null | {
    id: string;
    email: string;
    username: string;
    displayName: string;
    status: 'ACTIVE';
    roles: string[];
    permissions: string[];
  },
}));

vi.mock('next/link', () => ({
  default: ({ href, children }: { href: string; children: React.ReactNode }) => (
    <a href={href}>{children}</a>
  ),
}));
vi.mock('next/navigation', () => ({
  useRouter: () => ({ replace: mocks.replace }),
}));
vi.mock('../src/components/auth-provider', () => ({
  useAuth: () => ({
    user: mocks.user,
    loading: false,
    login: mocks.login,
    logout: mocks.logout,
    refresh: vi.fn(),
  }),
}));

import LoginPage from '../src/app/login/page';
import AuthenticatedShellPage from '../src/app/app/page';

describe('authentication UI', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mocks.user = null;
  });

  it('submits email/username and password then navigates to the protected shell', async () => {
    mocks.login.mockResolvedValue(undefined);
    const user = userEvent.setup();
    render(<LoginPage />);

    await user.type(screen.getByLabelText('Email or username'), 'admin@example.com');
    await user.type(screen.getByLabelText('Password'), 'StrongPassword!42');
    await user.click(screen.getByRole('button', { name: 'Sign in' }));

    expect(mocks.login).toHaveBeenCalledWith('admin@example.com', 'StrongPassword!42');
    expect(mocks.replace).toHaveBeenCalledWith('/app');
  });

  it('redirects an unauthenticated visitor away from the shell', () => {
    render(<AuthenticatedShellPage />);
    expect(mocks.replace).toHaveBeenCalledWith('/login');
  });

  it('renders authenticated identity and supports logout', async () => {
    mocks.user = {
      id: 'user-id',
      email: 'admin@example.com',
      username: 'admin',
      displayName: 'Administrator',
      status: 'ACTIVE',
      roles: ['super_admin'],
      permissions: [],
    };
    mocks.logout.mockResolvedValue(undefined);
    const user = userEvent.setup();
    render(<AuthenticatedShellPage />);
    expect(screen.getByText('Welcome, Administrator')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'Sign out' }));
    expect(mocks.logout).toHaveBeenCalledOnce();
  });
});
