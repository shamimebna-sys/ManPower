'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { PartnerListResult, PartnerSummary, PartnerType } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

const TYPES: PartnerType[] = ['agent', 'sub_agent', 'agencier', 'companier'];

export default function PartnersPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [type, setType] = useState<PartnerType>('agent');
  const [items, setItems] = useState<PartnerSummary[]>([]);
  const [q, setQ] = useState('');
  const [error, setError] = useState('');
  const [name, setName] = useState('');
  const [code, setCode] = useState('');
  const [parentAgentId, setParentAgentId] = useState('');

  const load = useCallback(async () => {
    const params = new URLSearchParams({ limit: '20' });
    if (q) params.set('q', q);
    const response = await fetch(`${config.apiUrl}/api/v1/partners/${type}?${params.toString()}`, {
      credentials: 'include',
    });
    const json = (await response.json()) as {
      success: boolean;
      data?: PartnerListResult;
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load partners');
      return;
    }
    setItems(json.data.items);
    setError('');
  }, [q, router, type]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user) void load();
  }, [loading, user, load]);

  async function createPartner(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post(`/api/v1/partners/${type}`, {
        name,
        code,
        ...(type === 'sub_agent' ? { agentId: parentAgentId } : {}),
      });
      setName('');
      setCode('');
      setParentAgentId('');
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create partner');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Recruitment partners</h1>
        </div>
        <div className="tab-row">
          {TYPES.map((key) => (
            <button
              key={key}
              type="button"
              className={`tab ${type === key ? 'active' : ''}`}
              onClick={() => setType(key)}
            >
              {key}
            </button>
          ))}
        </div>
        <form
          className="filter-row"
          onSubmit={(event) => {
            event.preventDefault();
          }}
        >
          <input
            placeholder="Search name, code, email, mobile"
            value={q}
            onChange={(event) => setQ(event.target.value)}
          />
        </form>
        {hasPermission(user, 'partners.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void createPartner(event)}>
            <h2>Create {type}</h2>
            <label>
              Name
              <input value={name} onChange={(event) => setName(event.target.value)} />
            </label>
            <label>
              Code
              <input value={code} onChange={(event) => setCode(event.target.value)} />
            </label>
            {type === 'sub_agent' ? (
              <label>
                Parent agent UUID
                <input value={parentAgentId} onChange={(event) => setParentAgentId(event.target.value)} required />
              </label>
            ) : null}
            <button type="submit">Create</button>
          </form>
        ) : null}
        {error ? <p className="auth-error">{error}</p> : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((partner) => (
              <tr key={partner.id}>
                <td>
                  <Link href={`/app/partners/${partner.type}/${partner.id}`}>{partner.code ?? '—'}</Link>
                </td>
                <td>{partner.name ?? '—'}</td>
                <td>{partner.email ?? '—'}</td>
                <td>{partner.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
