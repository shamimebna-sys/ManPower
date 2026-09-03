# 05 — Final RBAC matrix

- Roles from dump: **13**
- Permissions from dump: **246**
- Explicit grants: **714**
- Hard-coded numeric-role occurrences cataloged: **46**

Normative files: `rbac-permissions.csv`, `rbac-role-grants.csv`, `rbac-hardcoded-role-ids.csv`.

Legacy grants are CONFIRMED but route authorization is partly Voyager/BREAD dynamic. Therefore each permission row labels route/controller gaps UNVERIFIED. Target authorization uses stable semantic keys, explicit row scopes (self/agent/sub-agent/agency/company/all), separate document download and report execute/export grants, deny-by-default, and policy tests. Numeric IDs 1, 2, and 100–110 are compatibility inputs only; runtime policies must use stable keys. The dump contains 13 actual role records even though code also references stale/extra IDs; retain unknown IDs in a compatibility map and block deployment until each is resolved.
