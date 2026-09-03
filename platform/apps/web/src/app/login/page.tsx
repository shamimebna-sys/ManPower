'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/auth-provider';
import { ApiClientError } from '@/lib/api-client';

export default function LoginPage() {
  const router = useRouter();
  const { login } = useAuth();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');
    setSubmitting(true);
    try {
      await login(identifier, password);
      router.replace('/app');
    } catch (caught) {
      setError(caught instanceof ApiClientError ? caught.message : 'Unable to sign in');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <section className="auth-page">
      <form className="auth-card" onSubmit={submit}>
        <h1>ManPower Management System</h1>
        <p>Sign in with your email address or username.</p>
        {error ? <div role="alert" className="auth-error">{error}</div> : null}
        <label>
          Email or username
          <input
            autoComplete="username"
            value={identifier}
            onChange={(event) => setIdentifier(event.target.value)}
            required
            maxLength={320}
          />
        </label>
        <label>
          Password
          <input
            type="password"
            autoComplete="current-password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            required
            maxLength={128}
          />
        </label>
        <button type="submit" disabled={submitting}>
          {submitting ? 'Signing in…' : 'Sign in'}
        </button>
      </form>
    </section>
  );
}
