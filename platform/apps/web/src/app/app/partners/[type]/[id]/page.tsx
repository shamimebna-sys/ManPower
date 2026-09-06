'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import type { PartnerRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function PartnerDetailPage() {
  const router = useRouter();
  const params = useParams<{ type: string; id: string }>();
  const { user, loading } = useAuth();
  const [partner, setPartner] = useState<PartnerRecord | null>(null);
  const [error, setError] = useState('');
  const [name, setName] = useState('');
  const [status, setStatus] = useState('A');

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (loading || !user) return;
    void apiClient
      .get<PartnerRecord>(`/api/v1/partners/${params.type}/${params.id}`)
      .then((row) => {
        setPartner(row);
        setName(row.name ?? '');
        setStatus(row.status);
      })
      .catch((caught) => {
        if (caught instanceof ApiClientError && caught.statusCode === 403) {
          router.replace('/forbidden');
          return;
        }
        setError(caught instanceof ApiClientError ? caught.message : 'Unable to load partner');
      });
  }, [loading, params.id, params.type, router, user]);

  async function save(event: React.FormEvent) {
    event.preventDefault();
    try {
      const updated = await apiClient.patch<PartnerRecord>(
        `/api/v1/partners/${params.type}/${params.id}`,
        { name, status }
      );
      setPartner(updated);
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to update partner');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <h1>{partner?.name ?? 'Partner'}</h1>
        <p className="muted">{params.type}</p>
        {error ? <p className="auth-error">{error}</p> : null}
        {partner ? (
          <form className="candidate-form" onSubmit={(event) => void save(event)}>
            <label>
              Name
              <input value={name} onChange={(event) => setName(event.target.value)} />
            </label>
            <label>
              Status
              <select value={status} onChange={(event) => setStatus(event.target.value)}>
                <option value="A">Active (A)</option>
                <option value="I">Inactive (I)</option>
              </select>
            </label>
            <p>Code: {partner.code ?? '—'}</p>
            <p>Email: {partner.email ?? '—'}</p>
            <button type="submit">Save</button>
          </form>
        ) : null}
      </main>
    </section>
  );
}
