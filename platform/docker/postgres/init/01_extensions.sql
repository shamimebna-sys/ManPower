-- PostgreSQL initialization — ManPower Platform
-- Runs once when the container is first created.

-- Enable pgcrypto for UUID generation
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Enable pg_stat_statements for query monitoring (optional, useful for production tuning)
-- CREATE EXTENSION IF NOT EXISTS pg_stat_statements;

-- Set default timezone on whichever database POSTGRES_DB created.
SELECT format('ALTER DATABASE %I SET timezone TO %L', current_database(), 'UTC') \gexec
