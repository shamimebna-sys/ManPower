import '@testing-library/jest-dom';

// Set environment variables for tests
process.env['NEXT_PUBLIC_API_URL'] = 'http://localhost:4000';
// NODE_ENV is read-only in strict types — set it via vitest config instead
