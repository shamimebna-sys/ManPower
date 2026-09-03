import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

// This middleware is a navigation convenience only. It checks cookie presence
// and redirects unauthenticated browsers; the API validates the session and
// permissions server-side on every protected request.
export function middleware(request: NextRequest) {
  const hasSession = request.cookies.has('manpower_session');
  if (!hasSession) {
    const login = new URL('/login', request.url);
    login.searchParams.set('returnTo', request.nextUrl.pathname);
    return NextResponse.redirect(login);
  }
  return NextResponse.next();
}

export const config = {
  matcher: ['/app/:path*'],
};
