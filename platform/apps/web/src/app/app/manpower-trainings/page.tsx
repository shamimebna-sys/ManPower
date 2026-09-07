'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import type { ManpowerTrainingListResult, ManpowerTrainingRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function ManpowerTrainingsPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<ManpowerTrainingRecord[]>([]);
  const [error, setError] = useState('');
  const [candidateId, setCandidateId] = useState('');
  const [status, setStatus] = useState('A');

  const load = useCallback(async () => {
    const response = await fetch(`${config.apiUrl}/api/v1/manpower-trainings?limit=50`, {
      credentials: 'include',
    });
    const json = (await response.json()) as {
      success: boolean;
      data?: ManpowerTrainingListResult;
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load manpower training');
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

  async function createRecord(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/manpower-trainings', { candidateId, status });
      setCandidateId('');
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create manpower training');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Manpower training</h1>
        </div>
        <p className="muted">
          BMET / process evidence. Separate from candidate profile training and from live-status step 9
          (manpower payment).
        </p>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'training.manpower.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void createRecord(event)}>
            <h2>Create manpower training</h2>
            <label>
              Candidate ID
              <input value={candidateId} onChange={(event) => setCandidateId(event.target.value)} />
            </label>
            <label>
              Status
              <select value={status} onChange={(event) => setStatus(event.target.value)}>
                <option value="A">Active</option>
                <option value="I">Inactive</option>
              </select>
            </label>
            <button type="submit">Create</button>
          </form>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Start</th>
              <th>End</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>{row.candidateId}</td>
                <td>{row.trainingStartDate ?? '—'}</td>
                <td>{row.trainingEndDate ?? '—'}</td>
                <td>{row.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
