-- CreateSchema
CREATE SCHEMA IF NOT EXISTS "operations";

-- CreateSchema
CREATE SCHEMA IF NOT EXISTS "workflow";

-- AlterTable
ALTER TABLE "iam"."users" ADD COLUMN "teacher_id" UUID;

-- AlterTable
ALTER TABLE "candidate"."candidates" ADD COLUMN "class_group_ref_id" UUID;

-- CreateTable
CREATE TABLE "operations"."teachers" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "code" VARCHAR(100),
    "name" VARCHAR(255),
    "email" VARCHAR(255),
    "secondary_email" VARCHAR(255),
    "mobile" VARCHAR(255),
    "secondary_mobile" VARCHAR(255),
    "emergency_mobile" VARCHAR(255),
    "nid" VARCHAR(255),
    "passport_no" VARCHAR(255),
    "dob" DATE,
    "gender" VARCHAR(20),
    "nationality" VARCHAR(20),
    "father_name" VARCHAR(50),
    "mother_name" VARCHAR(50),
    "blood_group" VARCHAR(20),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "teachers_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "operations"."class_groups" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "name" VARCHAR(250),
    "description" VARCHAR(250),
    "code" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "fee_amount" DECIMAL(20,6) NOT NULL DEFAULT 0,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "class_groups_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "operations"."class_schedules" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "teacher_id" UUID,
    "class_group_id" UUID,
    "subject" VARCHAR(255),
    "start_time" TIME(6),
    "end_time" TIME(6),
    "week_day" VARCHAR(255),
    "status" CHAR(1) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "class_schedules_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "operations"."exams" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "name" TEXT,
    "exam_date" DATE,
    "exam_time" TIME(6),
    "exam_link" TEXT,
    "remarks" TEXT,
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "exams_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "operations"."exam_class_groups" (
    "exam_id" UUID NOT NULL,
    "class_group_id" UUID NOT NULL,
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "exam_class_groups_pkey" PRIMARY KEY ("exam_id","class_group_id")
);

-- CreateTable
CREATE TABLE "operations"."exam_results" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "exam_id" UUID NOT NULL,
    "candidate_id" UUID NOT NULL,
    "class_group_id" UUID,
    "abroad_ex" INTEGER,
    "local_ex" INTEGER,
    "bl" INTEGER,
    "skill" INTEGER,
    "english" INTEGER,
    "result" VARCHAR(5),
    "remarks" VARCHAR(255),
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,

    CONSTRAINT "exam_results_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "workflow"."manpower_training_events" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_legacy_id" BIGINT,
    "candidate_id" UUID NOT NULL,
    "certificate_issue_date" DATE,
    "certificate_expire_date" DATE,
    "certificate_file_ref" VARCHAR(250),
    "manpower_file_ref" VARCHAR(250),
    "finger_print_file_ref" VARCHAR(250),
    "training_start_date" DATE,
    "training_end_date" DATE,
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "created_by_legacy_id" BIGINT,
    "updated_by_legacy_id" BIGINT,

    CONSTRAINT "manpower_training_events_pkey" PRIMARY KEY ("id")
);

-- CreateIndex
CREATE UNIQUE INDEX "teachers_source_legacy_id_key" ON "operations"."teachers"("source_legacy_id");

-- CreateIndex
CREATE INDEX "teachers_status_idx" ON "operations"."teachers"("status");

-- CreateIndex
CREATE INDEX "teachers_name_idx" ON "operations"."teachers"("name");

-- CreateIndex
CREATE UNIQUE INDEX "class_groups_source_legacy_id_key" ON "operations"."class_groups"("source_legacy_id");

-- CreateIndex
CREATE INDEX "class_groups_status_idx" ON "operations"."class_groups"("status");

-- CreateIndex
CREATE INDEX "class_groups_name_idx" ON "operations"."class_groups"("name");

-- CreateIndex
CREATE UNIQUE INDEX "class_schedules_source_legacy_id_key" ON "operations"."class_schedules"("source_legacy_id");

-- CreateIndex
CREATE INDEX "class_schedules_teacher_id_idx" ON "operations"."class_schedules"("teacher_id");

-- CreateIndex
CREATE INDEX "class_schedules_class_group_id_idx" ON "operations"."class_schedules"("class_group_id");

-- CreateIndex
CREATE INDEX "class_schedules_status_idx" ON "operations"."class_schedules"("status");

-- CreateIndex
CREATE UNIQUE INDEX "exams_source_legacy_id_key" ON "operations"."exams"("source_legacy_id");

-- CreateIndex
CREATE INDEX "exams_status_idx" ON "operations"."exams"("status");

-- CreateIndex
CREATE INDEX "exams_exam_date_idx" ON "operations"."exams"("exam_date");

-- CreateIndex
CREATE INDEX "exam_class_groups_class_group_id_idx" ON "operations"."exam_class_groups"("class_group_id");

-- CreateIndex
CREATE UNIQUE INDEX "exam_results_source_legacy_id_key" ON "operations"."exam_results"("source_legacy_id");

-- CreateIndex
CREATE UNIQUE INDEX "exam_results_exam_id_candidate_id_key" ON "operations"."exam_results"("exam_id", "candidate_id");

-- CreateIndex
CREATE INDEX "exam_results_candidate_id_idx" ON "operations"."exam_results"("candidate_id");

-- CreateIndex
CREATE INDEX "exam_results_class_group_id_idx" ON "operations"."exam_results"("class_group_id");

-- CreateIndex
CREATE INDEX "exam_results_status_idx" ON "operations"."exam_results"("status");

-- CreateIndex
CREATE INDEX "exam_results_result_idx" ON "operations"."exam_results"("result");

-- CreateIndex
CREATE UNIQUE INDEX "manpower_training_events_source_legacy_id_key" ON "workflow"."manpower_training_events"("source_legacy_id");

-- CreateIndex
CREATE INDEX "manpower_training_events_candidate_id_created_at_idx" ON "workflow"."manpower_training_events"("candidate_id", "created_at");

-- CreateIndex
CREATE INDEX "manpower_training_events_candidate_id_status_idx" ON "workflow"."manpower_training_events"("candidate_id", "status");

-- CreateIndex
CREATE UNIQUE INDEX "users_teacher_id_key" ON "iam"."users"("teacher_id");

-- CreateIndex
CREATE INDEX "users_teacher_id_idx" ON "iam"."users"("teacher_id");

-- CreateIndex
CREATE INDEX "candidates_class_group_ref_id_idx" ON "candidate"."candidates"("class_group_ref_id");

-- AddForeignKey
ALTER TABLE "iam"."users" ADD CONSTRAINT "users_teacher_id_fkey" FOREIGN KEY ("teacher_id") REFERENCES "operations"."teachers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "candidate"."candidates" ADD CONSTRAINT "candidates_class_group_ref_id_fkey" FOREIGN KEY ("class_group_ref_id") REFERENCES "operations"."class_groups"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "operations"."class_schedules" ADD CONSTRAINT "class_schedules_teacher_id_fkey" FOREIGN KEY ("teacher_id") REFERENCES "operations"."teachers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "operations"."class_schedules" ADD CONSTRAINT "class_schedules_class_group_id_fkey" FOREIGN KEY ("class_group_id") REFERENCES "operations"."class_groups"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "operations"."exam_class_groups" ADD CONSTRAINT "exam_class_groups_exam_id_fkey" FOREIGN KEY ("exam_id") REFERENCES "operations"."exams"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "operations"."exam_class_groups" ADD CONSTRAINT "exam_class_groups_class_group_id_fkey" FOREIGN KEY ("class_group_id") REFERENCES "operations"."class_groups"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "operations"."exam_results" ADD CONSTRAINT "exam_results_exam_id_fkey" FOREIGN KEY ("exam_id") REFERENCES "operations"."exams"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "operations"."exam_results" ADD CONSTRAINT "exam_results_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "operations"."exam_results" ADD CONSTRAINT "exam_results_class_group_id_fkey" FOREIGN KEY ("class_group_id") REFERENCES "operations"."class_groups"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "workflow"."manpower_training_events" ADD CONSTRAINT "manpower_training_events_candidate_id_fkey" FOREIGN KEY ("candidate_id") REFERENCES "candidate"."candidates"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "operations"."exam_results"
    ADD CONSTRAINT "exam_results_result_check" CHECK ("result" IS NULL OR "result" IN ('PASS', 'FAIL'));

ALTER TABLE "operations"."exam_results"
    ADD CONSTRAINT "exam_results_abroad_ex_check" CHECK ("abroad_ex" IS NULL OR ("abroad_ex" >= 0 AND "abroad_ex" <= 100));

ALTER TABLE "operations"."exam_results"
    ADD CONSTRAINT "exam_results_local_ex_check" CHECK ("local_ex" IS NULL OR ("local_ex" >= 0 AND "local_ex" <= 100));

ALTER TABLE "operations"."exam_results"
    ADD CONSTRAINT "exam_results_bl_check" CHECK ("bl" IS NULL OR ("bl" >= 0 AND "bl" <= 100));

ALTER TABLE "operations"."exam_results"
    ADD CONSTRAINT "exam_results_skill_check" CHECK ("skill" IS NULL OR ("skill" >= 0 AND "skill" <= 100));

ALTER TABLE "operations"."exam_results"
    ADD CONSTRAINT "exam_results_english_check" CHECK ("english" IS NULL OR ("english" >= 0 AND "english" <= 100));
