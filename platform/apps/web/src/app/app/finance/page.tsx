'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import type { PaymentRequestListResult, PaymentRequestRecord, WalletListResult, WalletRecord } from '@manpower/shared';
import { useAuth } from '@/components/auth-provider';
import { AppHeader } from '@/components/app-header';
import { config } from '@/lib/config';
import { hasPermission } from '@/lib/permissions';
import { apiClient, ApiClientError } from '@/lib/api-client';

export default function FinanceWalletsPage() {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [wallets, setWallets] = useState<WalletRecord[]>([]);
  const [requests, setRequests] = useState<PaymentRequestRecord[]>([]);
  const [error, setError] = useState('');
  const [candidateId, setCandidateId] = useState('');
  const [amount, setAmount] = useState('6200');
  const [billTitle, setBillTitle] = useState('Manpower Fee');

  const load = useCallback(async () => {
    const [walletRes, requestRes] = await Promise.all([
      fetch(`${config.apiUrl}/api/v1/finance/wallets`, { credentials: 'include' }),
      fetch(`${config.apiUrl}/api/v1/finance/payment-requests?limit=50`, { credentials: 'include' }),
    ]);
    if (walletRes.status === 403 || requestRes.status === 403) {
      router.replace('/forbidden');
      return;
    }
    const walletJson = (await walletRes.json()) as { success: boolean; data?: WalletListResult; error?: { message: string } };
    const requestJson = (await requestRes.json()) as {
      success: boolean;
      data?: PaymentRequestListResult;
      error?: { message: string };
    };
    if (!walletRes.ok || !walletJson.success || !walletJson.data) {
      setError(walletJson.error?.message ?? 'Unable to load wallets');
      return;
    }
    setWallets(walletJson.data.items);
    setRequests(requestJson.data?.items ?? []);
    setError('');
  }, [router]);

  useEffect(() => {
    if (!loading && !user) router.replace('/login');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && !hasPermission(user, 'finance.wallet.read')) router.replace('/forbidden');
  }, [loading, user, router]);

  useEffect(() => {
    if (!loading && user && hasPermission(user, 'finance.wallet.read')) void load();
  }, [loading, user, load]);

  async function ensureWallet(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/finance/wallets/ensure', {});
      await load();
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to ensure wallet');
    }
  }

  async function createRequest(event: React.FormEvent) {
    event.preventDefault();
    try {
      await apiClient.post('/api/v1/finance/payment-requests', { candidateId, amount, billTitle });
      setCandidateId('');
      await load();
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to create request');
    }
  }

  async function act(id: string, action: 'approve' | 'reject') {
    try {
      await apiClient.post(`/api/v1/finance/payment-requests/${id}/${action}`, {});
      await load();
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : `Unable to ${action}`);
    }
  }

  if (loading || !user) return <p className="shell-loading">Loading your session…</p>;

  return (
    <section className="app-shell">
      <AppHeader />
      <main className="wide">
        <div className="page-head">
          <h1>Finance wallets</h1>
        </div>
        <p className="muted">Ledger-derived EUR available balance. Cache columns are not authority.</p>
        {error ? <p className="auth-error">{error}</p> : null}
        <form className="candidate-form" onSubmit={(event) => void ensureWallet(event)}>
          <button type="submit">Ensure my wallet</button>
        </form>
        <ul>
          {wallets.map((wallet) => (
            <li key={wallet.id}>
              {wallet.ownerType} {wallet.availableEur} EUR
            </li>
          ))}
        </ul>
        {hasPermission(user, 'finance.payment_request.create') ? (
          <form className="candidate-form" onSubmit={(event) => void createRequest(event)}>
            <h2>Create payment request</h2>
            <label>
              Candidate id
              <input value={candidateId} onChange={(event) => setCandidateId(event.target.value)} required />
            </label>
            <label>
              Amount EUR
              <input value={amount} onChange={(event) => setAmount(event.target.value)} required />
            </label>
            <label>
              Bill title
              <input value={billTitle} onChange={(event) => setBillTitle(event.target.value)} required />
            </label>
            <button type="submit">Create pending request</button>
          </form>
        ) : null}
        <h2>Payment requests</h2>
        <ul>
          {requests.map((row) => (
            <li key={row.id}>
              {row.billTitle} {row.amount} {row.status}
              {row.status === 'P' && hasPermission(user, 'finance.payment_request.approve') ? (
                <button type="button" onClick={() => void act(row.id, 'approve')}>
                  Approve
                </button>
              ) : null}
              {row.status === 'P' && hasPermission(user, 'finance.payment_request.reject') ? (
                <button type="button" onClick={() => void act(row.id, 'reject')}>
                  Reject
                </button>
              ) : null}
            </li>
          ))}
        </ul>
      </main>
    </section>
  );
}