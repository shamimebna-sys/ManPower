# M7 A02 — owner decision request

**Document type:** OWNER DECISION REQUEST ONLY  
**Date opened:** 2026-09-07  

A02 evidence extraction is **COMPLETE**. This form does **not** close A02.  
This form does **not** issue M7 IMPLEMENTATION GO.  
No application code, Prisma, migration, permissions, grants, CI, production data, or Laravel change.

Evidence: `docs/M7-A02-OWNER-DECISION-WORKSHEET.md`  
Recorded A01 / partial A02: `docs/M7-A01-A02-OWNER-DECISIONS.md`

Mark exactly one option per question. Do not treat answers as implementation approval.

Even after answers are recorded, **A02 becomes CLOSED only when all required decisions are complete and internally consistent.** M7 IMPLEMENTATION GO remains **NOT ISSUED** until A02 is formally closed.

---

## Owner identity (required when signing)

| Field | Value |
|---|---|
| Approver name | |
| Date | |
| Role / title | |

---

## A02-01 — FEE DEBIT CURRENCY

Verified evidence:

- `payment_requests` has NO currency column.
- Legacy fee approval writes `payment.currency_id = 2`.
- Currency ID 2 is not sufficient evidence that the business fee currency should be EUR.
- A01-10 opening balance uses the STORED payment currency.
- Historical transactions must not be rewritten.

Owner must choose ONE:

| | |
|---|---|
| BDT | [ ] |
| EUR | [ ] |
| Other | [ ] |

If "Other", specify ISO currency:

________________________________________

---

## A02-02 — W1 FX FORMULA / DIRECTION

Verified legacy calculation patterns include:

- (A) amount / exchange_rate
- (B) amount without FX
- (C) amount / conditional currency/rate logic

R1 currently fails.

The correct target direction cannot be finalized until A02-01 fee currency is decided.

Owner decision:

| | |
|---|---|
| Approve W1 formula after fee currency decision | [ ] |
| Require another FX rule | [ ] |
| HOLD | [ ] |

If another rule:

________________________________________

---

## A02-03 — A16 PANELTY FEE

Verified:

- Exact legacy title: `Panelty Fee`
- Appears on 12 `payment_requests` rows.
- 10 are status A.
- 2 are status P.
- The number 12 is a ROW COUNT, NOT a bill code.
- No numeric Panelty Fee code was found.
- Existing proposed `bill_title_code` enum does not contain Panelty Fee.
- Existing enum proposal is unsigned.

### Concept

| | |
|---|---|
| APPROVE keeping `Panelty Fee` as a distinct bill-title concept | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |
| HOLD | [ ] |

### Exact target code

| | |
|---|---|
| No code required yet | [ ] |
| Code: __________ | [ ] |
| HOLD | [ ] |

If CHANGE or a code is supplied, write it here:

________________________________________

---

## A02-04 — A17 TEACHER PAYMENT FLOW

Verified:

- Design Gate identifies FIN-BUG-05: "Teacher payment flow: in-use or deprecated?"
- Legacy teacher lookup uses `$agent_id`.
- Production dump has `payments.teacher_id != 0` = 0 rows.
- No evidence currently proves the flow is actively used.
- No owner decision exists yet.

Owner decision:

| | |
|---|---|
| RETAIN teacher payment flow | [ ] |
| DEPRECATE teacher payment flow | [ ] |
| REMOVE teacher payment flow | [ ] |
| HOLD / investigate further | [ ] |

If retaining, required business purpose:

________________________________________

---

## A02-05 — A18 ZERO-DR REJECTION ROW

Verified:

- Legacy rejection code inserts a zero-amount DR row.
- Production dump contains 0 such payment rows.
- There are 10 rejected payment requests.
- Design Gate asks: "confirm not intentional, approve removal."

Owner decision:

| | |
|---|---|
| Confirm zero-DR rejection row is NOT intentional | [ ] |
| Confirm zero-DR rejection row IS intentional | [ ] |
| HOLD / investigate further | [ ] |

If NOT intentional:

| | |
|---|---|
| Approve removal in target architecture | [ ] |

If intentional, explain:

________________________________________

---

## A02-06 — bill_code=101

Current owner decision remains:

**APPROVE WITH CONDITION**

Condition:

- Preserve legacy lineage only.
- Do not map 101 to a bill title without explicit evidence.
- Do not invent a target business meaning.
- No target implementation until mapping is explicitly approved.

Owner confirmation:

| | |
|---|---|
| CONFIRM | [ ] |
| CHANGE | [ ] |

If CHANGE:

________________________________________

---

## Rule

Do **not** convert these answers into implementation approval.

A02 stays **OPEN** until every required decision above is complete and internally consistent.

---

## Current status

**M6 = CLOSED / VERIFIED**  
**M7 PRE-GATE = COMPLETE**  
**A01 = APPROVED WITH CONDITIONS**  
**A02 = OPEN**

**M7 IMPLEMENTATION GO = NOT ISSUED**  
**M7 IMPLEMENTATION = NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS = NOT AUTHORIZED**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**
