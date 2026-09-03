'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import Link from 'next/link';

export default function AuthenticatedShellPage() {
  const router = useRouter();
  const { user, loading } = useAuth();

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <h1>Welcome, {user.displayName}</h1>
        <p>Authentication and the candidate master are available.</p>
        <dl>
          <dt>Account</dt><dd>{user.email}</dd>
          <dt>Status</dt><dd>{user.status}</dd>
          <dt>Roles</dt><dd>{user.roles.join(', ') || 'No roles assigned'}</dd>
        </dl>
        <Link className="button-link" href="/app/candidates">Open candidates</Link>
      </main>
    </section>
  );
}
