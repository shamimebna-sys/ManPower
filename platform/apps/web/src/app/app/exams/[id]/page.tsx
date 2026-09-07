'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useParams, useRouter } from 'next/navigation';
import type { ExamPublishResult, ExamRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function ExamDetailPage() {
  const router = useRouter();
  const params = useParams<{ id: string }>();
  const { user, loading } = useAuth();
  const [row, setRow] = useState<ExamRecord | null>(null);
  const [error, setError] = useState('');
  const [name, setName] = useState('');
  const [status, setStatus] = useState('A');
  const [publishMessage, setPublishMessage] = useState('');

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!user) return;
    void apiClient
      .get<ExamRecord>(`/api/v1/exams/${params.id}`)
      .then((data) => {
        setRow(data);
        setName(data.name ?? '');
        setStatus(data.status);
      })
      .catch((caught: unknown) => {
        if (caught instanceof ApiClientError && (caught.statusCode === 403 || caught.statusCode === 404)) {
          router.replace(caught.statusCode === 403 ? '/forbidden' : '/app/exams');
          return;
        }
        setError(caught instanceof ApiClientError ? caught.message : 'Unable to load exam');
      });
  }, [params.id, router, user]);

  async function save(event: React.FormEvent) {
    event.preventDefault();
    try {
      const updated = await apiClient.patch<ExamRecord>(`/api/v1/exams/${params.id}`, { name, status });
      setRow(updated);
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to update exam');
    }
  }

  async function publish() {
    try {
      const result = await apiClient.post<ExamPublishResult>(`/api/v1/exams/${params.id}/publish`, {});
      setPublishMessage(`Created ${result.created} result rows (${result.existing} already present).`);
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to publish exam');
    }
  }

  if (loading || !user || !row) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <div className="page-head">
          <h1>{row.name ?? 'Exam'}</h1>
        </div>
        {error ? <p className="auth-error">{error}</p> : null}
        {publishMessage ? <p className="muted">{publishMessage}</p> : null}
        {hasPermission(user, 'training.exam.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void save(event)}>
            <label>
              Name
              <input value={name} onChange={(event) => setName(event.target.value)} />
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
            <dt>Date</dt>
            <dd>{row.examDate ?? '—'}</dd>
            <dt>Groups</dt>
            <dd>{row.classGroupIds.join(', ') || '—'}</dd>
          </dl>
        )}
        {hasPermission(user, 'training.exam_result.manage') ? (
          <div className="pager">
            <button type="button" onClick={() => void publish()}>
              Publish / open sheet
            </button>
            <Link className="button-link" href={`/app/exam-results?examId=${row.id}`}>
              Open results
            </Link>
          </div>
        ) : null}
      </main>
    </section>
  );
}
