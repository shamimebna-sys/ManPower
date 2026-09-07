'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ClassScheduleListResult, ClassScheduleRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function SchedulesPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<ClassScheduleRecord[]>([]);
  const [error, setError] = useState('');
  const [teacherId, setTeacherId] = useState('');
  const [classGroupId, setClassGroupId] = useState('');
  const [subject, setSubject] = useState('');
  const [weekDay, setWeekDay] = useState('');

  const load = useCallback(async () => {
    const response = await fetch(`${config.apiUrl}/api/v1/class-schedules?limit=50`, { credentials: 'include' });
    const json = (await response.json()) as {
      success: boolean;
      data?: ClassScheduleListResult;
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load schedules');
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

  async function createSchedule(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/class-schedules', { teacherId, classGroupId, subject, weekDay });
      setTeacherId('');
      setClassGroupId('');
      setSubject('');
      setWeekDay('');
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create schedule');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Class schedules</h1>
        </div>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'training.schedule.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void createSchedule(event)}>
            <h2>Create schedule</h2>
            <label>
              Teacher ID
              <input value={teacherId} onChange={(event) => setTeacherId(event.target.value)} />
            </label>
            <label>
              Class group ID
              <input value={classGroupId} onChange={(event) => setClassGroupId(event.target.value)} />
            </label>
            <label>
              Subject
              <input value={subject} onChange={(event) => setSubject(event.target.value)} />
            </label>
            <label>
              Week day
              <input value={weekDay} onChange={(event) => setWeekDay(event.target.value)} />
            </label>
            <button type="submit">Create</button>
          </form>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Subject</th>
              <th>Week day</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>
                  <Link href={`/app/schedules/${row.id}`}>{row.subject ?? row.id}</Link>
                </td>
                <td>{row.weekDay ?? '—'}</td>
                <td>{row.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
