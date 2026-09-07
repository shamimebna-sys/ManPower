'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ClassGroupListResult, ClassGroupRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function ClassGroupsPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<ClassGroupRecord[]>([]);
  const [error, setError] = useState('');
  const [name, setName] = useState('');
  const [code, setCode] = useState('');

  const load = useCallback(async () => {
    const response = await fetch(`${config.apiUrl}/api/v1/class-groups?limit=50`, { credentials: 'include' });
    const json = (await response.json()) as {
      success: boolean;
      data?: ClassGroupListResult;
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load class groups');
      return;
    }
    setItems(json.data.items);
    setError('');
  }, [router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user) void load();
  }, [loading, user, load]);

  async function createGroup(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/class-groups', { name, code });
      setName('');
      setCode('');
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create class group');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Class groups</h1>
        </div>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'training.class_group.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void createGroup(event)}>
            <h2>Create class group</h2>
            <label>
              Name
              <input value={name} onChange={(event) => setName(event.target.value)} />
            </label>
            <label>
              Code
              <input value={code} onChange={(event) => setCode(event.target.value)} />
            </label>
            <button type="submit">Create</button>
          </form>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>
                  <Link href={`/app/class-groups/${row.id}`}>{row.code ?? row.id}</Link>
                </td>
                <td>{row.name ?? '—'}</td>
                <td>{row.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
