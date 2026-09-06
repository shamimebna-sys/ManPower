-- CreateSchema
CREATE SCHEMA IF NOT EXISTS "partners";

-- CreateEnum
CREATE TYPE "partners"."EmployerCandidatePurpose" AS ENUM ('FAVORITE', 'RESERVE', 'SELECTED');

-- AlterTable
ALTER TABLE "iam"."users" ADD COLUMN "agent_id" UUID,
ADD COLUMN "sub_agent_id" UUID,
ADD COLUMN "agencier_id" UUID,
ADD COLUMN "companier_id" UUID,
ADD COLUMN "candidate_id" UUID,
ADD COLUMN "employer_id" UUID;

-- CreateTable
CREATE TABLE "partners"."agents" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "code" VARCHAR(100),
    "name" VARCHAR(255),
    "email" VARCHAR(255),
    "mobile" VARCHAR(255),
    "address" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "country_id" BIGINT,
    "balance" DECIMAL(20,6) NOT NULL DEFAULT 0,
    "logo_file_ref" TEXT,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "agents_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "partners"."sub_agents" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "agent_id" UUID NOT NULL,
    "code" VARCHAR(100),
    "name" VARCHAR(255),
    "email" VARCHAR(255),
    "mobile" VARCHAR(255),
    "address" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "country_id" BIGINT,
    "balance" DECIMAL(20,6) NOT NULL DEFAULT 0,
    "logo_file_ref" TEXT,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "sub_agents_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "partners"."agenciers" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "code" VARCHAR(100),
    "name" VARCHAR(255),
    "email" VARCHAR(255),
    "mobile" VARCHAR(255),
    "license_no" VARCHAR(255),
    "vat_no" VARCHAR(255),
    "address" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "country_id" BIGINT,
    "owner_name" VARCHAR(255),
    "owner_mobile" VARCHAR(255),
    "owner_email" VARCHAR(255),
    "bank_account_name" VARCHAR(200),
    "bank_account_no" VARCHAR(200),
    "bank_iban_no" VARCHAR(200),
    "bank_swift_no" VARCHAR(200),
    "bank_name" VARCHAR(200),
    "bank_branch_name" VARCHAR(200),
    "balance" DECIMAL(20,6) NOT NULL DEFAULT 0,
    "logo_file_ref" TEXT,
    "signature_file_ref" TEXT,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "agenciers_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "partners"."companiers" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "code" VARCHAR(100),
    "name" VARCHAR(255),
    "email" VARCHAR(255),
    "mobile" VARCHAR(255),
    "license_no" VARCHAR(255),
    "vat_no" VARCHAR(255),
    "address" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "country_id" BIGINT,
    "owner_name" VARCHAR(255),
    "owner_mobile" VARCHAR(255),
    "owner_email" VARCHAR(255),
    "balance" DECIMAL(20,6) NOT NULL DEFAULT 0,
    "logo_file_ref" TEXT,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "companiers_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "partners"."employers" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "code" VARCHAR(100),
    "name" VARCHAR(255),
    "email" VARCHAR(255),
    "mobile" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "employers_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "partners"."employer_candidates" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "employer_id" UUID NOT NULL,
    "candidate_id" UUID NOT NULL,
    "purpose" "partners"."EmployerCandidatePurpose" NOT NULL,
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "employer_candidates_pkey" PRIMARY KEY ("id")
);

-- CreateIndex
CREATE UNIQUE INDEX "agents_source_legacy_id_key" ON "partners"."agents"("source_legacy_id");

-- CreateIndex
CREATE INDEX "agents_status_idx" ON "partners"."agents"("status");

-- CreateIndex
CREATE INDEX "agents_name_idx" ON "partners"."agents"("name");

-- CreateIndex
CREATE UNIQUE INDEX "sub_agents_source_legacy_id_key" ON "partners"."sub_agents"("source_legacy_id");

-- CreateIndex
CREATE INDEX "sub_agents_agent_id_idx" ON "partners"."sub_agents"("agent_id");

-- CreateIndex
CREATE INDEX "sub_agents_status_idx" ON "partners"."sub_agents"("status");

-- CreateIndex
CREATE INDEX "sub_agents_name_idx" ON "partners"."sub_agents"("name");

-- CreateIndex
CREATE UNIQUE INDEX "agenciers_source_legacy_id_key" ON "partners"."agenciers"("source_legacy_id");

-- CreateIndex
CREATE INDEX "agenciers_status_idx" ON "partners"."agenciers"("status");

-- CreateIndex
CREATE INDEX "agenciers_name_idx" ON "partners"."agenciers"("name");

-- CreateIndex
CREATE UNIQUE INDEX "companiers_source_legacy_id_key" ON "partners"."companiers"("source_legacy_id");

-- CreateIndex
CREATE INDEX "companiers_status_idx" ON "partners"."companiers"("status");

-- CreateIndex
CREATE INDEX "companiers_name_idx" ON "partners"."companiers"("name");

-- CreateIndex
CREATE UNIQUE INDEX "employers_source_legacy_id_key" ON "partners"."employers"("source_legacy_id");

-- CreateIndex
CREATE INDEX "employers_status_idx" ON "partners"."employers"("status");

-- CreateIndex
CREATE INDEX "employers_name_idx" ON "partners"."employers"("name");

-- CreateIndex
CREATE INDEX "employer_candidates_employer_id_status_idx" ON "partners"."employer_candidates"("employer_id", "status");

-- CreateIndex
CREATE INDEX "employer_candidates_candidate_id_status_idx" ON "partners"."employer_candidates"("candidate_id", "status");

-- CreateIndex
CREATE INDEX "employer_candidates_employer_id_candidate_id_purpose_status_idx" ON "partners"."employer_candidates"("employer_id", "candidate_id", "purpose", "status");

-- CreateIndex
CREATE UNIQUE INDEX "employer_candidates_active_membership_key" ON "partners"."employer_candidates"("employer_id", "candidate_id", "purpose") WHERE "status" = 'A';

-- CreateIndex
CREATE INDEX "users_agent_id_idx" ON "iam"."users"("agent_id");

-- CreateIndex
CREATE INDEX "users_sub_agent_id_idx" ON "iam"."users"("sub_agent_id");

-- CreateIndex
CREATE INDEX "users_agencier_id_idx" ON "iam"."users"("agencier_id");

-- CreateIndex
CREATE INDEX "users_companier_id_idx" ON "iam"."users"("companier_id");

-- CreateIndex
CREATE INDEX "users_candidate_id_idx" ON "iam"."users"("candidate_id");

-- CreateIndex
CREATE INDEX "users_employer_id_idx" ON "iam"."users"("employer_id");

-- AddForeignKey
ALTER TABLE "partners"."sub_agents" ADD CONSTRAINT "sub_agents_agent_id_fkey" FOREIGN KEY ("agent_id") REFERENCES "partners"."agents"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "partners"."employer_candidates" ADD CONSTRAINT "employer_candidates_employer_id_fkey" FOREIGN KEY ("employer_id") REFERENCES "partners"."employers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "partners"."employer_candidates" ADD CONSTRAINT "employer_candidates_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "iam"."users" ADD CONSTRAINT "users_agent_id_fkey" FOREIGN KEY ("agent_id") REFERENCES "partners"."agents"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "iam"."users" ADD CONSTRAINT "users_sub_agent_id_fkey" FOREIGN KEY ("sub_agent_id") REFERENCES "partners"."sub_agents"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "iam"."users" ADD CONSTRAINT "users_agencier_id_fkey" FOREIGN KEY ("agencier_id") REFERENCES "partners"."agenciers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "iam"."users" ADD CONSTRAINT "users_companier_id_fkey" FOREIGN KEY ("companier_id") REFERENCES "partners"."companiers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "iam"."users" ADD CONSTRAINT "users_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "iam"."users" ADD CONSTRAINT "users_employer_id_fkey" FOREIGN KEY ("employer_id") REFERENCES "partners"."employers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "partners"."employer_candidates"
    ADD CONSTRAINT "employer_candidates_status_check" CHECK ("status" IN ('A', 'I'));
