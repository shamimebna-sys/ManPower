-- M4 candidate supporting profile tables. No production data is imported.

CREATE TABLE "candidate"."educations" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "candidate_id" UUID NOT NULL,
    "source_user_id" BIGINT,
    "exam_name" VARCHAR(250),
    "institute_name" VARCHAR(250),
    "subject_group_major" VARCHAR(250),
    "education_label" VARCHAR(250),
    "start_date" DATE,
    "end_date" DATE,
    "duration_year" INTEGER,
    "result_type" VARCHAR(250),
    "result" VARCHAR(250),
    "achievements" VARCHAR(250),
    "certificate_file_ref" VARCHAR(250),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,
    "board_name" VARCHAR(200),
    "scale" VARCHAR(200),
    "passing_year" VARCHAR(200),

    CONSTRAINT "educations_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "candidate"."experiences" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "candidate_id" UUID NOT NULL,
    "source_user_id" BIGINT,
    "company_name" VARCHAR(250),
    "company_address" VARCHAR(250),
    "country_ref" VARCHAR(250),
    "designation" VARCHAR(250),
    "department" VARCHAR(250),
    "start_date" DATE,
    "end_date" DATE,
    "responsibilities" TEXT,
    "expertise" TEXT,
    "descriptions" TEXT,
    "achievements" VARCHAR(250),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "experiences_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "candidate"."skills" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "candidate_id" UUID NOT NULL,
    "source_user_id" BIGINT,
    "title" VARCHAR(250),
    "institute_name" VARCHAR(250),
    "details" VARCHAR(250),
    "result_score" DECIMAL(10,2),
    "exam_score" DECIMAL(10,2),
    "certificate_file_ref" VARCHAR(250),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "skills_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "candidate"."skill_list" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "candidate_id" UUID NOT NULL,
    "source_user_id" BIGINT,
    "skill_name" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "skill_list_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "candidate"."language_list" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "candidate_id" UUID NOT NULL,
    "source_user_id" BIGINT,
    "language_name" VARCHAR(255),
    "language_status" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "language_list_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "candidate"."trainings" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "candidate_id" UUID NOT NULL,
    "source_user_id" BIGINT,
    "title" VARCHAR(250),
    "institute_name" VARCHAR(250),
    "topics" VARCHAR(250),
    "start_date" DATE,
    "end_date" DATE,
    "duration" INTEGER,
    "duration_type" VARCHAR(10),
    "country_ref" VARCHAR(250),
    "descriptions" VARCHAR(250),
    "achievements" VARCHAR(250),
    "certificate_file_ref" VARCHAR(250),
    "address" VARCHAR(200),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "trainings_pkey" PRIMARY KEY ("id")
);

ALTER TABLE "candidate"."educations"
  ADD CONSTRAINT "educations_candidate_id_fkey"
  FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "candidate"."experiences"
  ADD CONSTRAINT "experiences_candidate_id_fkey"
  FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "candidate"."skills"
  ADD CONSTRAINT "skills_candidate_id_fkey"
  FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "candidate"."skill_list"
  ADD CONSTRAINT "skill_list_candidate_id_fkey"
  FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "candidate"."language_list"
  ADD CONSTRAINT "language_list_candidate_id_fkey"
  FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "candidate"."trainings"
  ADD CONSTRAINT "trainings_candidate_id_fkey"
  FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE INDEX "educations_candidate_id_created_at_idx" ON "candidate"."educations"("candidate_id", "created_at");
CREATE INDEX "educations_candidate_id_status_idx" ON "candidate"."educations"("candidate_id", "status");
CREATE INDEX "experiences_candidate_id_created_at_idx" ON "candidate"."experiences"("candidate_id", "created_at");
CREATE INDEX "experiences_candidate_id_status_idx" ON "candidate"."experiences"("candidate_id", "status");
CREATE INDEX "skills_candidate_id_created_at_idx" ON "candidate"."skills"("candidate_id", "created_at");
CREATE INDEX "skills_candidate_id_status_idx" ON "candidate"."skills"("candidate_id", "status");
CREATE INDEX "skill_list_candidate_id_created_at_idx" ON "candidate"."skill_list"("candidate_id", "created_at");
CREATE INDEX "language_list_candidate_id_created_at_idx" ON "candidate"."language_list"("candidate_id", "created_at");
CREATE INDEX "trainings_candidate_id_created_at_idx" ON "candidate"."trainings"("candidate_id", "created_at");
CREATE INDEX "trainings_candidate_id_status_idx" ON "candidate"."trainings"("candidate_id", "status");
