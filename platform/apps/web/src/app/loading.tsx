/**
 * Root loading UI — shown during page transitions and Suspense boundaries.
 */
export default function LoadingPage() {
  return (
    <div
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        height: '100vh',
        color: '#6c757d',
        gap: '0.75rem',
        fontSize: '0.9rem',
      }}
    >
      <span
        style={{
          display: 'inline-block',
          width: '16px',
          height: '16px',
          border: '2px solid #dee2e6',
          borderTopColor: '#0070f3',
          borderRadius: '50%',
          animation: 'spin 0.6s linear infinite',
        }}
      />
      Loading…
      <style>{`
        @keyframes spin {
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}
