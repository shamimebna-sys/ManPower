/**
 * API client foundation.
 *
 * Provides a typed fetch wrapper for communicating with the ManPower API.
 * All requests go through this client so that:
 *  - Base URL is managed in one place
 *  - Authentication headers are added consistently (M2+)
 *  - Error handling is uniform
 *  - Request IDs are forwarded
 *
 * M1 SCOPE: Base URL, JSON parsing, error wrapping only.
 * Authentication interceptors will be added in M2.
 */
import type { ApiResponse, ApiErrorResponse } from '@manpower/shared';
import { config } from './config';

export class ApiClientError extends Error {
  constructor(
    public readonly statusCode: number,
    public readonly code: string,
    message: string,
    public readonly details?: unknown
  ) {
    super(message);
    this.name = 'ApiClientError';
  }
}

interface RequestOptions extends Omit<RequestInit, 'body'> {
  body?: unknown;
  /** Skip authentication header (for public endpoints) */
  skipAuth?: boolean;
}

async function apiFetch<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { body, skipAuth: _skipAuth, ...rest } = options;
  const csrfToken =
    typeof document !== 'undefined'
      ? document.cookie
          .split('; ')
          .find((entry) => entry.startsWith('manpower_csrf='))
          ?.split('=')[1]
      : undefined;

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    ...(rest.headers as Record<string, string>),
    ...(csrfToken ? { 'X-CSRF-Token': decodeURIComponent(csrfToken) } : {}),
  };

  // TODO (M2): Add Authorization header from session store
  // if (!skipAuth) {
  //   const token = getSessionToken();
  //   if (token) headers['Authorization'] = `Bearer ${token}`;
  // }

  const response = await fetch(`${config.apiUrl}${path}`, {
    ...rest,
    headers,
    credentials: 'include',
    ...(body !== undefined ? { body: JSON.stringify(body) } : {}),
  });

  const json = (await response.json()) as ApiResponse<T> | ApiErrorResponse;

  if (!response.ok || !json.success) {
    const errorResponse = json as ApiErrorResponse;
    throw new ApiClientError(
      response.status,
      errorResponse.error?.code ?? 'UNKNOWN_ERROR',
      errorResponse.error?.message ?? `HTTP ${response.status}`,
      errorResponse.error?.details
    );
  }

  return (json as ApiResponse<T>).data;
}

export const apiClient = {
  get: <T>(path: string, options?: RequestOptions) =>
    apiFetch<T>(path, { ...options, method: 'GET' }),

  post: <T>(path: string, body: unknown, options?: RequestOptions) =>
    apiFetch<T>(path, { ...options, method: 'POST', body }),

  put: <T>(path: string, body: unknown, options?: RequestOptions) =>
    apiFetch<T>(path, { ...options, method: 'PUT', body }),

  patch: <T>(path: string, body: unknown, options?: RequestOptions) =>
    apiFetch<T>(path, { ...options, method: 'PATCH', body }),

  delete: <T>(path: string, options?: RequestOptions) =>
    apiFetch<T>(path, { ...options, method: 'DELETE' }),
};
