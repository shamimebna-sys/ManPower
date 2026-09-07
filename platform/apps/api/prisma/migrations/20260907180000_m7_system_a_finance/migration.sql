-- M7 System A finance. No M8 invoices/tickets. No production data import.

CREATE SCHEMA IF NOT EXISTS "finance";

CREATE TYPE "finance"."WalletOwnerType" AS ENUM ('AGENT', 'SUB_AGENT', 'TEACHER');
CREATE TYPE "finance"."JournalStatus" AS ENUM ('POSTED', 'REVERSED');
CREATE TYPE "finance"."JournalEntryType" AS ENUM ('WALLET_DEPOSIT', 'FEE_APPROVAL', 'FEE_REJECTION_ZERO', 'OPENING_BALANCE', 'REVERSAL');
CREATE TYPE "finance"."JournalSide" AS ENUM ('DEBIT', 'CREDIT');
CREATE TYPE "finance"."AccountType" AS ENUM ('WALLET_LIABILITY', 'DEPOSIT_CLEARING', 'FEE_INCOME', 'OPENING_EQUITY', 'REVERSAL_CLEARING');
CREATE TYPE "finance"."OpeningBatchStatus" AS ENUM ('STAGED', 'APPROVED', 'REJECTED');
CREATE TYPE "finance"."QuarantineStatus" AS ENUM ('OPEN', 'RESOLVED');
CREATE TYPE "finance"."ReconciliationStatus" AS ENUM ('RUNNING', 'PASSED', 'FAILED');

CREATE TABLE "finance"."currencies" (
    "code" VARCHAR(3) NOT NULL,
    "name" VARCHAR(40) NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "currencies_pkey" PRIMARY KEY ("code")
);

CREATE TABLE "finance"."accounts" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "account_type" "finance"."AccountType" NOT NULL,
    "name" VARCHAR(120) NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "accounts_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "accounts_account_type_key" ON "finance"."accounts"("account_type");

CREATE TABLE "finance"."wallets" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "owner_type" "finance"."WalletOwnerType" NOT NULL,
    "agent_id" UUID,
    "sub_agent_id" UUID,
    "teacher_id" UUID,
    "projected_available_eur" DECIMAL(20,6),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    CONSTRAINT "wallets_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "wallets_owner_type_agent_id_key" ON "finance"."wallets"("owner_type", "agent_id");
CREATE UNIQUE INDEX "wallets_owner_type_sub_agent_id_key" ON "finance"."wallets"("owner_type", "sub_agent_id");
CREATE UNIQUE INDEX "wallets_owner_type_teacher_id_key" ON "finance"."wallets"("owner_type", "teacher_id");
CREATE INDEX "wallets_agent_id_idx" ON "finance"."wallets"("agent_id");
CREATE INDEX "wallets_sub_agent_id_idx" ON "finance"."wallets"("sub_agent_id");

CREATE TABLE "finance"."wallet_accounts" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "wallet_id" UUID NOT NULL,
    "currency_code" VARCHAR(3) NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "wallet_accounts_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "wallet_accounts_wallet_id_currency_code_key" ON "finance"."wallet_accounts"("wallet_id", "currency_code");

CREATE TABLE "finance"."fx_rate_entries" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "from_currency" VARCHAR(3) NOT NULL,
    "to_currency" VARCHAR(3) NOT NULL,
    "rate" DECIMAL(20,8) NOT NULL,
    "effective_at" TIMESTAMPTZ(6) NOT NULL,
    "entered_by_user_id" UUID NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "fx_rate_entries_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "fx_rate_entries_from_currency_to_currency_effective_at_idx" ON "finance"."fx_rate_entries"("from_currency", "to_currency", "effective_at");

CREATE TABLE "finance"."payment_requests" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "status" VARCHAR(1) NOT NULL DEFAULT 'P',
    "candidate_id" UUID,
    "wallet_id" UUID NOT NULL,
    "amount" DECIMAL(20,6) NOT NULL,
    "bill_title" VARCHAR(120) NOT NULL,
    "bill_type_code" VARCHAR(40),
    "legacy_bill_code" INTEGER,
    "source_legacy_id" BIGINT,
    "source_table" VARCHAR(80),
    "journal_id" UUID,
    "created_by_user_id" UUID,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    CONSTRAINT "payment_requests_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "payment_requests_journal_id_key" ON "finance"."payment_requests"("journal_id");
CREATE INDEX "payment_requests_candidate_id_status_idx" ON "finance"."payment_requests"("candidate_id", "status");
CREATE INDEX "payment_requests_wallet_id_status_idx" ON "finance"."payment_requests"("wallet_id", "status");
CREATE INDEX "payment_requests_bill_title_status_idx" ON "finance"."payment_requests"("bill_title", "status");
CREATE UNIQUE INDEX "payment_requests_new_pending_candidate_title" ON "finance"."payment_requests"("candidate_id", "bill_title")
    WHERE "source_legacy_id" IS NULL AND "status" = 'P' AND "candidate_id" IS NOT NULL;

CREATE TABLE "finance"."wallet_deposits" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "status" VARCHAR(1) NOT NULL DEFAULT 'P',
    "wallet_id" UUID NOT NULL,
    "amount" DECIMAL(20,6) NOT NULL,
    "currency_code" VARCHAR(3) NOT NULL,
    "fx_rate" DECIMAL(20,8),
    "fx_rate_entry_id" UUID,
    "journal_id" UUID,
    "source_legacy_id" BIGINT,
    "created_by_user_id" UUID,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    CONSTRAINT "wallet_deposits_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "wallet_deposits_journal_id_key" ON "finance"."wallet_deposits"("journal_id");
CREATE INDEX "wallet_deposits_wallet_id_status_idx" ON "finance"."wallet_deposits"("wallet_id", "status");

CREATE TABLE "finance"."journal_entries" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "status" "finance"."JournalStatus" NOT NULL DEFAULT 'POSTED',
    "entry_type" "finance"."JournalEntryType" NOT NULL,
    "source_type" VARCHAR(40) NOT NULL,
    "source_id" VARCHAR(64) NOT NULL,
    "reference" VARCHAR(200),
    "posted_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "created_by_user_id" UUID,
    "approved_by_user_id" UUID,
    "idempotency_key" VARCHAR(160) NOT NULL,
    "reverses_journal_id" UUID,
    "reversed_by_journal_id" UUID,
    "correlation_id" VARCHAR(100),
    "payment_request_id" UUID,
    "wallet_deposit_id" UUID,
    "fx_rate_entry_id" UUID,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "journal_entries_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "journal_entries_idempotency_key_key" ON "finance"."journal_entries"("idempotency_key");
CREATE INDEX "journal_entries_entry_type_posted_at_idx" ON "finance"."journal_entries"("entry_type", "posted_at");
CREATE INDEX "journal_entries_source_type_source_id_idx" ON "finance"."journal_entries"("source_type", "source_id");
CREATE INDEX "journal_entries_payment_request_id_idx" ON "finance"."journal_entries"("payment_request_id");
CREATE UNIQUE INDEX "journal_entries_fee_approval_request_uniq" ON "finance"."journal_entries"("payment_request_id")
    WHERE "entry_type" = 'FEE_APPROVAL' AND "payment_request_id" IS NOT NULL;

CREATE TABLE "finance"."journal_lines" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "journal_id" UUID NOT NULL,
    "line_no" INTEGER NOT NULL,
    "account_type" "finance"."AccountType" NOT NULL,
    "account_id" UUID,
    "wallet_account_id" UUID,
    "side" "finance"."JournalSide" NOT NULL,
    "currency_code" VARCHAR(3) NOT NULL,
    "amount" DECIMAL(20,6) NOT NULL,
    "fx_rate" DECIMAL(20,8) NOT NULL,
    "base_amount" DECIMAL(20,6) NOT NULL,
    "fx_direction" VARCHAR(40) NOT NULL,
    "bill_type_code" VARCHAR(40),
    "bill_title" VARCHAR(120),
    "source_type" VARCHAR(40),
    "source_id" VARCHAR(64),
    CONSTRAINT "journal_lines_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "journal_lines_journal_id_line_no_key" ON "finance"."journal_lines"("journal_id", "line_no");
CREATE INDEX "journal_lines_wallet_account_id_idx" ON "finance"."journal_lines"("wallet_account_id");

CREATE TABLE "finance"."opening_balance_batches" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "status" "finance"."OpeningBatchStatus" NOT NULL DEFAULT 'STAGED',
    "migration_run_id" VARCHAR(100) NOT NULL,
    "approved_by_user_id" UUID,
    "approved_at" TIMESTAMPTZ(6),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "opening_balance_batches_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "opening_balance_batches_migration_run_id_idx" ON "finance"."opening_balance_batches"("migration_run_id");

CREATE TABLE "finance"."opening_manifests" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "batch_id" UUID NOT NULL,
    "source_payment_id" VARCHAR(64),
    "source_table" VARCHAR(80),
    "source_hash" VARCHAR(128),
    "migration_run_id" VARCHAR(100) NOT NULL,
    "wallet_id" UUID NOT NULL,
    "currency_code" VARCHAR(3) NOT NULL,
    "source_amount" DECIMAL(20,6) NOT NULL,
    "source_exchange_rate" DECIMAL(20,8) NOT NULL,
    "journal_id" UUID,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "opening_manifests_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "opening_manifests_source_payment_id_idx" ON "finance"."opening_manifests"("source_payment_id");
CREATE INDEX "opening_manifests_batch_id_idx" ON "finance"."opening_manifests"("batch_id");

CREATE TABLE "finance"."reconstruction_manifests" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_payment_id" VARCHAR(64) NOT NULL,
    "source_table" VARCHAR(80) NOT NULL,
    "source_hash" VARCHAR(128) NOT NULL,
    "migration_run_id" VARCHAR(100) NOT NULL,
    "wallet_id" UUID NOT NULL,
    "currency_code" VARCHAR(3) NOT NULL,
    "source_amount" DECIMAL(20,6) NOT NULL,
    "source_exchange_rate" DECIMAL(20,8) NOT NULL,
    "journal_id" UUID NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "reconstruction_manifests_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "reconstruction_manifests_source_payment_id_key" ON "finance"."reconstruction_manifests"("source_payment_id");
CREATE UNIQUE INDEX "reconstruction_manifests_journal_id_key" ON "finance"."reconstruction_manifests"("journal_id");
CREATE INDEX "reconstruction_manifests_migration_run_id_idx" ON "finance"."reconstruction_manifests"("migration_run_id");

CREATE TABLE "finance"."quarantine" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_table" VARCHAR(80) NOT NULL,
    "source_pk" VARCHAR(64) NOT NULL,
    "source_hash" VARCHAR(128),
    "migration_run_id" VARCHAR(100) NOT NULL,
    "reason" VARCHAR(80) NOT NULL,
    "payload" JSONB,
    "reconciliation_impact" VARCHAR(200),
    "status" "finance"."QuarantineStatus" NOT NULL DEFAULT 'OPEN',
    "resolved_at" TIMESTAMPTZ(6),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "quarantine_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "quarantine_migration_run_id_status_idx" ON "finance"."quarantine"("migration_run_id", "status");
CREATE INDEX "quarantine_reason_idx" ON "finance"."quarantine"("reason");

CREATE TABLE "finance"."reconciliation_runs" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "migration_run_id" VARCHAR(100) NOT NULL,
    "status" "finance"."ReconciliationStatus" NOT NULL DEFAULT 'RUNNING',
    "gates" JSONB NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "completed_at" TIMESTAMPTZ(6),
    CONSTRAINT "reconciliation_runs_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "reconciliation_runs_migration_run_id_idx" ON "finance"."reconciliation_runs"("migration_run_id");

ALTER TABLE "finance"."wallets" ADD CONSTRAINT "wallets_agent_id_fkey" FOREIGN KEY ("agent_id") REFERENCES "partners"."agents"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."wallets" ADD CONSTRAINT "wallets_sub_agent_id_fkey" FOREIGN KEY ("sub_agent_id") REFERENCES "partners"."sub_agents"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."wallets" ADD CONSTRAINT "wallets_teacher_id_fkey" FOREIGN KEY ("teacher_id") REFERENCES "operations"."teachers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."wallet_accounts" ADD CONSTRAINT "wallet_accounts_wallet_id_fkey" FOREIGN KEY ("wallet_id") REFERENCES "finance"."wallets"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."wallet_accounts" ADD CONSTRAINT "wallet_accounts_currency_code_fkey" FOREIGN KEY ("currency_code") REFERENCES "finance"."currencies"("code") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."fx_rate_entries" ADD CONSTRAINT "fx_rate_entries_from_currency_fkey" FOREIGN KEY ("from_currency") REFERENCES "finance"."currencies"("code") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."fx_rate_entries" ADD CONSTRAINT "fx_rate_entries_to_currency_fkey" FOREIGN KEY ("to_currency") REFERENCES "finance"."currencies"("code") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."payment_requests" ADD CONSTRAINT "payment_requests_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."payment_requests" ADD CONSTRAINT "payment_requests_wallet_id_fkey" FOREIGN KEY ("wallet_id") REFERENCES "finance"."wallets"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."wallet_deposits" ADD CONSTRAINT "wallet_deposits_wallet_id_fkey" FOREIGN KEY ("wallet_id") REFERENCES "finance"."wallets"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."wallet_deposits" ADD CONSTRAINT "wallet_deposits_fx_rate_entry_id_fkey" FOREIGN KEY ("fx_rate_entry_id") REFERENCES "finance"."fx_rate_entries"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."journal_entries" ADD CONSTRAINT "journal_entries_reverses_journal_id_fkey" FOREIGN KEY ("reverses_journal_id") REFERENCES "finance"."journal_entries"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."journal_entries" ADD CONSTRAINT "journal_entries_payment_request_id_fkey" FOREIGN KEY ("payment_request_id") REFERENCES "finance"."payment_requests"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."journal_entries" ADD CONSTRAINT "journal_entries_wallet_deposit_id_fkey" FOREIGN KEY ("wallet_deposit_id") REFERENCES "finance"."wallet_deposits"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."journal_entries" ADD CONSTRAINT "journal_entries_fx_rate_entry_id_fkey" FOREIGN KEY ("fx_rate_entry_id") REFERENCES "finance"."fx_rate_entries"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."journal_lines" ADD CONSTRAINT "journal_lines_journal_id_fkey" FOREIGN KEY ("journal_id") REFERENCES "finance"."journal_entries"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."journal_lines" ADD CONSTRAINT "journal_lines_account_id_fkey" FOREIGN KEY ("account_id") REFERENCES "finance"."accounts"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."journal_lines" ADD CONSTRAINT "journal_lines_wallet_account_id_fkey" FOREIGN KEY ("wallet_account_id") REFERENCES "finance"."wallet_accounts"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."journal_lines" ADD CONSTRAINT "journal_lines_currency_code_fkey" FOREIGN KEY ("currency_code") REFERENCES "finance"."currencies"("code") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."opening_manifests" ADD CONSTRAINT "opening_manifests_batch_id_fkey" FOREIGN KEY ("batch_id") REFERENCES "finance"."opening_balance_batches"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."opening_manifests" ADD CONSTRAINT "opening_manifests_wallet_id_fkey" FOREIGN KEY ("wallet_id") REFERENCES "finance"."wallets"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."opening_manifests" ADD CONSTRAINT "opening_manifests_currency_code_fkey" FOREIGN KEY ("currency_code") REFERENCES "finance"."currencies"("code") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."opening_manifests" ADD CONSTRAINT "opening_manifests_journal_id_fkey" FOREIGN KEY ("journal_id") REFERENCES "finance"."journal_entries"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "finance"."reconstruction_manifests" ADD CONSTRAINT "reconstruction_manifests_wallet_id_fkey" FOREIGN KEY ("wallet_id") REFERENCES "finance"."wallets"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."reconstruction_manifests" ADD CONSTRAINT "reconstruction_manifests_currency_code_fkey" FOREIGN KEY ("currency_code") REFERENCES "finance"."currencies"("code") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "finance"."reconstruction_manifests" ADD CONSTRAINT "reconstruction_manifests_journal_id_fkey" FOREIGN KEY ("journal_id") REFERENCES "finance"."journal_entries"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

INSERT INTO "finance"."currencies" ("code", "name") VALUES ('BDT', 'Bangladeshi Taka'), ('EUR', 'Euro');

INSERT INTO "finance"."accounts" ("account_type", "name") VALUES
    ('WALLET_LIABILITY', 'Wallet liability'),
    ('DEPOSIT_CLEARING', 'Deposit clearing'),
    ('FEE_INCOME', 'Fee income'),
    ('OPENING_EQUITY', 'Opening equity'),
    ('REVERSAL_CLEARING', 'Reversal clearing');

CREATE OR REPLACE FUNCTION finance.assert_journal_balanced()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
  target UUID;
  line_count INT;
  debit_base NUMERIC(20,6);
  credit_base NUMERIC(20,6);
  journal_status "finance"."JournalStatus";
BEGIN
  target := COALESCE(NEW.journal_id, OLD.journal_id);
  SELECT status INTO journal_status FROM finance.journal_entries WHERE id = target;
  IF journal_status IS NULL THEN
    RETURN COALESCE(NEW, OLD);
  END IF;
  SELECT COUNT(*),
         COALESCE(SUM(CASE WHEN side = 'DEBIT' THEN base_amount ELSE 0 END), 0),
         COALESCE(SUM(CASE WHEN side = 'CREDIT' THEN base_amount ELSE 0 END), 0)
    INTO line_count, debit_base, credit_base
    FROM finance.journal_lines
   WHERE journal_id = target;
  IF line_count > 0 AND line_count < 2 THEN
    RAISE EXCEPTION 'finance journal % must have at least two lines', target;
  END IF;
  IF line_count >= 2 AND debit_base <> credit_base THEN
    RAISE EXCEPTION 'finance journal % unbalanced: debit % credit %', target, debit_base, credit_base;
  END IF;
  RETURN COALESCE(NEW, OLD);
END;
$$;

CREATE CONSTRAINT TRIGGER journal_lines_balanced
AFTER INSERT OR UPDATE OR DELETE ON finance.journal_lines
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW
EXECUTE FUNCTION finance.assert_journal_balanced();

CREATE OR REPLACE FUNCTION finance.forbid_posted_line_mutation()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    RAISE EXCEPTION 'posted journal lines cannot be deleted';
  END IF;
  IF NEW.amount IS DISTINCT FROM OLD.amount
     OR NEW.fx_rate IS DISTINCT FROM OLD.fx_rate
     OR NEW.base_amount IS DISTINCT FROM OLD.base_amount
     OR NEW.account_type IS DISTINCT FROM OLD.account_type
     OR NEW.side IS DISTINCT FROM OLD.side
     OR NEW.currency_code IS DISTINCT FROM OLD.currency_code THEN
    RAISE EXCEPTION 'posted journal lines are immutable';
  END IF;
  RETURN NEW;
END;
$$;

CREATE TRIGGER journal_lines_immutable
BEFORE UPDATE OR DELETE ON finance.journal_lines
FOR EACH ROW
EXECUTE FUNCTION finance.forbid_posted_line_mutation();

CREATE OR REPLACE FUNCTION finance.forbid_posted_header_money_mutation()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    RAISE EXCEPTION 'posted journals cannot be deleted';
  END IF;
  IF NEW.entry_type IS DISTINCT FROM OLD.entry_type
     OR NEW.source_type IS DISTINCT FROM OLD.source_type
     OR NEW.source_id IS DISTINCT FROM OLD.source_id
     OR NEW.idempotency_key IS DISTINCT FROM OLD.idempotency_key THEN
    RAISE EXCEPTION 'posted journal headers are immutable except reversal flags';
  END IF;
  RETURN NEW;
END;
$$;

CREATE TRIGGER journal_entries_immutable
BEFORE UPDATE OR DELETE ON finance.journal_entries
FOR EACH ROW
EXECUTE FUNCTION finance.forbid_posted_header_money_mutation();

CREATE OR REPLACE FUNCTION finance.forbid_opening_of_reconstructed()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  IF NEW.source_payment_id IS NOT NULL AND EXISTS (
    SELECT 1 FROM finance.reconstruction_manifests r WHERE r.source_payment_id = NEW.source_payment_id
  ) THEN
    RAISE EXCEPTION 'DR-H1 double-count forbidden: payment % already reconstructed', NEW.source_payment_id;
  END IF;
  RETURN NEW;
END;
$$;

CREATE TRIGGER opening_manifests_no_double_count
BEFORE INSERT OR UPDATE ON finance.opening_manifests
FOR EACH ROW
EXECUTE FUNCTION finance.forbid_opening_of_reconstructed();

CREATE OR REPLACE FUNCTION finance.forbid_reconstruct_of_opening()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  IF EXISTS (
    SELECT 1 FROM finance.opening_manifests o WHERE o.source_payment_id = NEW.source_payment_id
  ) THEN
    RAISE EXCEPTION 'DR-H1 double-count forbidden: payment % already on opening manifest', NEW.source_payment_id;
  END IF;
  RETURN NEW;
END;
$$;

CREATE TRIGGER reconstruction_manifests_no_double_count
BEFORE INSERT OR UPDATE ON finance.reconstruction_manifests
FOR EACH ROW
EXECUTE FUNCTION finance.forbid_reconstruct_of_opening();
