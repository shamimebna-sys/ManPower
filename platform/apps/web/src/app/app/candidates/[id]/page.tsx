'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import type { CandidateRecord, LiveStatusBadgeRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { apiClient, ApiClientError } from '@/lib/api-client';
import { CandidateProfileSections } from '@/components/candidate-profile-sections';
import { hasAnyPermission, OVERSEAS_READ_KEYS } from '@/lib/permissions';

export default function CandidateDetailPage() {
  const params = useParams<{ id: string }>();
  const router = useRouter();
  const { user, loading } = useAuth();
  const [candidate, setCandidate] = useState<CandidateRecord | null>(null);
  const [badge, setBadge] = useState<LiveStatusBadgeRecord | null>(null);
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!user || !params.id) return;
    apiClient
      .get<CandidateRecord>(`/api/v1/candidates/${params.id}`)
      .then((record) => {
        setCandidate(record);
        if (hasAnyPermission(user, OVERSEAS_READ_KEYS)) {
          return apiClient.get<LiveStatusBadgeRecord>(`/api/v1/live-status/${params.id}`).then(setBadge);
        }
        return undefined;
      })
      .catch((caught: unknown) => {
        if (caught instanceof ApiClientError && caught.statusCode === 403) {
          router.replace('/forbidden');
          return;
        }
        setError(caught instanceof ApiClientError ? caught.message : 'Unable to load candidate');
      });
  }, [user, params.id, router]);

  async function save(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!candidate) return;
    const form = new FormData(event.currentTarget);
    setSubmitting(true);
    setError('');
    try {
      const updated = await apiClient.patch<CandidateRecord>(`/api/v1/candidates/${candidate.id}`, {
        name: String(form.get('name') ?? ''),
        email: String(form.get('email') ?? ''),
        mobile: String(form.get('mobile') ?? ''),
        passportNo: String(form.get('passportNo') ?? ''),
        nid: String(form.get('nid') ?? ''),
        nationality: String(form.get('nationality') ?? ''),
        gender: String(form.get('gender') ?? ''),
        remarks: String(form.get('remarks') ?? ''),
      });
      setCandidate(updated);
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to update candidate');
    } finally {
      setSubmitting(false);
    }
  }

  async function changeStatus(status: 'A' | 'P' | 'I') {
    if (!candidate) return;
    setSubmitting(true);
    try {
      const updated = await apiClient.patch<CandidateRecord>(
        `/api/v1/candidates/${candidate.id}/status`,
        { status }
      );
      setCandidate(updated);
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to change status');
    } finally {
      setSubmitting(false);
    }
  }

  if (loading || !user || !candidate) {
    return <p className="shell-loading">{error || 'Loading candidate…'}</p>;
  }

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <h1>{candidate.name ?? 'Candidate'}</h1>
        <p className="muted">
          Code {candidate.code ?? '—'} · Status {candidate.status}
        </p>
        {error ? <p className="auth-error">{error}</p> : null}
        <form className="candidate-form" onSubmit={save}>
          <label>Name<input name="name" defaultValue={candidate.name ?? ''} required /></label>
          <label>Email<input name="email" type="email" defaultValue={candidate.email ?? ''} required /></label>
          <label>Mobile<input name="mobile" defaultValue={candidate.mobile ?? ''} required /></label>
          <label>Passport<input name="passportNo" defaultValue={candidate.passportNo ?? ''} required /></label>
          <label>NID<input name="nid" defaultValue={candidate.nid ?? ''} /></label>
          <label>Nationality<input name="nationality" defaultValue={candidate.nationality ?? ''} /></label>
          <label>Gender<input name="gender" defaultValue={candidate.gender ?? ''} /></label>
          <label>Remarks<input name="remarks" defaultValue={candidate.remarks ?? ''} /></label>
          <button type="submit" disabled={submitting}>
            Save changes
          </button>
        </form>
        <div className="pager">
          <button type="button" disabled={submitting} onClick={() => void changeStatus('A')}>
            Set Active
          </button>
          <button type="button" disabled={submitting} onClick={() => void changeStatus('P')}>
            Set Pending
          </button>
          <button type="button" disabled={submitting} onClick={() => void changeStatus('I')}>
            Set Inactive
          </button>
        </div>
        <dl>
          <dt>Live status</dt>
          <dd>
            {badge ? `Step ${badge.stepNo}: ${badge.name}` : hasAnyPermission(user, OVERSEAS_READ_KEYS) ? '—' : 'No overseas read'}
          </dd>
          <dt>DOB</dt><dd>{candidate.dob ?? '—'}</dd>
          <dt>Agent ID</dt><dd>{candidate.agentId ?? '—'}</dd>
          <dt>Class group ID</dt><dd>{candidate.classGroupId ?? '—'}</dd>
          <dt>CV reference</dt><dd>{candidate.cvFileRef ? 'Private reference present' : 'None'}</dd>
          <dt>Photo reference</dt><dd>{candidate.fullPhotoFileRef ? 'Private reference present' : 'None'}</dd>
          <dt>Updated</dt><dd>{candidate.updatedAt}</dd>
        </dl>
        <CandidateProfileSections
          candidateId={candidate.id}
          onForbidden={() => router.replace('/forbidden')}
        />
      </main>
    </section>
  );
}
