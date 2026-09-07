'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/auth-provider';
import { hasAnyPermission, hasPermission, OVERSEAS_READ_KEYS } from '@/lib/permissions';

export function AppHeader() {
  const router = useRouter();
  const { logout, user } = useAuth();

  return (
    <header>
      <strong>
        <Link href="/app" style={{ color: 'inherit' }}>
          ManPower
        </Link>
      </strong>
      <nav className="app-nav">
        <Link href="/app">Home</Link>
        {hasPermission(user, 'candidate.read') ? <Link href="/app/candidates">Candidates</Link> : null}
        {hasPermission(user, 'partners.read') ? <Link href="/app/partners">Partners</Link> : null}
        {hasPermission(user, 'employer_candidate.read') ? (
          <Link href="/app/employer-candidates">Assignments</Link>
        ) : null}
        {hasPermission(user, 'training.teacher.read') ? <Link href="/app/teachers">Teachers</Link> : null}
        {hasPermission(user, 'training.class_group.read') ? (
          <Link href="/app/class-groups">Class groups</Link>
        ) : null}
        {hasPermission(user, 'training.schedule.read') ? <Link href="/app/schedules">Schedules</Link> : null}
        {hasPermission(user, 'training.exam.read') ? <Link href="/app/exams">Exams</Link> : null}
        {hasPermission(user, 'training.exam_result.read') ? (
          <Link href="/app/exam-results">Results</Link>
        ) : null}
        {hasPermission(user, 'training.manpower.read') ? (
          <Link href="/app/manpower-trainings">Manpower training</Link>
        ) : null}
        {hasAnyPermission(user, OVERSEAS_READ_KEYS) ? <Link href="/app/overseas">Overseas</Link> : null}
        {hasPermission(user, 'operations.license.read') ? <Link href="/app/licenses">Licenses</Link> : null}
        <button type="button" onClick={() => void logout().then(() => router.replace('/login'))}>
          Sign out
        </button>
      </nav>
    </header>
  );
}
