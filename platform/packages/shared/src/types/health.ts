/**
 * GET /api/health response
 */
export interface HealthResponse {
  status: 'ok' | 'degraded' | 'error';
  version: string;
  environment: string;
  timestamp: string;
  uptime: number;
}

/**
 * GET /api/health/db response
 */
export interface DbHealthResponse {
  status: 'ok' | 'error';
  database: {
    connected: boolean;
    latencyMs?: number;
    message?: string;
  };
  timestamp: string;
}
