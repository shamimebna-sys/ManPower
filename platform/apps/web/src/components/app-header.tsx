'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/auth-provider';

export function AppHeader() {
  const router = useRouter();
  const { logout } = useAuth();

  return (
    <header>
      <strong>
        <Link href="/app" style={{ color: 'inherit' }}>
          ManPower
        </Link>
      </strong>
      <nav className="app-nav">
        <Link href="/app">Home</Link>
        <Link href="/app/candidates">Candidates</Link>
        <button type="button" onClick={() => void logout().then(() => router.replace('/login'))}>
          Sign out
        </button>
      </nav>
    </header>
  );
}
