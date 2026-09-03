import Link from 'next/link';

export default function ForbiddenPage() {
  return (
    <section className="status-page">
      <h1>403</h1>
      <h2>Access denied</h2>
      <p>Your account does not have permission to access this area.</p>
      <Link href="/app">Return to the application</Link>
    </section>
  );
}
