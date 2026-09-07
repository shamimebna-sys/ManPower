'use client';

import { Suspense, useCallback, useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import type { ExamResultListResult, ExamResultRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function ExamResultsPage() {
  return (
    <Suspense fallback={<p className="shell-loading">Loading your session…</p>}>
      <ExamResultsPageInner />
    </Suspense>
  );
}

function ExamResultsPageInner() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const examId = searchParams.get('examId') ?? '';
  const { user, loading } = useAuth();
  const [items, setItems] = useState<ExamResultRecord[]>([]);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    const params = new URLSearchParams({ limit: '50' });
    if (examId) params.set('examId', examId);
    const response = await fetch(`${config.apiUrl}/api/v1/exam-results?${params.toString()}`, {
      credentials: 'include',
    });
    const json = (await response.json()) as {
      success: boolean;
      data?: ExamResultListResult;
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load exam results');
      return;
    }
    setItems(json.data.items);
    setError('');
  }, [examId, router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user) void load();
  }, [loading, user, load]);

  async function grade(id: string, result: 'PASS' | 'FAIL' | null, classGroupId?: string) {
    try {
      await apiClient.patch(`/api/v1/exam-results/${id}`, {
        result,
        ...(classGroupId ? { classGroupId } : {}),
      });
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to update result');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Exam results</h1>
        </div>
        <p className="muted">PASS and FAIL are manual. Marks do not compute a formula. Score 0 is a valid mark.</p>
        {error ? <p className="auth-error">{error}</p> : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Abroad</th>
              <th>Local</th>
              <th>BL</th>
              <th>Skill</th>
              <th>English</th>
              <th>Result</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>{row.candidateId}</td>
                <td>{row.abroadEx ?? '—'}</td>
                <td>{row.localEx ?? '—'}</td>
                <td>{row.bl ?? '—'}</td>
                <td>{row.skill ?? '—'}</td>
                <td>{row.english ?? '—'}</td>
                <td>{row.result ?? 'Ungraded'}</td>
                <td>
                  {hasPermission(user, 'training.exam_result.manage') ? (
                    <>
                      <button type="button" onClick={() => void grade(row.id, 'PASS', row.classGroupId ?? undefined)}>
                        PASS
                      </button>{' '}
                      <button type="button" onClick={() => void grade(row.id, 'FAIL')}>
                        FAIL
                      </button>
                    </>
                  ) : null}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
