import { beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

const mocks = vi.hoisted(() => ({
  replace: vi.fn(),
  user: {
    id: 'user-id',
    email: 'owner@example.com',
    username: 'owner',
    displayName: 'Owner',
    status: 'ACTIVE' as const,
    roles: ['owner'],
    permissions: [
      'training.teacher.read',
      'training.teacher.manage',
      'training.class_group.read',
      'training.class_group.manage',
      'training.schedule.read',
      'training.schedule.manage',
      'training.exam.read',
      'training.exam.manage',
      'training.exam_result.read',
      'training.exam_result.manage',
      'training.manpower.read',
      'training.manpower.manage',
    ],
  },
}));

vi.mock('next/link', () => ({
  default: ({ href, children }: { href: string; children: React.ReactNode }) => <a href={href}>{children}</a>,
}));
vi.mock('next/navigation', () => ({
  useRouter: () => ({ replace: mocks.replace }),
  useParams: () => ({ id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' }),
  useSearchParams: () => new URLSearchParams(),
}));
vi.mock('../src/lib/api-client', () => ({
  apiClient: {
    get: vi.fn().mockResolvedValue({ items: [] }),
    post: vi.fn(),
    patch: vi.fn(),
  },
  ApiClientError: class ApiClientError extends Error {
    constructor(
      public statusCode: number,
      public code: string,
      message: string
    ) {
      super(message);
    }
  },
}));
vi.mock('../src/components/auth-provider', () => ({
  useAuth: () => ({
    user: mocks.user,
    loading: false,
    login: vi.fn(),
    logout: vi.fn(),
    refresh: vi.fn(),
  }),
}));

import { AppHeader } from '../src/components/app-header';
import TeachersPage from '../src/app/app/teachers/page';
import ClassGroupsPage from '../src/app/app/class-groups/page';
import SchedulesPage from '../src/app/app/schedules/page';
import ExamsPage from '../src/app/app/exams/page';
import ExamResultsPage from '../src/app/app/exam-results/page';
import ManpowerTrainingsPage from '../src/app/app/manpower-trainings/page';

describe('M5 training UI', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mocks.user.permissions = [
      'training.teacher.read',
      'training.teacher.manage',
      'training.class_group.read',
      'training.class_group.manage',
      'training.schedule.read',
      'training.schedule.manage',
      'training.exam.read',
      'training.exam.manage',
      'training.exam_result.read',
      'training.exam_result.manage',
      'training.manpower.read',
      'training.manpower.manage',
    ];
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => ({ success: true, data: { items: [] } }),
      })
    );
  });

  it('shows training navigation only when the matching permission is present', () => {
    const { unmount } = render(<AppHeader />);
    expect(screen.getByRole('link', { name: 'Teachers' })).toHaveAttribute('href', '/app/teachers');
    expect(screen.getByRole('link', { name: 'Class groups' })).toHaveAttribute('href', '/app/class-groups');
    expect(screen.getByRole('link', { name: 'Schedules' })).toHaveAttribute('href', '/app/schedules');
    expect(screen.getByRole('link', { name: 'Exams' })).toHaveAttribute('href', '/app/exams');
    expect(screen.getByRole('link', { name: 'Results' })).toHaveAttribute('href', '/app/exam-results');
    expect(screen.getByRole('link', { name: 'Manpower training' })).toHaveAttribute(
      'href',
      '/app/manpower-trainings'
    );
    unmount();

    mocks.user.permissions = ['training.teacher.read'];
    render(<AppHeader />);
    expect(screen.getByRole('link', { name: 'Teachers' })).toBeInTheDocument();
    expect(screen.queryByRole('link', { name: 'Exams' })).not.toBeInTheDocument();
    expect(screen.queryByRole('link', { name: 'Results' })).not.toBeInTheDocument();
    expect(screen.queryByRole('link', { name: 'Candidates' })).not.toBeInTheDocument();
  });

  it('renders teacher, class-group, schedule, exam, result, and manpower pages', async () => {
    render(<TeachersPage />);
    expect(await screen.findByRole('heading', { name: 'Teachers' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Create' })).toBeInTheDocument();

    render(<ClassGroupsPage />);
    expect(await screen.findByRole('heading', { name: 'Class groups' })).toBeInTheDocument();

    render(<SchedulesPage />);
    expect(await screen.findByRole('heading', { name: 'Class schedules' })).toBeInTheDocument();

    render(<ExamsPage />);
    expect(await screen.findByRole('heading', { name: 'Exams' })).toBeInTheDocument();

    render(<ExamResultsPage />);
    expect(await screen.findByRole('heading', { name: 'Exam results' })).toBeInTheDocument();
    expect(screen.getByText(/Marks do not compute a formula/)).toBeInTheDocument();

    render(<ManpowerTrainingsPage />);
    expect(await screen.findByRole('heading', { name: 'Manpower training' })).toBeInTheDocument();
    expect(screen.getByText(/Separate from candidate profile training/)).toBeInTheDocument();
  });
});
