'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import type {
  EmployerCandidateListResult,
  EmployerCandidatePurpose,
  EmployerCandidateRecord,
} from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { apiClient, ApiClientError } from '@/lib/api-client';
import { hasPermission } from '@/lib/permissions';

const PURPOSES: EmployerCandidatePurpose[] = ['FAVORITE', 'RESERVE', 'SELECTED'];

export default function EmployerCandidatesPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<EmployerCandidateRecord[]>([]);
  const [error, setError] = useState('');
  const [candidateId, setCandidateId] = useState('');
  const [employerId, setEmployerId] = useState('');
  const [purpose, setPurpose] = useState<EmployerCandidatePurpose>('FAVORITE');

  const load = useCallback(async () => {
    try {
      const data = await apiClient.get<EmployerCandidateListResult>('/api/v1/employer-candidates?limit=50');
      setItems(data.items);
      setError('');
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to load assignments');
    }
  }, [router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user) void load();
  }, [loading, user, load]);

  async function create(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/employer-candidates', {
        candidateId,
        purpose,
        ...(employerId ? { employerId } : {}),
      });
      setCandidateId('');
      await load();
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create assignment');
    }
  }

  async function setStatus(id: string, status: 'A' | 'I') {
    try {
      await apiClient.patch(`/api/v1/employer-candidates/${id}/status`, { status });
      await load();
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to change status');
    }
  }

  async function setPurposeFor(id: string, next: EmployerCandidatePurpose) {
    try {
      await apiClient.patch(`/api/v1/employer-candidates/${id}`, { purpose: next });
      await load();
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to change purpose');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Employer assignments</h1>
        </div>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'employer_candidate.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void create(event)}>
            <label>
              Candidate UUID
              <input value={candidateId} onChange={(event) => setCandidateId(event.target.value)} required />
            </label>
            <label>
              Employer UUID
              <input
                value={employerId}
                onChange={(event) => setEmployerId(event.target.value)}
                placeholder="Required for staff; bound automatically for employers"
              />
            </label>
            <label>
              Purpose
              <select
                value={purpose}
                onChange={(event) => setPurpose(event.target.value as EmployerCandidatePurpose)}
              >
                {PURPOSES.map((item) => (
                  <option key={item} value={item}>
                    {item}
                  </option>
                ))}
              </select>
            </label>
            <button type="submit">Assign</button>
          </form>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Employer</th>
              <th>Purpose</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>{row.candidateId}</td>
                <td>{row.employerId}</td>
                <td>
                  {hasPermission(user, 'employer_candidate.manage') ? (
                    <select
                      value={row.purpose}
                      onChange={(event) =>
                        void setPurposeFor(row.id, event.target.value as EmployerCandidatePurpose)
                      }
                    >
                      {PURPOSES.map((item) => (
                        <option key={item} value={item}>
                          {item}
                        </option>
                      ))}
                    </select>
                  ) : (
                    row.purpose
                  )}
                </td>
                <td>{row.status}</td>
                <td>
                  {hasPermission(user, 'employer_candidate.manage') ? (
                    row.status === 'A' ? (
                      <button type="button" onClick={() => void setStatus(row.id, 'I')}>
                        Inactivate
                      </button>
                    ) : (
                      <button type="button" onClick={() => void setStatus(row.id, 'A')}>
                        Activate
                      </button>
                    )
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
