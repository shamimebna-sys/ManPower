# M7 IMPLEMENTATION GO

**Issued:** 2026-09-07  
**Issuer:** Owner (this authorization)  
**Baseline:** M6 CLOSED / VERIFIED. M7 PRE-GATE COMPLETE. A01 APPROVED WITH CONDITIONS. A02 CLOSED. Design review **READY FOR IMPLEMENTATION GO**.  
**Binding decisions:** `docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`

This document **issues** M7 implementation authorization for **System A only**.

---

## Two separate states (do not collapse)

| State | Status |
|---|---|
| **1. M7 design review** | **READY FOR IMPLEMENTATION GO** |
| **2. M7 IMPLEMENTATION GO issuance** | **ISSUED** 2026-09-07 |

**M7 IMPLEMENTATION GO: ISSUED**  
**M7 IMPLEMENTATION: AUTHORIZED (System A)**  
**M8: OUT OF SCOPE**  
**M13 PRODUCTION MIGRATION: NOT AUTHORIZED**  
**A01-09 production sign-off: NOT OCCURRED**  
**Production cutover: NOT AUTHORIZED**

---

## Authorized

Implement M7 System A: wallets, payment requests, double-entry journals, historical reconstruction tooling (not production load), administrative FX rate entry, finance audit, reconciliation tooling/tests, required UI, and the approved finance permission keys/grants.

## Not authorized

- M8 (invoices, receipts, tickets, FIN-BUG-07/09, A↔B/C settlement)
- Production database import / M13
- Inventing teacher wallets, reserved balances, or statutory GL numbers
- Silent repair of historical FKs or rates
- Backup UNION with live `payments`
- Using `euro_to_bdt_rates` as authority
- Laravel / `src/` changes

---

## Binding owner decisions (verbatim effect)

**DR-H1 A:** every qualifying live `payments.status='A'` row (after A01 exclusions) → exactly one reconstructed journal. Same id must not appear on an opening-balance posting. A01-10 is recon control only. Residual opening (if any) still A01-09. Cache-only balances are not journals.

**DR-FX1:** new-post `R` from authorized administrative rate-entry. Historical `R` = `payments.exchange_rate`. `euro_to_bdt_rates` non-authoritative.

---

## FINAL STATUS

**M7 IMPLEMENTATION GO = ISSUED**  
**M7 IMPLEMENTATION = AUTHORIZED (System A)**  
**M8 = OUT OF SCOPE**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**
