# Production dump evidence

The production SQL dump `u410970153_eujobbd.sql` is intentionally excluded from Git.

Reasons:

- The file is approximately 223 MB (GitHub blob limit is 100 MB).
- It contains production data and must not be published in the repository.

Recorded SHA-256 (do not change unless the authorized dump is replaced in the controlled migration environment):

`D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`

That value is committed in `production-dump.sha256`. CI verifies this evidence file only. It does **not** re-hash dump bytes.

Actual dump-byte verification (`Get-FileHash` / `sha256sum` of the SQL file) must be performed in the controlled migration environment where the dump is available. Do not import production data into CI, local development, or PostgreSQL 16 integration.
