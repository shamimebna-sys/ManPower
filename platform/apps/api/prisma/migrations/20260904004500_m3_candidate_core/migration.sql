-- CreateSchema
CREATE SCHEMA IF NOT EXISTS "candidate";

-- CreateSchema
CREATE SCHEMA IF NOT EXISTS "migration";

-- CreateTable
CREATE TABLE "candidate"."candidates" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "code" VARCHAR(100),
    "name" VARCHAR(100),
    "email" VARCHAR(250),
    "secondary_email" VARCHAR(250),
    "mobile" VARCHAR(250),
    "secondary_mobile" VARCHAR(250),
    "emergency_mobile" VARCHAR(250),
    "bid" VARCHAR(250),
    "bid_file_ref" TEXT,
    "nid" VARCHAR(250),
    "nid_file_ref" TEXT,
    "passport_no" VARCHAR(250),
    "passport_issue_date" DATE,
    "passport_expire_date" DATE,
    "passport_file_ref" TEXT,
    "dob" DATE,
    "full_photo_file_ref" TEXT,
    "half_photo_file_ref" TEXT,
    "father_name" VARCHAR(250),
    "mother_name" VARCHAR(250),
    "nationality" VARCHAR(250),
    "gender" VARCHAR(20),
    "blood_group" VARCHAR(20),
    "present_address_house" VARCHAR(200),
    "present_address_road" VARCHAR(20),
    "present_address_village" VARCHAR(20),
    "present_address_post" VARCHAR(20),
    "present_address_thana_id" BIGINT,
    "present_address_district_id" BIGINT,
    "present_address_division_id" BIGINT,
    "permanent_address_house" VARCHAR(200),
    "permanent_address_road" VARCHAR(20),
    "permanent_address_village" VARCHAR(20),
    "permanent_address_post" VARCHAR(20),
    "permanent_address_thana_id" BIGINT,
    "permanent_address_district_id" BIGINT,
    "permanent_address_division_id" BIGINT,
    "basic_info_career" TEXT,
    "basic_info_special" TEXT,
    "other_skills" TEXT,
    "balance" DECIMAL(20,6) NOT NULL DEFAULT 0,
    "cv_file_ref" TEXT,
    "status" VARCHAR(5) NOT NULL DEFAULT 'A',
    "created_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ(6) NOT NULL,
    "agent_id" BIGINT,
    "class_group_id" BIGINT,
    "remarks" VARCHAR(255),
    "replacement_remarks" TEXT,
    "admission_payment_id" BIGINT,
    "final_group_payment_id" BIGINT,
    "medical_fee_payment_id" BIGINT,
    "abroad_ex" INTEGER NOT NULL DEFAULT 0,
    "local_ex" INTEGER NOT NULL DEFAULT 0,
    "drive_link" VARCHAR(250),
    "facebook_link" VARCHAR(250),
    "youtube_link" VARCHAR(250),
    "linkedin_link" VARCHAR(250),
    "twitter_link" VARCHAR(250),
    "instagram_link" VARCHAR(250),
    "position" VARCHAR(250),
    "skill_certificate_file_ref" TEXT,
    "height" VARCHAR(100),
    "weight" VARCHAR(100),
    "marital_status" VARCHAR(100),
    "expertise" TEXT,
    "skills" TEXT,
    "extra_curricular" TEXT,
    "interest" TEXT,
    "attribute" TEXT,
    "companier_id" BIGINT,
    "agencier_id" BIGINT,
    "stamp_file_ref" TEXT,
    "position_id" BIGINT,
    "company_status" VARCHAR(200) DEFAULT 'ACTIVE',
    "sub_agent_id" BIGINT,
    "country_id" BIGINT,
    "replaced_by_legacy_id" BIGINT,

    CONSTRAINT "candidates_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "migration"."legacy_key_map" (
    "id" UUID NOT NULL DEFAULT gen_random_uuid(),
    "source_system" VARCHAR(100) NOT NULL,
    "source_table" VARCHAR(120) NOT NULL,
    "source_id" VARCHAR(64) NOT NULL,
    "target_type" VARCHAR(120) NOT NULL,
    "target_id" VARCHAR(64) NOT NULL,
    "migration_run_id" VARCHAR(100),
    "source_row_hash" VARCHAR(128),
    "migrated_at" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "legacy_key_map_pkey" PRIMARY KEY ("id")
);

-- CreateIndex
CREATE UNIQUE INDEX "candidates_code_key" ON "candidate"."candidates"("code");

-- CreateIndex
CREATE UNIQUE INDEX "candidates_email_key" ON "candidate"."candidates"("email");

-- CreateIndex
CREATE UNIQUE INDEX "candidates_mobile_key" ON "candidate"."candidates"("mobile");

-- CreateIndex
CREATE UNIQUE INDEX "candidates_passport_no_key" ON "candidate"."candidates"("passport_no");

-- CreateIndex
CREATE INDEX "candidates_status_idx" ON "candidate"."candidates"("status");

-- CreateIndex
CREATE INDEX "candidates_name_idx" ON "candidate"."candidates"("name");

-- CreateIndex
CREATE INDEX "candidates_nid_idx" ON "candidate"."candidates"("nid");

-- CreateIndex
CREATE INDEX "candidates_created_at_idx" ON "candidate"."candidates"("created_at");

-- CreateIndex
CREATE UNIQUE INDEX "legacy_key_map_source_system_source_table_source_id_key"
  ON "migration"."legacy_key_map"("source_system", "source_table", "source_id");

-- CreateIndex
CREATE INDEX "legacy_key_map_target_type_target_id_idx"
  ON "migration"."legacy_key_map"("target_type", "target_id");
