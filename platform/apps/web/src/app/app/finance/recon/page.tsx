'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import type { QuarantineRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function FinanceReconPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<QuarantineRecord[]>([]);
  const [error, setError] = useState('');
  const [runId, setRunId] = useState('run-1');
  const [result, setResult] = useState('');

  const load = useCallback(async () => {
    const response = await fetch(`${config.apiUrl}/api/v1/finance/quarantine?limit=50`, { credentials: 'include' });
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    const json = (await response.json()) as {
      success: boolean;
      data?: { items: QuarantineRecord[] };
      error?: { message: string };
    };
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load quarantine');
      return;
    }
    setItems(json.data.items);
    setError('');
  }, [router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && !hasPermission(user, 'finance.reconciliation.read')) router.replace('/forbidden');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && hasPermission(user, 'finance.reconciliation.read')) void load();
  }, [loading, user, load]);

  async function runRecon(event: React.FormEvent) {
    event.preventDefault();
    try {
      const data = await apiClient.post<{ passed: boolean; status: string }>('/api/v1/finance/reconciliation', {
        migrationRunId: runId,
      });
      setResult(`${data.status} passed=${String(data.passed)}`);
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to reconcile');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Finance reconciliation</h1>
        </div>
        <p className="muted">Quarantine retains source payload. No silent discard. Production import is not this screen.</p>
        {error ? <p className="auth-error">{error}</p> : null}
        {result ? <p>{result}</p> : null}
        <form className="candidate-form" onSubmit={(event) => void runRecon(event)}>
          <label>
            Migration run
            <input value={runId} onChange={(event) => setRunId(event.target.value)} />
          </label>
          <button type="submit">Run gates</button>
        </form>
        <ul>
          {items.map((item) => (
            <li key={item.id}>
              {item.reason} {item.sourceTable}#{item.sourcePk}
            </li>
          ))}
        </ul>
      </main>
    </section>
  );
}