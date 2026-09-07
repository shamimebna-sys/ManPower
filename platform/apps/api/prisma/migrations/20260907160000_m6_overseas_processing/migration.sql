-- M6 overseas processing: medical, police clearance, ARC, labour, visa, flight,
-- licenses/license_positions, live_status lookup.
-- FKs to candidates/companiers/licenses are nullable so M13 can quarantine orphans.
-- Do not import production data here.

-- CreateTable
CREATE TABLE "candidate"."medicals" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "candidate_id" UUID,
    "issue_date" DATE,
    "expire_date" DATE,
    "status" VARCHAR(5),
    "document_file_id" UUID,
    "country_id" BIGINT,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "medicals_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "workflow"."police_clearances" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "candidate_id" UUID,
    "thana_id" BIGINT,
    "country_id" BIGINT,
    "issue_date" DATE,
    "expire_date" DATE,
    "photo_file_id" UUID,
    "status" VARCHAR(5),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "police_clearances_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "candidate"."arcs" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "candidate_id" UUID,
    "issue_date" DATE,
    "expire_date" DATE,
    "is_lifetime" VARCHAR(5),
    "status" VARCHAR(5),
    "arc_file_id" UUID,
    "arc_number" VARCHAR(255),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "arcs_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "candidate"."labour_contracts" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "candidate_id" UUID,
    "issue_date" DATE,
    "expire_date" DATE,
    "document_file_id" UUID,
    "status" VARCHAR(5),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "labour_contracts_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "candidate"."visa_immigrations" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "candidate_id" UUID,
    "issue_date" DATE,
    "expire_date" DATE,
    "visa_mp_no" VARCHAR(255),
    "status" VARCHAR(5),
    "document_file_id" UUID,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "visa_immigrations_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "candidate"."flight_schedules" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "candidate_id" UUID,
    "airlinece_name" VARCHAR(255),
    "flight_date" DATE,
    "flight_time" TIME(6),
    "departure_time" TIME(6),
    "arrival_time" TIME(6),
    "ticket_file_id" UUID,
    "arrival_seal_page_file_id" UUID,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "flight_schedules_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "operations"."licenses" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "license_no" VARCHAR(255) NOT NULL,
    "status" VARCHAR(5) DEFAULT 'A',
    "companier_id" UUID,
    "license_start_date" DATE,
    "license_expire_date" DATE,
    "license_file_id" UUID,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "licenses_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "operations"."license_positions" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "license_id" UUID,
    "position_id" BIGINT,
    "quantity" INTEGER DEFAULT 0,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "license_positions_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "operations"."live_status" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "name" VARCHAR(255),
    "description" VARCHAR(255),
    "step_no" INTEGER,
    "status" VARCHAR(5),
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "live_status_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "medicals_source_legacy_id_key" ON "candidate"."medicals"("source_legacy_id");
CREATE INDEX "medicals_candidate_id_created_at_idx" ON "candidate"."medicals"("candidate_id", "created_at");
CREATE INDEX "medicals_candidate_id_source_legacy_id_idx" ON "candidate"."medicals"("candidate_id", "source_legacy_id");

CREATE UNIQUE INDEX "police_clearances_source_legacy_id_key" ON "workflow"."police_clearances"("source_legacy_id");
CREATE INDEX "police_clearances_candidate_id_created_at_idx" ON "workflow"."police_clearances"("candidate_id", "created_at");
CREATE INDEX "police_clearances_candidate_id_source_legacy_id_idx" ON "workflow"."police_clearances"("candidate_id", "source_legacy_id");

CREATE UNIQUE INDEX "arcs_source_legacy_id_key" ON "candidate"."arcs"("source_legacy_id");
CREATE INDEX "arcs_candidate_id_created_at_idx" ON "candidate"."arcs"("candidate_id", "created_at");
CREATE INDEX "arcs_candidate_id_source_legacy_id_idx" ON "candidate"."arcs"("candidate_id", "source_legacy_id");

CREATE UNIQUE INDEX "labour_contracts_source_legacy_id_key" ON "candidate"."labour_contracts"("source_legacy_id");
CREATE INDEX "labour_contracts_candidate_id_created_at_idx" ON "candidate"."labour_contracts"("candidate_id", "created_at");
CREATE INDEX "labour_contracts_candidate_id_source_legacy_id_idx" ON "candidate"."labour_contracts"("candidate_id", "source_legacy_id");

CREATE UNIQUE INDEX "visa_immigrations_source_legacy_id_key" ON "candidate"."visa_immigrations"("source_legacy_id");
CREATE INDEX "visa_immigrations_candidate_id_created_at_idx" ON "candidate"."visa_immigrations"("candidate_id", "created_at");
CREATE INDEX "visa_immigrations_candidate_id_source_legacy_id_idx" ON "candidate"."visa_immigrations"("candidate_id", "source_legacy_id");

CREATE UNIQUE INDEX "flight_schedules_source_legacy_id_key" ON "candidate"."flight_schedules"("source_legacy_id");
CREATE INDEX "flight_schedules_candidate_id_created_at_idx" ON "candidate"."flight_schedules"("candidate_id", "created_at");
CREATE INDEX "flight_schedules_candidate_id_source_legacy_id_idx" ON "candidate"."flight_schedules"("candidate_id", "source_legacy_id");

CREATE UNIQUE INDEX "licenses_source_legacy_id_key" ON "operations"."licenses"("source_legacy_id");
CREATE UNIQUE INDEX "licenses_license_no_key" ON "operations"."licenses"("license_no");
CREATE INDEX "licenses_companier_id_idx" ON "operations"."licenses"("companier_id");
CREATE INDEX "licenses_status_idx" ON "operations"."licenses"("status");

CREATE UNIQUE INDEX "license_positions_source_legacy_id_key" ON "operations"."license_positions"("source_legacy_id");
CREATE INDEX "license_positions_license_id_idx" ON "operations"."license_positions"("license_id");

CREATE UNIQUE INDEX "live_status_source_legacy_id_key" ON "operations"."live_status"("source_legacy_id");
CREATE UNIQUE INDEX "live_status_step_no_key" ON "operations"."live_status"("step_no");

ALTER TABLE "candidate"."medicals" ADD CONSTRAINT "medicals_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "workflow"."police_clearances" ADD CONSTRAINT "police_clearances_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "candidate"."arcs" ADD CONSTRAINT "arcs_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "candidate"."labour_contracts" ADD CONSTRAINT "labour_contracts_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "candidate"."visa_immigrations" ADD CONSTRAINT "visa_immigrations_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "candidate"."flight_schedules" ADD CONSTRAINT "flight_schedules_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "operations"."licenses" ADD CONSTRAINT "licenses_companier_id_fkey" FOREIGN KEY ("companier_id") REFERENCES "partners"."companiers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "operations"."license_positions" ADD CONSTRAINT "license_positions_license_id_fkey" FOREIGN KEY ("license_id") REFERENCES "operations"."licenses"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- Exact dump labels. Do not correct spelling.
INSERT INTO "operations"."live_status" ("source_legacy_id", "name", "description", "step_no", "status", "updated_at")
VALUES
  (1, 'Registration', NULL, 1, 'A', CURRENT_TIMESTAMP),
  (2, 'Profile Update/CV', NULL, 2, 'A', CURRENT_TIMESTAMP),
  (3, 'Group Name', NULL, 3, 'A', CURRENT_TIMESTAMP),
  (4, 'Interview', NULL, 4, 'A', CURRENT_TIMESTAMP),
  (5, 'Selection', NULL, 5, 'A', CURRENT_TIMESTAMP),
  (6, 'Labour Contact', NULL, 6, 'A', CURRENT_TIMESTAMP),
  (7, 'Police Clearance & Medical', NULL, 7, 'A', CURRENT_TIMESTAMP),
  (8, 'VISA/Work Permite', NULL, 8, 'A', CURRENT_TIMESTAMP),
  (9, 'Manpower Status', NULL, 9, 'A', CURRENT_TIMESTAMP),
  (10, 'Flight', NULL, 10, 'A', CURRENT_TIMESTAMP);
