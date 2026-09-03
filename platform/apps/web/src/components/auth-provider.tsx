'use client';

import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import type { AuthenticatedUser, LoginResult } from '@manpower/shared';
import { apiClient, ApiClientError } from '@/lib/api-client';

interface AuthContextValue {
  user: AuthenticatedUser | null;
  loading: boolean;
  login(identifier: string, password: string): Promise<void>;
  logout(): Promise<void>;
  refresh(): Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthenticatedUser | null>(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    try {
      setUser(await apiClient.get<AuthenticatedUser>('/api/v1/auth/me'));
    } catch (error) {
      if (!(error instanceof ApiClientError) || error.statusCode !== 401) throw error;
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const login = useCallback(async (identifier: string, password: string) => {
    const result = await apiClient.post<LoginResult>(
      '/api/v1/auth/login',
      { identifier, password },
      { skipAuth: true }
    );
    setUser(result.user);
  }, []);

  const logout = useCallback(async () => {
    await apiClient.post('/api/v1/auth/logout', {});
    setUser(null);
  }, []);

  const value = useMemo(
    () => ({ user, loading, login, logout, refresh }),
    [user, loading, login, logout, refresh]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const value = useContext(AuthContext);
  if (!value) throw new Error('useAuth must be used inside AuthProvider');
  return value;
}
