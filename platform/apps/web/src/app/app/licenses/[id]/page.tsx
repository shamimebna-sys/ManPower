'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useParams, useRouter } from 'next/navigation';
import type { LicenseRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function LicenseDetailPage() {
  const router = useRouter();
  const params = useParams<{ id: string }>();
  const { user, loading } = useAuth();
  const [row, setRow] = useState<LicenseRecord | null>(null);
  const [error, setError] = useState('');
  const [licenseNo, setLicenseNo] = useState('');
  const [status, setStatus] = useState('');
  const [startDate, setStartDate] = useState('');
  const [expireDate, setExpireDate] = useState('');

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!user) return;
    if (!hasPermission(user, 'operations.license.read')) {
      router.replace('/forbidden');
      return;
    }
    void apiClient
      .get<LicenseRecord>(`/api/v1/licenses/${params.id}`)
      .then((data) => {
        setRow(data);
        setLicenseNo(data.licenseNo);
        setStatus(data.status ?? '');
        setStartDate(data.licenseStartDate ?? '');
        setExpireDate(data.licenseExpireDate ?? '');
      })
      .catch((caught: unknown) => {
        if (caught instanceof ApiClientError && (caught.statusCode === 403 || caught.statusCode === 404)) {
          router.replace(caught.statusCode === 403 ? '/forbidden' : '/app/licenses');
          return;
        }
        setError(caught instanceof ApiClientError ? caught.message : 'Unable to load license');
      });
  }, [params.id, router, user]);

  async function save(event: React.FormEvent) {
    event.preventDefault();
    try {
      const updated = await apiClient.patch<LicenseRecord>(`/api/v1/licenses/${params.id}`, {
        licenseNo,
        status,
        licenseStartDate: startDate,
        licenseExpireDate: expireDate,
      });
      setRow(updated);
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to update license');
    }
  }

  if (loading || !user || !row) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <div className="page-head">
          <h1>{row.licenseNo}</h1>
        </div>
        <p className="muted">
          <Link href="/app/licenses">Back to licenses</Link> · Companier {row.companierId ?? 'unbound / orphan'}
        </p>
        {error ? <p className="auth-error">{error}</p> : null}
        <dl>
          <dt>Positions</dt>
          <dd>{row.positions.length}</dd>
          <dt>File reference</dt>
          <dd>{row.licenseFileId ?? 'None'}</dd>
        </dl>
        {hasPermission(user, 'operations.license.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void save(event)}>
            <label>
              License no
              <input value={licenseNo} onChange={(event) => setLicenseNo(event.target.value)} />
            </label>
            <label>
              Status
              <input value={status} onChange={(event) => setStatus(event.target.value)} maxLength={5} />
            </label>
            <label>
              Start date
              <input value={startDate} onChange={(event) => setStartDate(event.target.value)} placeholder="YYYY-MM-DD" />
            </label>
            <label>
              Expire date
              <input value={expireDate} onChange={(event) => setExpireDate(event.target.value)} placeholder="YYYY-MM-DD" />
            </label>
            <button type="submit">Save</button>
          </form>
        ) : (
          <p className="muted">Read only</p>
        )}
      </main>
    </section>
  );
}
