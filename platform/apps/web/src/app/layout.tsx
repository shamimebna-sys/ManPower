import type { Metadata, Viewport } from 'next';
import './globals.css';
import { AuthProvider } from '@/components/auth-provider';

export const metadata: Metadata = {
  title: {
    default: 'ManPower Management System',
    template: '%s | ManPower',
  },
  description: 'ManPower overseas employment management system',
  robots: {
    index: false, // Keep private — not for public indexing
    follow: false,
  },
};

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
};

interface RootLayoutProps {
  children: React.ReactNode;
}

/**
 * Root layout — wraps every page in the application.
 *
 * M1 SCOPE: Minimal HTML shell, metadata, global CSS.
 * Navigation, sidebar, auth wrapper will be added in M2+.
 */
export default function RootLayout({ children }: RootLayoutProps) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body>
        {/*
         * M2+: Add authentication provider, navigation, toast notifications.
         * M12+: Add admin layout shell, sidebar, breadcrumbs.
         */}
        <AuthProvider>
          <main>{children}</main>
        </AuthProvider>
      </body>
    </html>
  );
}
