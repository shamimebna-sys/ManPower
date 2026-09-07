'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { LiveStatusBadgeRecord, OverseasListResult } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission, OVERSEAS_READ_KEYS } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

const RESOURCES = [
  { key: 'medicals', label: 'Medical', read: 'overseas.medical.read', manage: 'overseas.medical.manage' },
  {
    key: 'police-clearances',
    label: 'Police Clearance',
    read: 'overseas.police_clearance.read',
    manage: 'overseas.police_clearance.manage',
  },
  { key: 'arcs', label: 'ARC', read: 'overseas.arc.read', manage: 'overseas.arc.manage' },
  {
    key: 'labour-contracts',
    label: 'Labour Contract',
    read: 'overseas.labour_contract.read',
    manage: 'overseas.labour_contract.manage',
  },
  { key: 'visas', label: 'Visa', read: 'overseas.visa.read', manage: 'overseas.visa.manage' },
  { key: 'flights', label: 'Flight', read: 'overseas.flight.read', manage: 'overseas.flight.manage' },
] as const;

type ResourceKey = (typeof RESOURCES)[number]['key'];

export default function OverseasPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const visible = RESOURCES.filter((row) => hasPermission(user, row.read));
  const visibleKeySet = visible.map((row) => row.key).join('|');
  const [resource, setResource] = useState<ResourceKey>('medicals');
  const [items, setItems] = useState<Array<{ id: string; candidateId: string | null; status?: string | null }>>([]);
  const [error, setError] = useState('');
  const [candidateId, setCandidateId] = useState('');
  const [badge, setBadge] = useState<LiveStatusBadgeRecord | null>(null);

  const current = RESOURCES.find((row) => row.key === resource) ?? RESOURCES[0];

  const load = useCallback(async () => {
    if (!current) return;
    const response = await fetch(`${config.apiUrl}/api/v1/overseas/${current.key}?limit=50`, {
      credentials: 'include',
    });
    const json = (await response.json()) as {
      success: boolean;
      data?: OverseasListResult<{ id: string; candidateId: string | null; status?: string | null }>;
      error?: { message: string };
    };
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load overseas records');
      return;
    }
    setItems(json.data.items);
    setError('');
  }, [current, router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && visible.length === 0) router.replace('/forbidden');
  }, [loading, user, visible.length, router]);

  useEffect(() => {
    if (!user || !visibleKeySet) return;
    const keys = visibleKeySet.split('|') as ResourceKey[];
    if (!keys.includes(resource) && keys[0]) {
      setResource(keys[0]);
    }
  }, [user, resource, visibleKeySet]);

  useEffect(() => {
    if (!loading && user && visibleKeySet.split('|').includes(resource)) void load();
  }, [loading, user, resource, load, visibleKeySet]);

  async function createRecord(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post(`/api/v1/overseas/${current.key}`, { candidateId });
      setCandidateId('');
      await load();
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create record');
    }
  }

  async function loadBadge(event: React.FormEvent) {
    event.preventDefault();
    try {
      const data = await apiClient.get<LiveStatusBadgeRecord>(`/api/v1/live-status/${candidateId}`);
      setBadge(data);
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to load live status');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Overseas processing</h1>
        </div>
        <p className="muted">
          Medical and ARC are independent. The compatibility badge skips Step 5 and does not write candidate
          status. Agent labour-contract access is a target authorization decision, not legacy parity.
        </p>
        {error ? <p className="auth-error">{error}</p> : null}
        {OVERSEAS_READ_KEYS.some((key) => hasPermission(user, key)) ? (
          <form className="candidate-form" onSubmit={(event) => void loadBadge(event)}>
            <h2>Live-status badge</h2>
            <label>
              Candidate ID
              <input value={candidateId} onChange={(event) => setCandidateId(event.target.value)} />
            </label>
            <button type="submit">Read badge</button>
            {badge ? (
              <p>
                Step {badge.stepNo}: {badge.name}
                {badge.extraText ? ` (${badge.extraText})` : ''}
              </p>
            ) : null}
          </form>
        ) : null}
        <label>
          Resource
          <select value={resource} onChange={(event) => setResource(event.target.value as ResourceKey)}>
            {visible.map((row) => (
              <option key={row.key} value={row.key}>
                {row.label}
              </option>
            ))}
          </select>
        </label>
        {hasPermission(user, current.manage) ? (
          <form className="candidate-form" onSubmit={(event) => void createRecord(event)}>
            <h2>Create {current.label}</h2>
            <label>
              Candidate ID
              <input value={candidateId} onChange={(event) => setCandidateId(event.target.value)} />
            </label>
            <button type="submit">Create</button>
          </form>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Candidate</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.id}>
                <td>
                  <Link href={`/app/overseas/${resource}/${row.id}`}>{row.id}</Link>
                </td>
                <td>{row.candidateId ?? '—'}</td>
                <td>{row.status ?? '—'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </main>
    </section>
  );
}
