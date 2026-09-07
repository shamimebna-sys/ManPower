'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { LicenseListResult, LicenseRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function LicensesPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<LicenseRecord[]>([]);
  const [error, setError] = useState('');
  const [licenseNo, setLicenseNo] = useState('');
  const [companierId, setCompanierId] = useState('');

  const load = useCallback(async () => {
    const response = await fetch(`${config.apiUrl}/api/v1/licenses?limit=50`, { credentials: 'include' });
    const json = (await response.json()) as {
      success: boolean;
      data?: LicenseListResult;
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load licenses');
      return;
    }
    setItems(json.data.items);
    setError('');
  }, [router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && !hasPermission(user, 'operations.license.read')) {
      router.replace('/forbidden');
    }
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && hasPermission(user, 'operations.license.read')) void load();
  }, [loading, user, load]);

  async function createRecord(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/licenses', { licenseNo, companierId });
      setLicenseNo('');
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create license');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Licenses</h1>
        </div>
        <p className="muted">
          Companier-scoped masters. Orphan license-position rows are not repaired, deleted, or reassigned.
        </p>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'operations.license.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void createRecord(event)}>
            <h2>Create license</h2>
            <label>
              License no
              <input value={licenseNo} onChange={(event) => setLicenseNo(event.target.value)} />
            </label>
            <label>
              Companier ID
              <input value={companierId} onChange={(event) => setCompanierId(event.target.value)} />
            </label>
            <button type="submit">Create</button>
          </form>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>License no</th>
              <th>Companier</th>
              <th>Positions</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>
                  <Link href={`/app/licenses/${row.id}`}>{row.licenseNo}</Link>
                </td>
                <td>{row.companierId ?? '—'}</td>
                <td>{row.positions.length}</td>
                <td>{row.status ?? '—'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
