# 07 — Definitive report catalog

`report-catalog.csv` contains **17 ACTIVE DB reports**, **6 ACTIVE print outputs**, **7 LEGACY controller methods**, and **1 orphaned LEGACY view**. Every row includes permission, route/action/view, source query, tables, filters, output columns, calculations, totals, ordering, pagination, PDF/Excel/CSV behavior, business rules, defect, scope, disposition, and evidence classification.

All 17 DB rows are active and have confirmed routes, controller actions, and Blade views, including Teacher Schedule and Exam Result Sheet. Candidate profile export is ACTIVE because `GET my/profile/export` and `profileExport()` are executable, although `debug=true` makes its PDF branch unreachable.

## Confirmed cross-report defects
- `reportProcess` uses bitwise `|` for the EXCEL/CSV branch.
- Both EXCEL and CSV requests generate XLSX with only generic `id,name`; CSV implementation is commented out.
- Reports use unpaginated `get()`/in-memory collections.
- Exception text is echoed or returned by several paths.
- Agent Report hard-codes five business metrics/balances to zero.
- Flight Schedule filters payment status by rendered HTML text after loading rows.

LEGACY rows cover every non-current controller report method and the orphaned supplier-ledger Blade. They are not target requirements without explicit owner approval.
