'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { CandidateListResult, CandidateSummary } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';

export default function CandidatesListPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<CandidateSummary[]>([]);
  const [q, setQ] = useState('');
  const [status, setStatus] = useState('');
  const [cursor, setCursor] = useState<string | null>(null);
  const [nextCursor, setNextCursor] = useState<string | null>(null);
  const [total, setTotal] = useState<number | undefined>(undefined);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    const params = new URLSearchParams({ limit: '20' });
    if (q) params.set('q', q);
    if (status) params.set('status', status);
    if (cursor) params.set('cursor', cursor);
    const response = await fetch(`${config.apiUrl}/api/v1/candidates?${params.toString()}`, {
      credentials: 'include',
    });
    const json = (await response.json()) as {
      success: boolean;
      data?: CandidateListResult;
      meta?: { pagination?: { nextCursor: string | null; total?: number } };
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load candidates');
      return;
    }
    setItems(json.data.items);
    setNextCursor(json.meta?.pagination?.nextCursor ?? null);
    setTotal(json.meta?.pagination?.total);
    setError('');
  }, [q, status, cursor, router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user) void load();
  }, [loading, user, load]);

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Candidates</h1>
          <Link className="button-link" href="/app/candidates/new">
            New candidate
          </Link>
        </div>
        <form
          className="filter-row"
          onSubmit={(event) => {
            event.preventDefault();
            setCursor(null);
          }}
        >
          <input
            placeholder="Search name, passport, NID, mobile, email, code"
            value={q}
            onChange={(event) => {
              setQ(event.target.value);
              setCursor(null);
            }}
          />
          <select
            value={status}
            onChange={(event) => {
              setStatus(event.target.value);
              setCursor(null);
            }}
          >
            <option value="">All statuses</option>
            <option value="A">Active (A)</option>
            <option value="P">Pending (P)</option>
            <option value="I">Inactive (I)</option>
          </select>
        </form>
        {error ? <p className="auth-error">{error}</p> : null}
        <p className="muted">{total !== undefined ? `${total} records` : 'Candidate master'}</p>
        <table className="data-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Mobile</th>
              <th>Passport</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((candidate) => (
              <tr key={candidate.id}>
                <td>
                  <Link href={`/app/candidates/${candidate.id}`}>{candidate.code ?? '—'}</Link>
                </td>
                <td>{candidate.name ?? '—'}</td>
                <td>{candidate.mobile ?? '—'}</td>
                <td>{candidate.passportNo ?? '—'}</td>
                <td>{candidate.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
        <div className="pager">
          <button type="button" disabled={!cursor} onClick={() => setCursor(null)}>
            First page
          </button>
          <button type="button" disabled={!nextCursor} onClick={() => setCursor(nextCursor)}>
            Next page
          </button>
        </div>
      </main>
    </section>
  );
}
