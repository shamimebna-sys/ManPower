'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ExamListResult, ExamRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function ExamsPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<ExamRecord[]>([]);
  const [error, setError] = useState('');
  const [name, setName] = useState('');
  const [classGroupIds, setClassGroupIds] = useState('');

  const load = useCallback(async () => {
    const response = await fetch(`${config.apiUrl}/api/v1/exams?limit=50`, { credentials: 'include' });
    const json = (await response.json()) as { success: boolean; data?: ExamListResult; error?: { message: string } };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load exams');
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

  async function createExam(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/exams', {
        name,
        classGroupIds: classGroupIds
          .split(',')
          .map((value) => value.trim())
          .filter(Boolean),
      });
      setName('');
      setClassGroupIds('');
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create exam');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Exams</h1>
        </div>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'training.exam.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void createExam(event)}>
            <h2>Create exam</h2>
            <label>
              Name
              <input value={name} onChange={(event) => setName(event.target.value)} />
            </label>
            <label>
              Class group IDs
              <input
                value={classGroupIds}
                onChange={(event) => setClassGroupIds(event.target.value)}
                placeholder="Comma-separated UUIDs"
              />
            </label>
            <button type="submit">Create</button>
          </form>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Date</th>
              <th>Groups</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>
                  <Link href={`/app/exams/${row.id}`}>{row.name ?? row.id}</Link>
                </td>
                <td>{row.examDate ?? '—'}</td>
                <td>{row.classGroupIds.length}</td>
                <td>{row.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
