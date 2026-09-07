'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useParams, useRouter } from 'next/navigation';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

const RESOURCES = {
  medicals: { label: 'Medical', read: 'overseas.medical.read', manage: 'overseas.medical.manage' },
  'police-clearances': {
    label: 'Police Clearance',
    read: 'overseas.police_clearance.read',
    manage: 'overseas.police_clearance.manage',
  },
  arcs: { label: 'ARC', read: 'overseas.arc.read', manage: 'overseas.arc.manage' },
  'labour-contracts': {
    label: 'Labour Contract',
    read: 'overseas.labour_contract.read',
    manage: 'overseas.labour_contract.manage',
  },
  visas: { label: 'Visa', read: 'overseas.visa.read', manage: 'overseas.visa.manage' },
  flights: { label: 'Flight', read: 'overseas.flight.read', manage: 'overseas.flight.manage' },
} as const;

type ResourceKey = keyof typeof RESOURCES;

interface OverseasDetail {
  id: string;
  candidateId: string | null;
  status?: string | null;
  issueDate?: string | null;
  expireDate?: string | null;
  visaMpNo?: string | null;
  airlineceName?: string | null;
  arcNumber?: string | null;
  isLifetime?: string | null;
  documentFileId?: string | null;
  photoFileId?: string | null;
  arcFileId?: string | null;
  ticketFileId?: string | null;
  arrivalSealPageFileId?: string | null;
}

function isResourceKey(value: string): value is ResourceKey {
  return value in RESOURCES;
}

export default function OverseasDetailPage() {
  const router = useRouter();
  const params = useParams<{ resource: string; id: string }>();
  const { user, loading } = useAuth();
  const [row, setRow] = useState<OverseasDetail | null>(null);
  const [error, setError] = useState('');
  const [status, setStatus] = useState('');
  const [issueDate, setIssueDate] = useState('');
  const [expireDate, setExpireDate] = useState('');
  const [visaMpNo, setVisaMpNo] = useState('');
  const [airlineceName, setAirlineceName] = useState('');
  const [arcNumber, setArcNumber] = useState('');

  const resource = isResourceKey(params.resource) ? params.resource : null;
  const meta = resource ? RESOURCES[resource] : null;

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!user) return;
    if (!resource || !meta) {
      router.replace('/app/overseas');
      return;
    }
    if (!hasPermission(user, meta.read)) {
      router.replace('/forbidden');
      return;
    }
    void apiClient
      .get<OverseasDetail>(`/api/v1/overseas/${resource}/${params.id}`)
      .then((data) => {
        setRow(data);
        setStatus(data.status ?? '');
        setIssueDate(data.issueDate ?? '');
        setExpireDate(data.expireDate ?? '');
        setVisaMpNo(data.visaMpNo ?? '');
        setAirlineceName(data.airlineceName ?? '');
        setArcNumber(data.arcNumber ?? '');
      })
      .catch((caught: unknown) => {
        if (caught instanceof ApiClientError && (caught.statusCode === 403 || caught.statusCode === 404)) {
          router.replace(caught.statusCode === 403 ? '/forbidden' : '/app/overseas');
          return;
        }
        setError(caught instanceof ApiClientError ? caught.message : 'Unable to load record');
      });
  }, [meta, params.id, resource, router, user]);

  async function save(event: React.FormEvent) {
    event.preventDefault();
    if (!resource || !meta) return;
    try {
      const payload: Record<string, string> = {};
      if (resource === 'flights') {
        payload.airlineceName = airlineceName;
      } else {
        payload.status = status;
        payload.issueDate = issueDate;
        payload.expireDate = expireDate;
      }
      if (resource === 'visas') payload.visaMpNo = visaMpNo;
      if (resource === 'arcs') payload.arcNumber = arcNumber;
      const updated = await apiClient.patch<OverseasDetail>(`/api/v1/overseas/${resource}/${params.id}`, payload);
      setRow(updated);
    } catch (caught) {
      if (caught instanceof ApiClientError && caught.statusCode === 403) {
        router.replace('/forbidden');
        return;
      }
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to update record');
    }
  }

  if (loading || !user || !row || !meta) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main>
        <div className="page-head">
          <h1>{meta.label}</h1>
        </div>
        <p className="muted">
          <Link href="/app/overseas">Back to overseas</Link> · In-place update only. No hard delete.
        </p>
        {error ? <p className="auth-error">{error}</p> : null}
        <dl>
          <dt>ID</dt>
          <dd>{row.id}</dd>
          <dt>Candidate</dt>
          <dd>{row.candidateId ?? '—'}</dd>
          <dt>File reference</dt>
          <dd>{row.documentFileId ?? row.photoFileId ?? row.arcFileId ?? row.ticketFileId ?? 'None'}</dd>
        </dl>
        {hasPermission(user, meta.manage) ? (
          <form className="candidate-form" onSubmit={(event) => void save(event)}>
            {resource === 'flights' ? (
              <label>
                Airline name
                <input value={airlineceName} onChange={(event) => setAirlineceName(event.target.value)} />
              </label>
            ) : (
              <>
                <label>
                  Status
                  <input value={status} onChange={(event) => setStatus(event.target.value)} maxLength={5} />
                </label>
                <label>
                  Issue date
                  <input value={issueDate} onChange={(event) => setIssueDate(event.target.value)} placeholder="YYYY-MM-DD" />
                </label>
                <label>
                  Expire date
                  <input value={expireDate} onChange={(event) => setExpireDate(event.target.value)} placeholder="YYYY-MM-DD" />
                </label>
              </>
            )}
            {resource === 'visas' ? (
              <label>
                Visa MP no
                <input value={visaMpNo} onChange={(event) => setVisaMpNo(event.target.value)} />
              </label>
            ) : null}
            {resource === 'arcs' ? (
              <label>
                ARC number
                <input value={arcNumber} onChange={(event) => setArcNumber(event.target.value)} />
              </label>
            ) : null}
            <button type="submit">Save</button>
          </form>
        ) : (
          <p className="muted">Read only</p>
        )}
      </main>
    </section>
  );
}
