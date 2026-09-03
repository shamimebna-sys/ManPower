/**
 * Standard API response envelope.
 * All API endpoints return this shape for consistency.
 */
export interface ApiResponse<T = unknown> {
  success: boolean;
  data: T;
  meta?: ResponseMeta;
}

/**
 * Standard API error response.
 */
export interface ApiErrorResponse {
  success: false;
  error: {
    code: string;
    message: string;
    /** Field-level validation errors (present on 422 responses) */
    details?: ValidationError[];
    /** Request ID for tracing (present in production) */
    requestId?: string;
  };
}

export interface ValidationError {
  field: string;
  message: string;
  code?: string;
}

export interface ResponseMeta {
  /** ISO 8601 timestamp of the response */
  timestamp: string;
  /** API version string, e.g. "v1" */
  version: string;
  /** Request ID for distributed tracing */
  requestId?: string;
  /** Pagination info when applicable */
  pagination?: PaginationMeta;
}

export interface PaginationMeta {
  /** Cursor for the next page (null if last page) */
  nextCursor: string | null;
  /** Cursor for the previous page (null if first page) */
  prevCursor: string | null;
  /** Total count (omitted for large datasets — use cursors) */
  total?: number;
}
