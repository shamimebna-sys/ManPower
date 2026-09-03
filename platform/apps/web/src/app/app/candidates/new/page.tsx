'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import type { CandidateRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function NewCandidatePage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setSubmitting(true);
    setError('');
    try {
      const created = await apiClient.post<CandidateRecord>('/api/v1/candidates', {
        name: String(form.get('name') ?? ''),
        email: String(form.get('email') ?? ''),
        mobile: String(form.get('mobile') ?? ''),
        passportNo: String(form.get('passportNo') ?? ''),
        agentId: String(form.get('agentId') ?? ''),
        classGroupId: String(form.get('classGroupId') ?? ''),
        nid: String(form.get('nid') ?? ''),
        dob: String(form.get('dob') ?? ''),
        nationality: String(form.get('nationality') ?? ''),
        gender: String(form.get('gender') ?? ''),
        status: String(form.get('status') ?? 'A'),
      });
      router.replace(`/app/candidates/${created.id}`);
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create candidate');
    } finally {
      setSubmitting(false);
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <h1>Create candidate</h1>
        <p className="muted">Required fields match confirmed legacy create rules. Agent and class group IDs are opaque until those modules exist.</p>
        {error ? <p className="auth-error">{error}</p> : null}
        <form className="candidate-form" onSubmit={submit}>
          <label>Name<input name="name" required maxLength={100} /></label>
          <label>Email<input name="email" type="email" required maxLength={250} /></label>
          <label>Mobile<input name="mobile" required maxLength={250} /></label>
          <label>Passport number<input name="passportNo" required maxLength={250} /></label>
          <label>Agent legacy ID<input name="agentId" required /></label>
          <label>Class group legacy ID<input name="classGroupId" required /></label>
          <label>NID<input name="nid" maxLength={250} /></label>
          <label>Date of birth<input name="dob" type="date" /></label>
          <label>Nationality<input name="nationality" maxLength={250} /></label>
          <label>Gender<input name="gender" maxLength={20} /></label>
          <label>
            Status
            <select name="status" defaultValue="A">
              <option value="A">Active (A)</option>
              <option value="P">Pending (P)</option>
              <option value="I">Inactive (I)</option>
            </select>
          </label>
          <button type="submit" disabled={submitting}>
            {submitting ? 'Saving…' : 'Create candidate'}
          </button>
        </form>
      </main>
    </section>
  );
}
