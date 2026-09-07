'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import Link from 'next/link';
import { hasAnyPermission, hasPermission, OVERSEAS_READ_KEYS } from '@/lib/permissions';

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
        <p>Authentication, candidate master, and recruitment are available.</p>
        <dl>
          <dt>Account</dt><dd>{user.email}</dd>
          <dt>Status</dt><dd>{user.status}</dd>
          <dt>Roles</dt><dd>{user.roles.join(', ') || 'No roles assigned'}</dd>
        </dl>
        <p className="filter-row">
          {hasPermission(user, 'candidate.read') ? (
            <Link className="button-link" href="/app/candidates">Open candidates</Link>
          ) : null}
          {hasPermission(user, 'partners.read') ? (
            <Link className="button-link" href="/app/partners">Open partners</Link>
          ) : null}
          {hasPermission(user, 'employer_candidate.read') ? (
            <Link className="button-link" href="/app/employer-candidates">Open assignments</Link>
          ) : null}
          {hasPermission(user, 'training.teacher.read') ? (
            <Link className="button-link" href="/app/teachers">Open teachers</Link>
          ) : null}
          {hasPermission(user, 'training.exam.read') ? (
            <Link className="button-link" href="/app/exams">Open exams</Link>
          ) : null}
          {hasPermission(user, 'training.manpower.read') ? (
            <Link className="button-link" href="/app/manpower-trainings">Open manpower training</Link>
          ) : null}
          {hasAnyPermission(user, OVERSEAS_READ_KEYS) ? (
            <Link className="button-link" href="/app/overseas">Open overseas processing</Link>
          ) : null}
          {hasPermission(user, 'operations.license.read') ? (
            <Link className="button-link" href="/app/licenses">Open licenses</Link>
          ) : null}
        </p>
      </main>
    </section>
  );
}
