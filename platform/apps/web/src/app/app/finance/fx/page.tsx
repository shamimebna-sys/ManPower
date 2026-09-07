'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import type { FxRateRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function FxRatesPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [items, setItems] = useState<FxRateRecord[]>([]);
  const [error, setError] = useState('');
  const [rate, setRate] = useState('140');

  const load = useCallback(async () => {
    const response = await fetch(`${config.apiUrl}/api/v1/finance/fx-rates`, { credentials: 'include' });
    if (response.status === 403) {
      router.replace('/forbidden');
      return;
    }
    const json = (await response.json()) as {
      success: boolean;
      data?: { items: FxRateRecord[] };
      error?: { message: string };
    };
    if (!response.ok || !json.success || !json.data) {
      setError(json.error?.message ?? 'Unable to load FX rates');
      return;
    }
    setItems(json.data.items);
    setError('');
  }, [router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && !hasPermission(user, 'finance.read')) router.replace('/forbidden');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && hasPermission(user, 'finance.read')) void load();
  }, [loading, user, load]);

  async function createRate(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/finance/fx-rates', { fromCurrency: 'BDT', toCurrency: 'EUR', rate });
      setRate('140');
      await load();
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to enter rate');
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Administrative FX rates</h1>
        </div>
        <p className="muted">New-post R only. Historical journals keep payments.exchange_rate. euro_to_bdt_rates is not authority.</p>
        {error ? <p className="auth-error">{error}</p> : null}
        {hasPermission(user, 'finance.fx_rate.manage') ? (
          <form className="candidate-form" onSubmit={(event) => void createRate(event)}>
            <label>
              BDT to EUR rate
              <input value={rate} onChange={(event) => setRate(event.target.value)} required />
            </label>
            <button type="submit">Enter rate</button>
          </form>
        ) : null}
        <ul>
          {items.map((item) => (
            <li key={item.id}>
              {item.fromCurrency}/{item.toCurrency} {item.rate} at {item.effectiveAt}
            </li>
          ))}
        </ul>
      </main>
    </section>
  );
}