-- Atomic allocator for new candidate codes (fallback prefix 9 + 6 digits).
-- Replaces COUNT+1. Migrated legacy codes are never generated here.

CREATE SEQUENCE "candidate"."candidate_code_seq"
    AS BIGINT
    INCREMENT BY 1
    MINVALUE 1
    START WITH 1
    NO CYCLE;
