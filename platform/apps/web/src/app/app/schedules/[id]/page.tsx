'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import type { ClassScheduleRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function ScheduleDetailPage() {
  const router = useRouter();
  const params = useParams<{ id: string }>();
  const { user, loading } = useAuth();
  const [row, setRow] = useState<ClassScheduleRecord | null>(null);
  const [error, setError] = useState('');
  const [subject, setSubject] = useState('');
  const [status, setStatus] = useState('A');

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!user) return;
    void apiClient
      .get<ClassScheduleRecord>(`/api/v1/class-schedules/${params.id}`)
      .then((data) => {
        setRow(data);
        setSubject(data.subject ?? '');
        setStatus(data.status);
      })
      .catch((caught: unknown) => {
        if (caught instanceof ApiClientError && (caught.statusCode === 403 || caught.statusCode === 404)) {
          router.replace(caught.statusCode === 403 ? '/forbidden' : '/app/schedules');
          return;
        }
        setError(caught instanceof ApiClientError ? caught.message : 'Unable to load schedule');
      });
  }, [params.id, router, user]);

  async function save(event: React.FormEvent) {
    event.preventDefault();
    try {
      const updated = await apiClient.patch<ClassScheduleRecord>(`/api/v1/class-schedules/${params.id}`, {
        subject,
        status,
      });
      setRow(updated);
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to update schedule');
    }
  }

  if (loading || !user || !row) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <div className="page-head">
          <h1>{row.subject ?? 'Schedule'}</h1>
        </div>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'training.schedule.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void save(event)}>
            <label>
              Subject
              <input value={subject} onChange={(event) => setSubject(event.target.value)} />
            </label>
            <label>
              Status
              <select value={status} onChange={(event) => setStatus(event.target.value)}>
                <option value="A">Active</option>
                <option value="I">Inactive</option>
              </select>
            </label>
            <button type="submit">Save</button>
          </form>
        ) : (
          <dl>
            <dt>Week day</dt>
            <dd>{row.weekDay ?? '—'}</dd>
            <dt>Status</dt>
            <dd>{row.status}</dd>
          </dl>
        )}
      </main>
    </section>
  );
}
