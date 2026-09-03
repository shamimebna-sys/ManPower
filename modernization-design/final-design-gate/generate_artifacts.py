"""Generate evidence-backed design-gate documentation.

This is documentation tooling, not application code. It reads only the canonical
legacy source and offline SQL dump and writes only beside this file.
"""
from __future__ import annotations

import csv
import hashlib
import json
import os
import re
from collections import Counter, defaultdict
from pathlib import Path

ROOT = Path(r"C:\Users\Muaza it\Desktop\ManPower")
OUT = Path(__file__).resolve().parent
SQL_PATH = ROOT / "docs" / "database-audit" / "u410970153_eujobbd.sql"
SRC = ROOT / "src"


def write(name: str, content: str) -> None:
    (OUT / name).write_text(content.rstrip() + "\n", encoding="utf-8")


def write_csv(name: str, rows: list[dict], fields: list[str] | None = None) -> None:
    if fields is None:
        fields = list(rows[0]) if rows else []
    with (OUT / name).open("w", newline="", encoding="utf-8-sig") as f:
        w = csv.DictWriter(f, fieldnames=fields, extrasaction="ignore")
        w.writeheader()
        w.writerows(rows)


def split_sql_tuple(line: str) -> list[str]:
    s = line.strip()
    if s.startswith("("):
        s = s[1:]
    if s.endswith(";"):
        s = s[:-1]
    if s.endswith(","):
        s = s[:-1]
    if s.endswith(")"):
        s = s[:-1]
    values, buf, quoted, esc = [], [], False, False
    for ch in s:
        if esc:
            buf.append(ch)
            esc = False
        elif ch == "\\" and quoted:
            buf.append(ch)
            esc = True
        elif ch == "'":
            quoted = not quoted
            buf.append(ch)
        elif ch == "," and not quoted:
            values.append("".join(buf).strip())
            buf = []
        else:
            buf.append(ch)
    values.append("".join(buf).strip())
    return values


def clean_sql(v: str):
    if v.upper() == "NULL":
        return None
    if len(v) >= 2 and v[0] == "'" and v[-1] == "'":
        return v[1:-1].replace("\\'", "'").replace("\\\\", "\\")
    if re.fullmatch(r"-?\d+", v):
        return int(v)
    return v


sql_text = SQL_PATH.read_text(encoding="utf-8", errors="replace")
sql_lines = sql_text.splitlines()

# Schema definitions
tables: dict[str, dict] = {}
create_re = re.compile(
    r"CREATE TABLE `([^`]+)` \(\r?\n(.*?)\r?\n\) ENGINE=([^\s;]+)(.*?);",
    re.S,
)
for m in create_re.finditer(sql_text):
    name, body, engine, options = m.groups()
    columns = []
    for raw in body.splitlines():
        cm = re.match(r"\s*`([^`]+)`\s+(.+?)(?:,)?$", raw)
        if not cm:
            continue
        col, definition = cm.groups()
        definition = definition.rstrip(",")
        type_match = re.match(r"([a-zA-Z]+(?:\([^)]*\))?(?:\s+UNSIGNED)?)", definition)
        source_type = type_match.group(1) if type_match else definition.split()[0]
        default_match = re.search(r"\bDEFAULT\s+((?:'[^']*')|NULL|[^\s,]+)", definition, re.I)
        columns.append(
            {
                "name": col,
                "source_type": source_type,
                "nullable": "NO" if "NOT NULL" in definition.upper() else "YES",
                "default": default_match.group(1) if default_match else "NO DEFAULT",
                "definition": definition,
            }
        )
    tables[name] = {
        "name": name,
        "engine": engine,
        "options": options.strip(),
        "columns": columns,
        "pk": [],
        "indexes": [],
        "fks": [],
        "rows": 0,
    }

# ALTER-based keys and constraints.
for am in re.finditer(r"ALTER TABLE `([^`]+)`\r?\n(.*?);", sql_text, re.S):
    table, body = am.groups()
    if table not in tables:
        continue
    pm = re.search(r"ADD PRIMARY KEY \(([^)]+)\)", body)
    if pm:
        tables[table]["pk"] = re.findall(r"`([^`]+)`", pm.group(1))
    for im in re.finditer(r"ADD (UNIQUE KEY|KEY) `([^`]+)` \(([^)]+)\)", body):
        tables[table]["indexes"].append(
            {
                "kind": "UNIQUE" if im.group(1) == "UNIQUE KEY" else "INDEX",
                "name": im.group(2),
                "columns": re.findall(r"`([^`]+)`", im.group(3)),
            }
        )
    for fm in re.finditer(
        r"ADD CONSTRAINT `([^`]+)` FOREIGN KEY \(([^)]+)\) REFERENCES `([^`]+)` \(([^)]+)\)([^,\r\n]*)",
        body,
    ):
        tables[table]["fks"].append(
            {
                "name": fm.group(1),
                "columns": re.findall(r"`([^`]+)`", fm.group(2)),
                "ref_table": fm.group(3),
                "ref_columns": re.findall(r"`([^`]+)`", fm.group(4)),
                "actions": fm.group(5).strip(),
            }
        )

# Exact INSERT tuple counts and safe metadata rows.
target_extract = {
    "roles",
    "permissions",
    "permission_role",
    "reports",
    "live_status",
    "data_types",
    "data_rows",
}
extracted: dict[str, list[list]] = defaultdict(list)
insert_columns: dict[str, list[str]] = {}
active_table = None
active_cols: list[str] = []
for line in sql_lines:
    ins = re.match(r"INSERT INTO `([^`]+)` \((.*?)\) VALUES$", line)
    if ins:
        active_table = ins.group(1)
        active_cols = re.findall(r"`([^`]+)`", ins.group(2))
        insert_columns[active_table] = active_cols
        continue
    if active_table and line.startswith("("):
        if active_table in tables:
            tables[active_table]["rows"] += 1
        if active_table in target_extract:
            vals = [clean_sql(x) for x in split_sql_tuple(line)]
            if len(vals) == len(active_cols):
                extracted[active_table].append(vals)
        if line.rstrip().endswith(";"):
            active_table = None
            active_cols = []
    elif active_table and line and not line.startswith("--") and not line.startswith("("):
        # Defensive reset: dump tuples are one physical line.
        active_table = None
        active_cols = []


def records(table: str) -> list[dict]:
    cols = insert_columns.get(table, [])
    return [dict(zip(cols, row)) for row in extracted.get(table, [])]


# Canonical source inventory, excluding dependencies and runtime data.
source_files: list[tuple[Path, str]] = []
allowed_ext = {".php", ".js", ".json", ".blade.php"}
for base, dirs, files in os.walk(SRC):
    dirs[:] = [d for d in dirs if d not in {"vendor", "node_modules", "storage", ".git"}]
    for fn in files:
        p = Path(base) / fn
        if p.suffix.lower() in {".php", ".js", ".json"}:
            try:
                source_files.append((p, p.read_text(encoding="utf-8", errors="replace")))
            except OSError:
                pass


def relpath(p: Path) -> str:
    return p.relative_to(ROOT).as_posix()


def kind_for_path(p: Path) -> str:
    s = p.as_posix().lower()
    for label, token in [
        ("MODEL", "/models/"),
        ("CONTROLLER", "/controllers/"),
        ("SERVICE", "/services/"),
        ("HELPER", "/helpers/"),
        ("REPORT_VIEW", "/reports/"),
        ("ROUTE", "/routes/"),
    ]:
        if token in s:
            return label
    return "OTHER"


def pascal(name: str) -> str:
    return "".join(x.capitalize() for x in re.split(r"[_\-\s]+", name))


source_refs: dict[str, list[dict]] = defaultdict(list)
for table in tables:
    terms = {table, table.rstrip("s"), pascal(table), pascal(table.rstrip("s"))}
    pattern = re.compile(r"(?<![A-Za-z0-9_])(?:" + "|".join(re.escape(x) for x in terms if x) + r")(?![A-Za-z0-9_])", re.I)
    for p, content in source_files:
        match = pattern.search(content)
        if match:
            line_no = content.count("\n", 0, match.start()) + 1
            source_refs[table].append(
                {"table": table, "kind": kind_for_path(p), "path": relpath(p), "line": line_no}
            )

# Resolve direct application models by explicit $table or Laravel convention.
# This closes false negatives such as Agency -> agencies and Currency -> currencies.
model_exceptions = {"Exprience": "experiences"}
for p, content in source_files:
    if "/models/" not in p.as_posix().lower():
        continue
    cm = re.search(r"\bclass\s+([A-Za-z_][A-Za-z0-9_]*)", content)
    if not cm:
        continue
    cls = cm.group(1)
    tm = re.search(r"""protected\s+\$table\s*=\s*['"]([^'"]+)['"]""", content)
    if tm:
        candidate_table = tm.group(1)
    else:
        snake = re.sub(r"(?<!^)(?=[A-Z])", "_", cls).lower()
        if snake.endswith("y"):
            candidate_table = snake[:-1] + "ies"
        elif snake.endswith(("s", "x", "ch", "sh")):
            candidate_table = snake + "es"
        else:
            candidate_table = snake + "s"
        candidate_table = model_exceptions.get(cls, candidate_table)
    if candidate_table in tables:
        model_ref = {"table": candidate_table, "kind": "MODEL", "path": relpath(p), "line": content.count("\n", 0, cm.start()) + 1}
        if not any(r["kind"] == "MODEL" and r["path"] == model_ref["path"] for r in source_refs[candidate_table]):
            source_refs[candidate_table].append(model_ref)

# Definitive active report evidence, shared by catalog and table-reference mapping.
REPORT_SPECS = {
    "agent-ledger": {
        "action": "agentLedger", "view": "agent-ledger", "tables": ["agents", "payments"],
        "query": "Agent::where(id=agent_id)->first(); Blade traverses agent payment ledger",
        "filters": "agent_id (required by action)", "ordering": "Blade/relationship ordering UNVERIFIED",
        "rules": "Ledger is scoped to one agent; debit/credit/balance presentation comes from the Blade.",
        "defect": "No explicit date filter or controller ordering; correctness depends on model relationship/view behavior.",
    },
    "agent-report": {
        "action": "agentReport", "view": "agent-report", "tables": ["agents", "candidates", "class_groups"],
        "query": "Bound raw SQL over active agents with correlated candidate/class-group counts",
        "filters": "agent_id,from_date,to_date", "ordering": "agents.name ASC",
        "rules": "Counts total candidates, class_group_id=1 admissions, and groups whose name contains Final.",
        "defect": "selected_cand, visa_updated_cand, payment_due_cand, balance and due_balance are hard-coded zero.",
    },
    "selected-candidate-report": {
        "action": "selectedCandidateReport", "view": "selected-candidate-report",
        "tables": ["candidates", "agents", "agenciers", "companiers", "positions", "visa_immigrations", "flight_schedules"],
        "query": "Active Candidate with agent/agencier/companier/position/visa/flight eager loads",
        "filters": "agent_id,agencier_id,companier_id,from_date,to_date", "ordering": "candidates.name ASC",
        "rules": "Only candidates.status='A'.", "defect": "Report name implies selected status but query only enforces active candidate status.",
    },
    "flight-report": {
        "action": "flightReport", "view": "flight-report",
        "tables": ["candidates", "agents", "agenciers", "companiers", "flight_schedules"],
        "query": "Active Candidate with party and flight relationships",
        "filters": "agent_id,agencier_id,from_date,to_date", "ordering": "candidates.name ASC",
        "rules": "Only candidates.status='A'; dates filter candidate creation, not flight date.",
        "defect": "Date labels can be mistaken for flight dates although controller filters candidates.created_at.",
    },
    "employee-report": {
        "action": "employerReport", "view": "employer-report",
        "tables": ["candidates", "agents", "agenciers", "companiers", "positions", "class_groups", "exam_results"],
        "query": "Active Candidate with party/position/class/latest-exam relationships",
        "filters": "agent_id,agencier_id,companier_id,from_date,to_date", "ordering": "candidates.name ASC",
        "rules": "Only candidates.status='A'.", "defect": "URI/name says Employee Report while action is employerReport and dataset is candidates.",
    },
    "exam-report": {
        "action": "examReport", "view": "exam-report",
        "tables": ["candidates", "agents", "agenciers", "exam_results", "exams"],
        "query": "Active Candidate with agent/agencier/latest exam result",
        "filters": "agent_id,agencier_id,from_date,to_date", "ordering": "candidates.name ASC",
        "rules": "Only candidates.status='A'.", "defect": "Candidate creation dates, rather than exam dates, are filtered.",
    },
    "flight-schedule": {
        "action": "flightSchedule", "view": "flight-schedule",
        "tables": ["candidates", "agents", "agenciers", "companiers", "visa_immigrations", "flight_schedules", "payments"],
        "query": "Candidate with relationships; whereHas flight date; optional in-memory rendered payment-status filter",
        "filters": "agent_id,agencier_id,from_date,to_date,flight_from_date,flight_to_date,payment_status",
        "ordering": "candidates.name ASC",
        "rules": "Flight date range uses related schedules; payment status is derived by CommonClass.",
        "defect": "payment_status filters HTML-rendered text after loading all rows; fragile, unindexed and pagination-incompatible.",
    },
    "teacher-schedule": {
        "action": "teacherSchedule", "view": "teacher-schedule", "tables": ["class_schedules", "teachers", "class_groups"],
        "query": "ClassSchedule with teacher and classGroupRelation",
        "filters": "teacher_id,class_group_id,week_day", "ordering": "week_day ASC,start_time ASC",
        "rules": "Returns all matching schedule rows.", "defect": "Textual week_day ordering may not equal calendar order; domain ordering is UNVERIFIED.",
    },
    "exam-result-sheet": {
        "action": "examResultSheet", "view": "exam-result-sheet", "tables": ["exam_results", "candidates", "exams", "class_groups"],
        "query": "ExamResult with candidate/exam/classGroup",
        "filters": "exam_id,class_group_id,result", "ordering": "created_at DESC",
        "rules": "Result filter is exact legacy value.", "defect": "No required exam guard; an unfiltered request can export every result.",
    },
    "candidate-resume-report": {
        "action": "candidateResumeReport", "view": "candidate-resume-report",
        "tables": ["candidates", "agents", "agenciers", "companiers", "positions", "class_groups", "visa_immigrations", "flight_schedules"],
        "query": "Shared buildCandidateQuery with eager-loaded relationships",
        "filters": "agent_id,agencier_id,companier_id,position_id,class_group_id,gender,status,from_date,to_date",
        "ordering": "candidates.name ASC",
        "rules": "One resume section per matching candidate.", "defect": "Unbounded get() can generate very large resume exports.",
    },
    "candidate-list-report": {
        "action": "candidateListReport", "view": "candidate-list-report",
        "tables": ["candidates", "agents", "agenciers", "companiers", "positions", "class_groups", "visa_immigrations", "flight_schedules"],
        "query": "Shared buildCandidateQuery with eager-loaded relationships",
        "filters": "agent_id,agencier_id,companier_id,position_id,class_group_id,gender,status,from_date,to_date",
        "ordering": "candidates.name ASC",
        "rules": "Status is optional; absent status includes all candidate statuses.", "defect": "Unbounded get(); caller can omit all filters.",
    },
    "company-report": {
        "action": "companyReport", "view": "company-report", "tables": ["companiers", "countries"],
        "query": "Companier with country",
        "filters": "from_date,to_date,status", "ordering": "companiers.name ASC",
        "rules": "Reports master companier records, not candidate company snapshots.", "defect": "User-facing Company label hides the legacy companier/company distinction.",
    },
    "police-clearance-report": {
        "action": "policeClearanceReport", "view": "police-clearance-report", "tables": ["police_clearances", "candidates"],
        "query": "PoliceClearance with candidate",
        "filters": "from_date,to_date,candidate_id,status", "ordering": "created_at DESC",
        "rules": "Exact legacy status; document field is police_clearances.photo_file_path.", "defect": "No defect confirmed beyond unbounded export and unverified status semantics.",
    },
    "arc-report": {
        "action": "arcReport", "view": "arc-report", "tables": ["arcs", "candidates"],
        "query": "Arc with candidate",
        "filters": "from_date,to_date,candidate_id,status", "ordering": "created_at DESC",
        "rules": "ARC document source field is the legacy typo arcs.acr_file_path.", "defect": "Legacy acr_file_path typo must remain lineage-only, not become target naming.",
    },
    "visa-report": {
        "action": "visaReport", "view": "visa-report", "tables": ["visa_immigrations", "candidates"],
        "query": "VisaImmigration with candidate",
        "filters": "from_date,to_date,candidate_id,status", "ordering": "created_at DESC",
        "rules": "Exact legacy status.", "defect": "No confirmed calculation defect; unbounded export/status semantics remain risks.",
    },
    "tickets-report": {
        "action": "ticketsReport", "view": "tickets-report", "tables": ["ticket_invoices", "candidates", "companiers", "agenciers"],
        "query": "TicketInvoice with candidate/companier/agencier",
        "filters": "from_date,to_date,candidate_id,companier_id,status", "ordering": "invoice_date DESC",
        "rules": "Date filters use invoice_date.", "defect": "No pagination; line/total derivation in view/model requires parity testing.",
    },
    "money-receipt-report": {
        "action": "moneyReceiptReport", "view": "money-receipt-report",
        "tables": ["invoice_money_receipts", "invoices", "ticket_invoice_money_receipts", "ticket_invoices", "agenciers", "companiers"],
        "query": "Load general and ticket receipts, tag type, concatenate, sort in memory",
        "filters": "from_date,to_date,payment_method", "ordering": "receipt_date DESC after in-memory union",
        "rules": "Combines General and Ticket receipts into one output.", "defect": "Two full result sets are loaded and sorted in memory; cross-source duplicate semantics are UNVERIFIED.",
    },
}

table_report_refs: dict[str, list[str]] = defaultdict(list)
report_controller_path = SRC / "app" / "Http" / "Controllers" / "Admin" / "VoyagerReportController.php"
report_controller_text = report_controller_path.read_text(encoding="utf-8", errors="replace")
for uri, spec in REPORT_SPECS.items():
    action_match = re.search(rf"\bfunction\s+{re.escape(spec['action'])}\s*\(", report_controller_text)
    action_line = report_controller_text.count("\n", 0, action_match.start()) + 1 if action_match else 0
    for table in spec["tables"]:
        table_report_refs[table].append(
            f"{uri}: src/app/Http/Controllers/Admin/VoyagerReportController.php::{spec['action']}; "
            f"src/resources/views/vendor/voyager/reports/print/{spec['view']}.blade.php"
        )
        source_refs[table].append({
            "table": table, "kind": "REPORT_CONTROLLER",
            "path": "src/app/Http/Controllers/Admin/VoyagerReportController.php", "line": action_line,
        })
        source_refs[table].append({
            "table": table, "kind": "REPORT_VIEW",
            "path": f"src/resources/views/vendor/voyager/reports/print/{spec['view']}.blade.php", "line": 1,
        })


def infer_logical_edges() -> list[dict]:
    edges = []
    declared_pairs = {
        (t, fk["columns"][0], fk["ref_table"])
        for t, meta in tables.items()
        for fk in meta["fks"]
        if fk["columns"]
    }
    aliases = {
        "group": "class_groups",
        "class_group": "class_groups",
        "companier": "companiers",
        "agencier": "agenciers",
        "user_type": "user_types",
        "document_type": "document_types",
        "invoice_head": "invoice_heads",
        "ticket_company": "ticket_companies",
        "currency": "currencies",
        "permission": "permissions",
        "role": "roles",
        "candidate": "candidates",
        "agent": "agents",
        "sub_agent": "sub_agents",
        "teacher": "teachers",
        "employee": "employees",
        "employer": "employers",
        "country": "countries",
        "division": "divisions",
        "district": "districts",
        "upazila": "upazilas",
        "thana": "thanas",
        "union": "unions",
        "payment": "payments",
        "request": "payment_requests",
        "exam": "exams",
        "report": "reports",
        "menu": "menus",
        "parent": None,
    }
    for table, meta in tables.items():
        for c in meta["columns"]:
            col = c["name"]
            if not col.endswith("_id"):
                continue
            stem = col[:-3]
            target = aliases.get(stem)
            if target is None and stem + "s" in tables:
                target = stem + "s"
            if stem == "parent":
                target = table
            if target in tables and (table, col, target) not in declared_pairs:
                edges.append(
                    {
                        "source_table": table,
                        "source_column": col,
                        "target_table": target,
                        "target_column": "id",
                        "evidence": "INFERRED",
                        "reason": "Naming convention plus target table presence; no declared FK.",
                    }
                )
    return edges


logical_edges = infer_logical_edges()
declared_edges = [
    {
        "source_table": t,
        "source_column": ",".join(fk["columns"]),
        "target_table": fk["ref_table"],
        "target_column": ",".join(fk["ref_columns"]),
        "evidence": "CONFIRMED",
        "reason": f"Declared constraint {fk['name']} {fk['actions']}".strip(),
    }
    for t, meta in tables.items()
    for fk in meta["fks"]
]


def pg_type(mysql: str, col: str) -> tuple[str, str]:
    t = mysql.lower()
    if col == "id" or col.endswith("_id"):
        return ("bigint", "Cast signed/unsigned identifier; resolve through legacy_key_map when relational.")
    if t.startswith("tinyint(1)"):
        return ("boolean", "Normalize 0/1; quarantine values outside {0,1,NULL}.")
    if any(t.startswith(x) for x in ("tinyint", "smallint")):
        return ("smallint", "Exact integer cast.")
    if any(t.startswith(x) for x in ("int", "mediumint")):
        return ("integer", "Exact integer cast; range-check unsigned values.")
    if t.startswith("bigint"):
        return ("bigint", "Exact integer cast; range-check unsigned values.")
    if t.startswith(("float", "double", "decimal")):
        if any(x in col for x in ("amount", "balance", "rate", "total", "price", "fee")):
            return ("numeric(20,6)", "Parse decimal text exactly; no binary-float arithmetic; round only at posted transaction boundary.")
        return ("numeric(20,6)", "Parse decimal representation; compare source text and target numeric.")
    if t.startswith("timestamp") or t.startswith("datetime"):
        return ("timestamptz", "Interpret using approved legacy application timezone, convert to UTC; UNVERIFIED until timezone approval.")
    if t.startswith("date"):
        return ("date", "ISO date cast; reject zero/invalid dates.")
    if t.startswith("time"):
        return ("time", "Time-of-day only; retain no inferred timezone.")
    if "json" in t:
        return ("jsonb", "Parse valid JSON; quarantine invalid payloads.")
    if any(x in t for x in ("text", "blob")):
        return ("text", "Preserve bytes/text semantically; file-like JSON handled by document migration.")
    if t.startswith(("char", "varchar", "enum")):
        return ("text", "Preserve exact value initially; normalize only through approved lookup mapping.")
    return ("text", "UNVERIFIED mapping: preserve source lexical value pending type-specific review.")


framework_tables = {
    "data_rows", "data_types", "menus", "menu_items", "migrations", "password_resets",
    "settings", "translations", "pages", "posts", "categories", "failed_jobs",
}
archive_tokens = ("backup", "_bk", "temp_")
special_targets = {
    "agenciers": ("partners.agencies", "KEEP"),
    "agencies": ("candidate.agency_snapshots", "KEEP"),
    "companiers": ("partners.companies", "KEEP"),
    "companies": ("candidate.company_snapshots", "KEEP"),
    "trainings": ("profile.trainings", "KEEP"),
    "manpower_trainings": ("workflow.manpower_training_events", "KEEP"),
    "police_clearances": ("workflow.police_clearances", "KEEP"),
    "payments": ("finance.ledger_entries", "SPLIT"),
    "payment_requests": ("finance.payment_requests", "KEEP"),
    "sub_agent_payment_requests": ("finance.payment_requests", "MERGE"),
    "notifications": ("communications.notifications", "KEEP"),
    "notification_class_groups": ("communications.notification_audiences", "KEEP"),
    "roles": ("iam.roles", "KEEP"),
    "permissions": ("iam.permissions", "KEEP"),
    "permission_role": ("iam.role_permissions", "KEEP"),
    "user_roles": ("iam.user_roles", "KEEP"),
}


def module_for(table: str) -> str:
    if table in special_targets:
        return special_targets[table][0].split(".")[0]
    if any(x in table for x in ("payment", "invoice", "currency", "receipt", "rate")):
        return "finance"
    if any(x in table for x in ("candidate", "medical", "visa", "flight", "arc", "police", "labour", "selected")):
        return "candidate"
    if table in {"users", "roles", "permissions", "permission_role", "user_roles", "user_types"}:
        return "iam"
    if table in framework_tables:
        return "platform"
    return "operations"


def decision_for(table: str) -> tuple[str, str]:
    if table in special_targets:
        return special_targets[table][1], special_targets[table][0]
    if any(tok in table for tok in archive_tokens):
        return "ARCHIVE", f"archive.{table}"
    if table in framework_tables:
        return "IGNORE", f"legacy_platform.{table}"
    return "KEEP", f"{module_for(table)}.{table}"


def purpose_for(table: str) -> str:
    specials = {
        "agenciers": "Master records for overseas employment agencies/account principals.",
        "agencies": "Candidate-specific agency name/address snapshots; not the agencier master.",
        "companiers": "Master records for overseas employer companies/account principals.",
        "companies": "Candidate-specific company name/address snapshots; not the companier master.",
        "trainings": "User/candidate profile training history.",
        "manpower_trainings": "Candidate manpower-processing training records.",
        "payments": "Mutable legacy financial transaction/agent-balance records.",
        "notifications": "Per-user notification deliveries; dominant-volume operational data.",
    }
    return specials.get(table, f"Legacy records for {table.replace('_', ' ')}; business semantics beyond schema/source references are UNVERIFIED.")


def sensitive_fields(meta: dict) -> list[str]:
    rx = re.compile(r"(name|email|mobile|phone|address|passport|nid|bid|dob|birth|bank|account|iban|swift|password|token|signature|photo|file|balance|amount|salary|remarks)", re.I)
    return [c["name"] for c in meta["columns"] if rx.search(c["name"])]


def status_fields(meta: dict) -> list[str]:
    return [c["name"] for c in meta["columns"] if "status" in c["name"].lower() or c["name"].lower().startswith("is_")]


table_rows = []
column_rows = []
source_ref_rows = []
decision_counts = Counter()
for table in sorted(tables):
    meta = tables[table]
    decision, target = decision_for(table)
    decision_counts[decision] += 1
    refs = source_refs.get(table, [])
    refs_by_kind = defaultdict(list)
    for ref in refs:
        refs_by_kind[ref["kind"]].append(f"{ref['path']}:{ref['line']}")
        source_ref_rows.append(ref)
    relationships = [
        f"{e['source_column']}->{e['target_table']}.{e['target_column']} [{e['evidence']}]"
        for e in declared_edges + logical_edges
        if e["source_table"] == table
    ]
    duplicate = "None evidenced by name/schema alone."
    if any(tok in table for tok in archive_tokens):
        duplicate = "Backup/temp naming is direct legacy-copy evidence; archive, do not union automatically."
    elif table in {"agencies", "agenciers", "companies", "companiers", "trainings", "manpower_trainings"}:
        duplicate = "Near-name collision but materially different columns/ownership; do not merge without business keys."
    table_rows.append(
        {
            "source_table": table,
            "purpose": purpose_for(table),
            "insert_row_count": meta["rows"],
            "primary_key": ",".join(meta["pk"]) or "UNVERIFIED: no PK declared in dump",
            "indexes": json.dumps(meta["indexes"], separators=(",", ":")),
            "declared_fks": json.dumps(meta["fks"], separators=(",", ":")),
            "logical_relationships": " | ".join(relationships) or "UNVERIFIED: no safe relationship inferred",
            "model_refs": " | ".join(refs_by_kind["MODEL"]) or "UNVERIFIED: no canonical model maps to this table by explicit $table or Laravel naming convention",
            "controller_refs": " | ".join(refs_by_kind["CONTROLLER"]) or "UNVERIFIED: no canonical controller reference found",
            "service_helper_refs": " | ".join(refs_by_kind["SERVICE"] + refs_by_kind["HELPER"]) or "UNVERIFIED: no canonical service/helper reference found",
            "report_refs": " | ".join(table_report_refs.get(table, [])) or "UNVERIFIED: definitive active report catalog contains no query/view reference to this table",
            "sensitive_fields": ",".join(sensitive_fields(meta)) or "none identified by field-name heuristic",
            "status_fields": ",".join(status_fields(meta)) or "none",
            "duplicate_legacy_evidence": duplicate,
            "target_table": target,
            "target_module": target.split(".")[0],
            "decision": decision,
            "legacy_id_mapping": "legacy_key_map(source_system='manpower_mysql',source_table,source_id)->target_type,target_id; unique on source triplet",
            "validation_rule": f"source INSERT tuples={meta['rows']}; reconcile target lineage count and PK uniqueness; compare deterministic field hashes excluding approved transforms",
            "migration_risk": "HIGH" if decision in {"MERGE", "SPLIT"} or sensitive_fields(meta) else ("MEDIUM" if meta["rows"] else "LOW"),
        }
    )
    for c in meta["columns"]:
        target_type, transform = pg_type(c["source_type"], c["name"])
        target_name = c["name"]
        if c["name"] == "acr_file_path":
            target_name = "arc_file_id"
        elif re.search(r"(file_path|photo|logo|signature|avatar)$", c["name"]):
            target_name = re.sub(r"(_file_path|_path)?$", "", c["name"]) + "_file_id"
        column_rows.append(
            {
                "source_table": table,
                "source_column": c["name"],
                "source_type": c["source_type"],
                "source_nullable": c["nullable"],
                "source_default": c["default"],
                "source_definition": c["definition"],
                "target_table": target,
                "target_column": target_name,
                "target_type": target_type,
                "transform": transform,
                "constraint_proposal": "NOT NULL only if source is NOT NULL and profiling proves no invalid/null rows; otherwise staged nullable",
                "validation": f"null-count, distinct-count, min/max/length and deterministic transformed-value hash for {table}.{c['name']}",
                "evidence": "CONFIRMED source / PROPOSED target",
            }
        )

write_csv("table-mapping.csv", table_rows)
write_csv("column-mapping.csv", column_rows)
write_csv("source-references.csv", source_ref_rows, ["table", "kind", "path", "line"])
write_csv("relationships.csv", declared_edges + logical_edges)

# RBAC machine-readable artifacts.
roles = records("roles")
permissions = records("permissions")
grants = records("permission_role")
role_by_id = {r.get("id"): r for r in roles}
permission_by_id = {p.get("id"): p for p in permissions}
permission_rows = []
for p in permissions:
    key = str(p.get("key"))
    action, _, resource = key.partition("_")
    refs = source_refs.get(str(p.get("table_name") or resource), [])
    permission_rows.append(
        {
            "legacy_permission_id": p.get("id"),
            "legacy_key": key,
            "table_name": p.get("table_name"),
            "proposed_stable_key": f"{module_for(str(p.get('table_name') or resource))}.{resource}.{action}",
            "route_controller_actions": " | ".join(f"{r['path']}:{r['line']}" for r in refs if r["kind"] in {"ROUTE", "CONTROLLER"}) or "UNVERIFIED: Voyager/BREAD dynamic route or no explicit canonical action",
            "row_scope": "PROPOSED: explicit policy scope required; legacy global scopes/hard-coded roles must be translated",
            "document_access": "PROPOSED: inherit resource read plus file-specific policy; never infer from URL possession",
            "report_access": "PROPOSED: separate report.execute/report.export permission where applicable",
            "evidence": "CONFIRMED legacy permission; PROPOSED stable key/scopes",
        }
    )
write_csv("rbac-permissions.csv", permission_rows)

grant_rows = []
for g in grants:
    role = role_by_id.get(g.get("role_id"), {})
    perm = permission_by_id.get(g.get("permission_id"), {})
    grant_rows.append(
        {
            "legacy_role_id": g.get("role_id"),
            "role_name": role.get("name", "UNVERIFIED"),
            "legacy_permission_id": g.get("permission_id"),
            "permission_key": perm.get("key", "UNVERIFIED"),
            "proposed_role_key": re.sub(r"[^a-z0-9]+", ".", str(role.get("name", f"legacy.{g.get('role_id')}")).lower()).strip("."),
            "grant_evidence": "CONFIRMED: permission_role INSERT",
        }
    )
write_csv("rbac-role-grants.csv", grant_rows)

hardcoded = []
for p, content in source_files:
    for line_no, line in enumerate(content.splitlines(), 1):
        if "role_id" not in line:
            continue
        # Capture every numeric literal on a role_id-bearing line, including the
        # stale role 3 assignment, rather than limiting the inventory to DB roles.
        ids = sorted(set(re.findall(r"(?<!\d)\d{1,6}(?!\d)", line)), key=int)
        if ids:
            hardcoded.append(
                {
                    "path": relpath(p),
                    "line": line_no,
                    "numeric_role_ids": ",".join(ids),
                    "context": re.sub(r"\s+", " ", line.strip())[:300],
                    "replacement": "Replace with stable role key/policy check; preserve current behavior until parity tests pass.",
                }
            )
write_csv("rbac-hardcoded-role-ids.csv", hardcoded)

# Workflow based on safe live-status metadata plus confirmed payment transitions.
live_rows = records("live_status")
workflow = []
for idx, row in enumerate(sorted(live_rows, key=lambda r: int(r.get("step_no") or 0))):
    step = row.get("step_no")
    name = row.get("name")
    workflow.append(
        {
            "workflow": "candidate",
            "from_state": "previous approved live status" if idx else "registered",
            "event": f"record/approve step {step}",
            "to_state": f"legacy-live-{step}",
            "legacy_live_status": name,
            "actors": "UNVERIFIED: permissions and role grants do not prove operational actor ownership",
            "guards": "PROPOSED: candidate active; prerequisite event exists; actor has scoped permission",
            "side_effects": "INFERRED: related process record and candidate live-status display change",
            "finance_effect": "UNVERIFIED except payment-gated stages; no implicit posting permitted",
            "document_requirements": "UNVERIFIED: confirm per-stage checklist with business owners",
            "notifications": "INFERRED: notification helper may emit stage updates",
            "rollback": "PROPOSED: compensating event only; do not rewrite history",
            "source_reference": "src/app/Helpers/CommonClass.php:539-576 and live_status INSERT evidence",
            "evidence": "CONFIRMED status label/order; INFERRED transition; PROPOSED guards",
            "compatibility": f"Expose exact legacy label {name!r} while new event state is authoritative",
        }
    )
for from_state, event, to_state, actor, ref in [
    ("P", "approve payment request", "A", "finance approver / agent flow", "src/app/Http/Controllers/Admin/VoyagerAjaxController.php:184-284"),
    ("P", "reject payment request", "R", "finance approver / agent flow", "src/app/Http/Controllers/Admin/VoyagerAjaxController.php:184-284"),
    ("P", "approve payment", "A", "authorized payment actor", "src/app/Http/Controllers/Admin/VoyagerAjaxController.php:70-133"),
]:
    workflow.append(
        {
            "workflow": "finance",
            "from_state": from_state,
            "event": event,
            "to_state": to_state,
            "legacy_live_status": "not applicable",
            "actors": actor,
            "guards": "CONFIRMED code checks current state P; PROPOSED idempotency key and row lock",
            "side_effects": "Balance/ledger/status updates; exact atomicity UNVERIFIED in legacy",
            "finance_effect": "PROPOSED immutable posting plus balanced reconciliation",
            "document_requirements": "UNVERIFIED",
            "notifications": "CONFIRMED targeted notification/email paths",
            "rollback": "PROPOSED reversal entry, never UPDATE posted amount",
            "source_reference": ref,
            "evidence": "CONFIRMED state codes and code path; PROPOSED target controls",
            "compatibility": "API emits P/A/R aliases until all consumers migrate",
        }
    )
write_csv("workflow-transitions.csv", workflow)

# Definitive report catalog. Metadata contains no report row data or identifying samples.
def view_columns(view_name: str, root: str = "vendor/voyager/reports/print") -> str:
    path = SRC / "resources" / "views" / root / f"{view_name}.blade.php"
    if not path.exists():
        return "UNVERIFIED: referenced Blade view does not exist"
    text = path.read_text(encoding="utf-8", errors="replace")
    labels = []
    for value in re.findall(r"<th\b[^>]*>(.*?)</th>", text, re.I | re.S):
        value = re.sub(r"\{\{.*?\}\}|{!!.*?!!}|<[^>]+>|@[A-Za-z_][A-Za-z0-9_]*(?:\([^)]*\))?", " ", value, flags=re.S)
        value = re.sub(r"\s+", " ", value).strip(" :-\r\n\t")
        if value and value not in labels:
            labels.append(value)
    return " | ".join(labels) if labels else "UNVERIFIED: Blade has no static <th> labels; inspect rendered output"


common_report_defect = (
    "Common exporter defect: EXCEL and CSV use bitwise `|`, both emit XLSX, and export only id/name; "
    "CSV code is commented out. reportProcess catches and echoes exception text."
)
report_rows = []
for r in records("reports"):
    uri = str(r.get("report_uri") or "")
    spec = REPORT_SPECS.get(uri)
    raw_params = str(r.get("params") or "")
    param_keys = re.findall(r'(?:\\"|")key(?:\\"|")\s*:\s*(?:\\"|")([^"\\]+)', raw_params)
    db_params = ",".join(param_keys) if param_keys else ("none" if not raw_params else "UNVERIFIED: malformed params JSON")
    if not spec:
        # All current DB rows are expected to resolve; retaining this branch makes
        # future dump changes explicit rather than silently claiming activity.
        report_rows.append({
            "catalog_type": "DB_REPORT", "legacy_id": r.get("id"), "name": r.get("name"), "uri": uri,
            "db_status": r.get("status"), "classification": "UNVERIFIED", "permission": "UNVERIFIED: no resolved execution path",
            "route": f"POST reports/generate/{uri}", "controller_action": "UNVERIFIED", "view": "UNVERIFIED",
            "source_query": "UNVERIFIED: DB metadata has no matched controller method", "tables": "UNVERIFIED",
            "filters": db_params, "output_columns": "UNVERIFIED", "calculations": "UNVERIFIED", "totals": "UNVERIFIED",
            "ordering": "UNVERIFIED", "pagination": "UNVERIFIED", "pdf": "UNVERIFIED", "excel": "UNVERIFIED",
            "csv": "UNVERIFIED", "business_rules": "UNVERIFIED", "known_defect": "No matched execution path",
            "row_scope": "UNVERIFIED", "sensitive_output": "YES: private by default",
            "target_disposition": "Do not rebuild until path and owner are confirmed", "evidence_classification": "UNVERIFIED",
        })
        continue
    report_rows.append({
        "catalog_type": "DB_REPORT", "legacy_id": r.get("id"), "name": r.get("name"), "uri": uri,
        "db_status": r.get("status"), "classification": "ACTIVE",
        "permission": "browse_reports exists; execution enforcement UNVERIFIED because explicit per-route permission middleware is absent",
        "route": f"POST reports/generate/{uri} (CONFIRMED src/routes/admin.php)",
        "controller_action": f"VoyagerReportController::{spec['action']}",
        "view": f"vendor.voyager.reports.print.{spec['view']} (CONFIRMED)",
        "source_query": spec["query"], "tables": ",".join(spec["tables"]),
        "filters": f"Controller: {spec['filters']}; DB metadata: {db_params}",
        "output_columns": view_columns(spec["view"]),
        "calculations": spec["rules"],
        "totals": "Blade-derived totals/calculations require golden-output parity; no separate controller total query unless stated",
        "ordering": spec["ordering"], "pagination": "NONE: action terminates with get()/first() or in-memory collection",
        "pdf": "YES: PDF-VIEW and PDF-DOWNLOAD via reportProcess",
        "excel": "DEFECTIVE: returns generic id/name XLSX rather than report columns",
        "csv": "DEFECTIVE: CSV request also returns XLSX; CSV implementation is commented out",
        "business_rules": spec["rules"], "known_defect": spec["defect"] + " " + common_report_defect,
        "row_scope": "Model scopes may apply; effective role-by-role scope is UNVERIFIED and must be policy-tested",
        "sensitive_output": "YES: candidate/finance/identity data must be private",
        "target_disposition": "Rebuild with golden query/layout/authorization parity and owner approval",
        "evidence_classification": "CONFIRMED DB row + route + controller + Blade; permission/row-scope details UNVERIFIED",
    })

print_outputs = [
    {
        "name": "Exam result sheet PDF", "route": "GET exam-results/export-pdf",
        "action": "VoyagerExamResultController::exportPdf", "view": "exam-result",
        "query": "Exam with classGroup; exam results and candidates for requested exam", "tables": "exams,class_groups,exam_results,candidates",
        "filters": "exam_id; additional request behavior must be contract-tested", "ordering": "Controller/result ordering as implemented; full rule UNVERIFIED",
        "columns": "Rendered exam result Blade columns; exact static extraction UNVERIFIED for non-report view",
        "defect": "Authorization is not explicit on the route; action returns exception text in error response.",
    },
    {
        "name": "Candidate profile export", "route": "GET my/profile/export",
        "action": "VoyagerMyPanelController::profileExport", "view": "my/profile-export",
        "query": "Authenticated role selects own agent/candidate/teacher/employee/employer profile; candidate loads profile lists",
        "tables": "users,agents,candidates,teachers,employees,employers,language_list,expertise_list,skill_list,curricular_list,interest_list,attribute_list,experiences,educations,skills,trainings",
        "filters": "Authenticated user and hard-coded role IDs 101-105", "ordering": "Profile/list ordering UNVERIFIED",
        "columns": "CV/profile fields selected by BREAD metadata and profile-export Blade",
        "defect": "debug=true returns HTML before the PDF branch, so route is executable/ACTIVE but PDF code is unreachable.",
    },
    {
        "name": "Invoice print/PDF", "route": "ANY reports/generate/invoice/{id}",
        "action": "VoyagerInvoiceController::printInvoice", "view": "invoice",
        "query": "Invoice by id with invoice lines/parties", "tables": "invoices,invoice_lines,invoice_heads,invoice_candidates,agenciers,companiers,candidates",
        "filters": "invoice id; report_type/downloadable request controls", "ordering": "Invoice line ordering from relationship/view; UNVERIFIED",
        "columns": view_columns("invoice"), "defect": "ANY verb broadens attack surface; authorization and issued-version immutability require tests.",
    },
    {
        "name": "Invoice money receipt print/PDF", "route": "ANY reports/generate/invoice-money-receipts/{id}",
        "action": "VoyagerInvoiceMoneyReceiptController::printInvoiceMoneyReceipt", "view": "invoice-money-receipt",
        "query": "Invoice money receipt by id with invoice/parties", "tables": "invoice_money_receipts,invoices,agenciers,companiers",
        "filters": "receipt id; report_type/downloadable request controls", "ordering": "Single record",
        "columns": view_columns("invoice-money-receipt"), "defect": "ANY verb and explicit authorization gap; printed totals need ledger reconciliation.",
    },
    {
        "name": "Ticket invoice print/PDF", "route": "ANY reports/generate/ticket-invoice/{id}",
        "action": "VoyagerTicketInvoiceController::printInvoice", "view": "ticket-invoice",
        "query": "Ticket invoice by id with ticket lines/parties", "tables": "ticket_invoices,ticket_invoice_lines,ticket_companies,candidates,agenciers,companiers",
        "filters": "ticket invoice id; report_type/downloadable request controls", "ordering": "Ticket line ordering from relationship/view; UNVERIFIED",
        "columns": view_columns("ticket-invoice"), "defect": "ANY verb; authorization and monetary parity require tests.",
    },
    {
        "name": "Ticket money receipt print/PDF", "route": "ANY reports/generate/ticket-invoice-money-receipts/{id}",
        "action": "VoyagerTicketInvoiceMoneyReceiptController::printInvoiceMoneyReceipt", "view": "ticket-invoice-money-receipt",
        "query": "Ticket money receipt by id with invoice/parties", "tables": "ticket_invoice_money_receipts,ticket_invoices,agenciers,companiers",
        "filters": "ticket receipt id; report_type/downloadable request controls", "ordering": "Single record",
        "columns": view_columns("ticket-invoice-money-receipt"), "defect": "ANY verb; authorization and receipt-to-ledger reconciliation require tests.",
    },
]
for p in print_outputs:
    profile = p["name"] == "Candidate profile export"
    report_rows.append({
        "catalog_type": "PRINT_OUTPUT", "legacy_id": "", "name": p["name"], "uri": "", "db_status": "not DB-driven",
        "classification": "ACTIVE", "permission": "Authenticated/admin route context; exact policy UNVERIFIED",
        "route": p["route"] + " (CONFIRMED)", "controller_action": p["action"], "view": p["view"],
        "source_query": p["query"], "tables": p["tables"], "filters": p["filters"], "output_columns": p["columns"],
        "calculations": "Blade/controller calculations; golden-output parity required",
        "totals": "Invoice/receipt totals where applicable; otherwise not applicable or UNVERIFIED",
        "ordering": p["ordering"], "pagination": "NONE: single record/profile or bounded exam result set",
        "pdf": "UNREACHABLE in current profileExport because debug=true" if profile else "YES: controller PDF stream/download path",
        "excel": "NO", "csv": "NO", "business_rules": p["query"], "known_defect": p["defect"],
        "row_scope": "Single authenticated/self or ID-selected resource; ownership enforcement UNVERIFIED unless stated",
        "sensitive_output": "YES: private identity/financial document", "target_disposition": "Preserve after authorization and golden-output parity",
        "evidence_classification": "CONFIRMED route + controller; specified authorization/business details UNVERIFIED",
    })

legacy_reports = [
    ("supplierLedger_bk_test", "generic index", "Product::select(id,name)", "products", "No route/DB report; debug dd() on failure."),
    ("customerLedger", "customer-ledger", "Customer::where(id)", "customers", "Customer model/table absent from current 90-table dump."),
    ("profitLoss", "customer-ledger", "Prints Under development then attempts Customer query", "customers", "Explicitly under development; absent model/table."),
    ("sales", "sales", "Sale filtered by customer/date/sales_type", "sales,customers,products", "Sale/customer/product business tables absent; uses bitwise & for date guard."),
    ("purchase", "purchase", "Purchase filtered by supplier/date", "purchases,suppliers,products", "Business tables absent; uses bitwise & for date guard."),
    ("products", "product", "Product optional id filter", "products", "Product table/model absent from dump/application models."),
    ("stock", "stock", "Stock optional product filter", "stocks,products", "Stock/product tables/models absent from current business schema."),
]
for action, view, query, legacy_tables, defect in legacy_reports:
    report_rows.append({
        "catalog_type": "LEGACY_METHOD", "legacy_id": "", "name": action, "uri": "", "db_status": "no DB report row",
        "classification": "LEGACY", "permission": "UNVERIFIED: no route/grant found", "route": "UNVERIFIED: no canonical route",
        "controller_action": f"VoyagerReportController::{action}", "view": f"vendor.voyager.reports.print.{view}",
        "source_query": query, "tables": legacy_tables, "filters": "See legacy method request fields; not an active contract",
        "output_columns": view_columns(view) if view != "generic index" else view_columns("index"),
        "calculations": "Legacy Blade/controller only; not approved target behavior", "totals": "UNVERIFIED",
        "ordering": "As legacy method; often id DESC or unspecified", "pagination": "NONE", "pdf": "Dispatcher-supported if reachable",
        "excel": "DEFECTIVE generic id/name XLSX", "csv": "DEFECTIVE returns XLSX", "business_rules": "No active business ownership evidenced",
        "known_defect": defect + " " + common_report_defect, "row_scope": "UNVERIFIED",
        "sensitive_output": "Assume YES until owner confirms", "target_disposition": "Archive; do not rebuild without explicit approval",
        "evidence_classification": "CONFIRMED legacy method/view; route and business ownership UNVERIFIED",
    })
report_rows.append({
    "catalog_type": "LEGACY_VIEW", "legacy_id": "", "name": "supplier-ledger Blade", "uri": "", "db_status": "view only",
    "classification": "LEGACY", "permission": "UNVERIFIED", "route": "UNVERIFIED: no route/method renders this view",
    "controller_action": "none found", "view": "vendor.voyager.reports.print.supplier-ledger",
    "source_query": "none found", "tables": "suppliers/products (inferred from view name only)", "filters": "UNVERIFIED",
    "output_columns": view_columns("supplier-ledger"), "calculations": "View-only legacy calculations", "totals": "UNVERIFIED",
    "ordering": "UNVERIFIED", "pagination": "NONE", "pdf": "UNVERIFIED", "excel": "NO", "csv": "NO",
    "business_rules": "No active execution evidence", "known_defect": "Orphaned Blade view.",
    "row_scope": "UNVERIFIED", "sensitive_output": "Assume YES", "target_disposition": "Archive",
    "evidence_classification": "CONFIRMED view; execution UNVERIFIED",
})
for row in report_rows:
    row["params"] = row.get("filters", "")
    row["output_formats"] = f"PDF={row.get('pdf','')}; Excel={row.get('excel','')}; CSV={row.get('csv','')}"
    row["evidence"] = row.get("evidence_classification", "")
report_fields = [
    "catalog_type", "legacy_id", "name", "uri", "db_status", "classification", "permission",
    "route", "controller_action", "view", "source_query", "tables", "params", "filters",
    "output_columns", "calculations", "totals", "ordering", "pagination", "pdf", "excel", "csv",
    "output_formats", "business_rules", "known_defect", "row_scope", "sensitive_output",
    "target_disposition", "evidence_classification", "evidence",
]
write_csv("report-catalog.csv", report_rows, report_fields)

# File-bearing columns and BREAD declarations.
data_type_by_id = {r.get("id"): r for r in records("data_types")}
bread_by_field = defaultdict(list)
for r in records("data_rows"):
    typ = str(r.get("type") or "")
    details = str(r.get("details") or "")
    if typ in {"file", "image", "multiple_images", "media_picker"} or "download_link" in details or "file" in typ:
        dt = data_type_by_id.get(r.get("data_type_id"), {})
        bread_by_field[(dt.get("name"), r.get("field"))].append(typ)
document_rows = []
file_rx = re.compile(r"(file|path|photo|image|avatar|logo|signature|certificate|document|attachment)", re.I)
for table, meta in tables.items():
    for c in meta["columns"]:
        bread = bread_by_field.get((table, c["name"]), [])
        if not file_rx.search(c["name"]) and not bread:
            continue
        json_format = "Possible Voyager array JSON with download_link/original_name; detect per value" if c["source_type"].lower().endswith("text") else "Usually scalar path; profile before load"
        document_rows.append(
            {
                "source_table": table,
                "source_column": c["name"],
                "source_type": c["source_type"],
                "bread_type": ",".join(bread) or "UNVERIFIED: no file-type BREAD declaration",
                "legacy_format": json_format,
                "legacy_path_root": f"Voyager/public storage relative path, often {table}/<MonthYear>/...; physical root UNVERIFIED",
                "target_metadata": "file_id UUID, object_key, original_name, detected_mime, size_bytes, sha256, source_table, source_id, source_column, ordinal, created_at, classification",
                "access_rule": "Private-by-default; short-lived authorized download; resource policy plus document classification",
                "orphan_rule": "Inventory path; MISSING if DB ref lacks object; ORPHAN if object lacks ref; quarantine, never silently delete",
                "validation": "byte-for-byte SHA-256 after copy; independently sniff MIME; size parity; JSON cardinality parity",
                "evidence": "CONFIRMED field/BREAD metadata; UNVERIFIED storage existence without filesystem inventory",
            }
        )
write_csv("document-fields.csv", document_rows)

# Finance operation register.
finance_ops = [
    ("Create agent credit/payment", "payments CR row plus agent balance mutation", "Balanced immutable ledger transaction", "PARITY", "Float/integer-scale source amounts; mutable balance", "Approve currency/minor-unit and opening-balance cutover"),
    ("Create candidate debit from request", "Approve request, decrement balance, create DR payment", "Serializable request approval posts balanced entries once", "PARITY", "bill_title uses assignment (=) in stage linkage branches", "Stage linkage must use approved fee-type key"),
    ("Reject payment request", "P to R status update", "Append decision event; no ledger posting", "PARITY", "Atomicity/audit actor completeness UNVERIFIED", "Define reject/reopen authority"),
    ("Sub-agent request approval", "Agent/sub-agent conditional approval path", "Same request aggregate with scoped approver chain", "PARITY", "Mixed agent/sub-agent model and hard-coded role IDs", "Approve escalation and liability owner"),
    ("Manual payment edit/delete", "Voyager BREAD may permit mutation according to grants", "Posted entries immutable; correction by reversal/repost", "INTENTIONAL CHANGE", "Silent history changes possible", "Approve lock date and correction workflow"),
    ("Agent balance display", "Stored agents.balance and payment sums/global scopes", "Derived ledger balance plus reconciliation snapshot", "PARITY", "Drift possible; tautological candidate/manpower query defect noted in canonical code audit", "Choose ledger as source of truth"),
    ("Currency conversion", "currency_id plus float exchange_rate", "numeric(20,8) rate; amount numeric(20,6); posted base amount numeric(20,6)", "PARITY", "Binary float and unclear rounding", "Approve currency decimal places and rounding mode"),
    ("Invoice issue", "Mutable invoice header/lines", "Versioned issued invoice; immutable monetary lines after issue", "PARITY", "Totals/tax semantics require report parity", "Approve lifecycle DRAFT/ISSUED/VOID"),
    ("Money receipt issue", "Invoice receipt records and print", "Receipt tied to ledger settlement and immutable issuance event", "PARITY", "Potential invoice/payment divergence", "Approve allocation/overpayment policy"),
    ("Ticket invoice/receipt", "Separate ticket tables and print paths", "Finance sub-ledger using shared posting primitives", "PARITY", "Duplicate financial implementation", "Approve shared versus separate numbering"),
    ("Opening balances", "Legacy CR rows/descriptions and backups", "Signed, approved migration journal at cutover", "PARITY", "Backup overlap can double count", "Approve cutoff and authoritative source"),
    ("Payment backup handling", "Five payment copies plus request backup", "Archive exactly once with lineage; never union into live ledger", "INTENTIONAL CHANGE", "Overlapping IDs/rows likely; chronology is not authority", "Business/data owner selects authoritative live table"),
]
finance_rows = [
    {
        "operation": a,
        "old_behavior": b,
        "proposed_behavior": c,
        "parity": d,
        "known_bug_or_risk": e,
        "business_decision": f,
        "numeric_policy": "amount numeric(20,6); exchange_rate numeric(20,8); base_amount numeric(20,6); currency minor-unit validation; ROUND_HALF_UP only at approved posting boundary",
        "immutability": "No UPDATE/DELETE of POSTED entries; append REVERSAL linked to original; audit actor/reason/correlation/idempotency keys",
        "reconciliation": "Every journal balances per currency; daily source-vs-target counts/sums; account trial balance; request-to-posting one-to-one; backup exclusion proof",
        "evidence": "CONFIRMED schema/source where described; PROPOSED target; UNVERIFIED business semantics flagged",
    }
    for a, b, c, d, e, f in finance_ops
]
write_csv("finance-operations.csv", finance_rows)

# Human review documents.
mapping_md = [
    "# 01 — Complete source-to-target mapping",
    "",
    "The normative details are in `table-mapping.csv` (one row per source table), `column-mapping.csv` (one row per source column), `source-references.csv`, and `relationships.csv`. Row counts are physical INSERT tuples, never AUTO_INCREMENT metadata.",
    "",
    f"- Source tables: **{len(tables)}**",
    f"- Source columns: **{sum(len(x['columns']) for x in tables.values())}**",
    f"- Parsed INSERT rows: **{sum(x['rows'] for x in tables.values()):,}**",
    f"- Declared foreign keys: **{len(declared_edges)}**",
    f"- Logical relationships: **{len(logical_edges)}**",
    "",
    "## Decision roll-up",
]
mapping_md += [f"- {k}: {decision_counts[k]}" for k in ["KEEP", "MERGE", "SPLIT", "ARCHIVE", "IGNORE"]]
mapping_md += [
    "",
    "## Review protocol",
    "1. Filter `table-mapping.csv` by decision/risk/module. 2. Review every column in `column-mapping.csv`. 3. Resolve every UNVERIFIED item before making a target constraint mandatory. 4. Attach signed business approval to the register in artifact 11.",
    "",
    "## Special collisions",
    "- `agenciers`/`companiers` are master counterparties with account/contact fields; `agencies`/`companies` are candidate-owned snapshots with `candidate_id`. KEEP separately; optional later matching must preserve snapshot text and lineage.",
    "- `trainings` is profile training; `manpower_trainings` is candidate process evidence. KEEP in separate modules.",
    "- `police_clearances` keeps the correct plural target name and owns `photo_file_path`. Separately, `arcs.acr_file_path` is the legacy ARC typo; it maps to `arc_file_id` while retaining exact source-column lineage.",
    "- Payment backup tables are ARCHIVE only. They must not be unioned into live history absent an approved, row-level authority/deduplication decision.",
    "- Voyager/BREAD tables are configuration evidence, not target business schema. Preserve export for traceability; IGNORE as runtime target except explicitly rebuilt IAM/content needs.",
]
write("01-table-mapping.md", "\n".join(mapping_md))

mermaid = ["# 02 — Database relationships", "", "Solid arrows are declared FKs; dashed arrows are logical naming-based relationships.", "", "```mermaid", "flowchart LR"]
for e in declared_edges:
    mermaid.append(f"  {e['source_table']} -->|{e['source_column']} CONFIRMED| {e['target_table']}")
for e in logical_edges:
    mermaid.append(f"  {e['source_table']} -.->|{e['source_column']} INFERRED| {e['target_table']}")
mermaid += ["```", "", f"The current dump declares exactly {len(declared_edges)} constraints. Logical edges are migration hypotheses, not permission to enforce target FKs until orphan/null/cardinality profiling passes. Full edge reasons are in `relationships.csv`."]
write("02-relationships.md", "\n".join(mermaid))

write(
    "03-legacy-id.md",
    """# 03 — Legacy ID and lineage strategy

Every migrated business row receives a target UUID/bigint according to module convention and an immutable mapping:

```sql
legacy_key_map(
  source_system text NOT NULL,
  source_table text NOT NULL,
  source_id text NOT NULL,
  target_type text NOT NULL,
  target_id uuid NOT NULL,
  migration_run_id uuid NOT NULL,
  source_row_hash bytea NOT NULL,
  migrated_at timestamptz NOT NULL,
  PRIMARY KEY(source_system, source_table, source_id),
  UNIQUE(target_type, target_id, source_system, source_table, source_id)
)
```

Use `source_table + source_id` even where IDs overlap between live and backup tables. Child resolution joins the mapping, never assumes identical numeric IDs. Records without a declared PK use a deterministic synthetic source ID from table name + canonical row hash + duplicate ordinal; this is PROPOSED and must be approved before load. MERGE/SPLIT records may have multiple mappings through `legacy_record_component`; all business targets retain at least one exact source lineage. Hashes are stored in the migration environment, not these documents. Re-runs are idempotent by source triplet and compare source hashes before any update.
""",
)

write(
    "04-workflow.md",
    f"""# 04 — Candidate workflow transition matrix

`workflow-transitions.csv` is normative and contains {len(workflow)} transitions with states, event, actor, guards, side effects, finance/doc/notification effects, rollback, source references, compatibility, and evidence classification.

Candidate live-status labels/order from the DB are CONFIRMED. A label is not proof that every adjacent transition is legal, so candidate transitions are INFERRED and proposed guards remain PROPOSED. Preserve compatibility by exposing exact legacy live-status labels/codes while deriving them from append-only workflow events. Unknown historical values remain `LEGACY_UNKNOWN(<raw>)`; never coerce them to a current state.

No unverified actor, prerequisite, document, or finance rule becomes a mandatory database constraint until business approval.
""",
)

write(
    "05-rbac.md",
    f"""# 05 — Final RBAC matrix

- Roles from dump: **{len(roles)}**
- Permissions from dump: **{len(permissions)}**
- Explicit grants: **{len(grants)}**
- Hard-coded numeric-role occurrences cataloged: **{len(hardcoded)}**

Normative files: `rbac-permissions.csv`, `rbac-role-grants.csv`, `rbac-hardcoded-role-ids.csv`.

Legacy grants are CONFIRMED but route authorization is partly Voyager/BREAD dynamic. Therefore each permission row labels route/controller gaps UNVERIFIED. Target authorization uses stable semantic keys, explicit row scopes (self/agent/sub-agent/agency/company/all), separate document download and report execute/export grants, deny-by-default, and policy tests. Numeric IDs 1, 2, and 100–110 are compatibility inputs only; runtime policies must use stable keys. The dump contains 13 actual role records even though code also references stale/extra IDs; retain unknown IDs in a compatibility map and block deployment until each is resolved.
""",
)

write(
    "06-finance.md",
    """# 06 — Finance specification

`finance-operations.csv` is the operation-by-operation decision register. It records old behavior, proposed behavior, parity intent, defects/risks, and required business decisions.

The source uses FLOAT (including `float(12,0)`) for money and rates. Target amounts are `numeric(20,6)`, exchange rates `numeric(20,8)`, and currency-specific minor-unit checks are applied at posting/export boundaries. No historical row is silently corrected. The confirmed `bill_title` assignment defect (`=` in conditional branches) and the tautological manpower/candidate query defect are preserved as evidence but explicitly not reproduced. Posted journals are immutable; fixes are linked reversals/reposts. Reconciliation must prove counts, signed sums by currency/account/day, request-to-payment cardinality, and exclusion/deduplication of every backup table before sign-off.
""",
)

write(
    "07-reports.md",
    f"""# 07 — Definitive report catalog

`report-catalog.csv` contains **{len([r for r in report_rows if r['catalog_type']=='DB_REPORT'])} ACTIVE DB reports**, **{len(print_outputs)} ACTIVE print outputs**, **{len([r for r in report_rows if r['catalog_type']=='LEGACY_METHOD'])} LEGACY controller methods**, and **{len([r for r in report_rows if r['catalog_type']=='LEGACY_VIEW'])} orphaned LEGACY view**. Every row includes permission, route/action/view, source query, tables, filters, output columns, calculations, totals, ordering, pagination, PDF/Excel/CSV behavior, business rules, defect, scope, disposition, and evidence classification.

All 17 DB rows are active and have confirmed routes, controller actions, and Blade views, including Teacher Schedule and Exam Result Sheet. Candidate profile export is ACTIVE because `GET my/profile/export` and `profileExport()` are executable, although `debug=true` makes its PDF branch unreachable.

## Confirmed cross-report defects
- `reportProcess` uses bitwise `|` for the EXCEL/CSV branch.
- Both EXCEL and CSV requests generate XLSX with only generic `id,name`; CSV implementation is commented out.
- Reports use unpaginated `get()`/in-memory collections.
- Exception text is echoed or returned by several paths.
- Agent Report hard-codes five business metrics/balances to zero.
- Flight Schedule filters payment status by rendered HTML text after loading rows.

LEGACY rows cover every non-current controller report method and the orphaned supplier-ledger Blade. They are not target requirements without explicit owner approval.
""",
)

write(
    "08-documents.md",
    f"""# 08 — Document migration specification

`document-fields.csv` inventories **{len(document_rows)}** file-bearing schema/BREAD fields. It covers scalar paths, Voyager JSON arrays (`download_link`/`original_name`), images, signatures, logos, certificates, and attachment-like fields without reproducing any file names or personal data.

Target files are private objects with UUID, opaque object key, original name, detected MIME, size, SHA-256, source table/id/column/ordinal, timestamps, classification, malware-scan status, and retention/legal-hold fields. Never trust extension or client MIME. Copy streams, compute source/target checksum, compare size, then activate metadata. Missing DB references become MISSING; storage-only objects become ORPHAN; malformed JSON becomes QUARANTINED. None are silently deleted. Authorization requires resource access plus document classification; downloads are audited and use short-lived URLs.

Physical storage roots, object existence, access inheritance exceptions, retention periods, and malware policy are UNVERIFIED because only SQL/source evidence was authorized.
""",
)

write(
    "09-postgresql-prisma.md",
    """# 09 — PostgreSQL and Prisma proposal

This is design pseudocode, not an application schema.

## PostgreSQL baseline
- Schemas: `iam`, `candidate`, `workflow`, `partners`, `finance`, `documents`, `communications`, `reporting`, `migration`, `audit`.
- `timestamptz` in UTC for instants; `date` for civil dates; source timezone is an approval item. Never reinterpret timestamps before approval.
- UUID business identifiers; exact legacy lineage through `migration.legacy_key_map`.
- Deferred FK creation: load, profile orphans, resolve approvals, then add `NOT VALID`, validate, and make mandatory only where CONFIRMED.
- Audit: append-only actor/action/entity/before-hash/after-hash/correlation/reason at application and privileged DB boundaries.
- Workflow: append-only `workflow.events`; unique `(aggregate_id, sequence)` and idempotency key.
- Files: private metadata/object table plus polymorphic links and checksum index.
- RBAC: stable role/permission keys, grants, scoped assignments; unique normalized keys.
- Finance: journals/entries with balanced-posting procedure, immutable POSTED state, reversal link, `numeric(20,6)` amounts and `numeric(20,8)` rates.
- Index every validated FK; partial indexes for active/open queues; composite indexes follow confirmed filters; avoid speculative indexes.

## Constraint examples
```sql
CHECK (amount >= 0);
CHECK (status IN ('PENDING','APPROVED','REJECTED','VOID')) -- only after status approval
UNIQUE (source_system, source_table, source_id);
UNIQUE (journal_id, line_no);
CHECK ((status = 'POSTED') = (posted_at IS NOT NULL));
```
Status checks above are PROPOSED, not mandatory until value profiling and business approval.

## Prisma-shaped pseudocode
```text
model Candidate { id UUID; legacyKeys LegacyKey[]; workflowEvents WorkflowEvent[]; documents DocumentLink[] }
model LegacyKey { sourceSystem String; sourceTable String; sourceId String; targetType String; targetId UUID; @@unique([sourceSystem,sourceTable,sourceId]) }
model WorkflowEvent { aggregateId UUID; sequence Int; eventKey String; occurredAt Instant; actorId UUID?; payload Json }
model Role { key String @unique; permissions RolePermission[]; assignments RoleAssignment[] }
model Permission { key String @unique; module String; action String; resource String }
model FileObject { id UUID; objectKey String @unique; sha256 Bytes; size BigInt; mime String; classification String }
model Journal { id UUID; status JournalStatus; entries LedgerEntry[]; reversalOf UUID?; legacyKeys LegacyKey[] }
model LedgerEntry { journalId UUID; lineNo Int; accountId UUID; currency String; debit Decimal(20,6); credit Decimal(20,6) }
```

Prisma cannot itself guarantee balanced multi-row journals or immutable history; use restricted SQL procedures/triggers plus transaction tests. Complete field proposals remain in `column-mapping.csv`.
""",
)

write(
    "10-api-migration-testing.md",
    """# 10 — API modules, migration architecture, and testing

## Modules
Identity/RBAC, Candidates, Partners, Workflow, Training, Documents, Finance, Invoicing, Travel/Flight, Medical/Compliance, Notifications, Reporting, Reference Data, and Migration/Admin. APIs are versioned, idempotent for commands, cursor-paginated, scoped by policy, and return legacy ID/label compatibility only through explicit adapters. File content is never public by path.

## Migration architecture
Offline extract parser → immutable staging tables → profiling/quarantine → approved transforms → target load → legacy-key resolution → deferred relationship validation → file copy/verification → reconciliation pack → business sign-off. Each run has manifest hashes, code/config version, counts, rejects, timings, and restart checkpoints. Use dual-read/shadow comparison before cutover; dual-write only if operations can support reconciliation. Backups are separate sources and excluded by default.

## Test gates
Schema parser tests; per-column transform tests; PK/FK/orphan/null/domain profiling; exact row-count and hash reconciliation; workflow transition/property tests; role × permission × row-scope policy tests for all 13 roles/246 permissions; report golden datasets and totals; document checksum/MIME/access/orphan tests; finance double-entry, idempotency, concurrency, rounding and reversal tests; timezone boundary tests; API contract/security/performance tests; rehearsal and rollback drills. Production cutover requires zero unexplained count/sum deltas and signed exceptions.
""",
)

write(
    "11-milestones-risks-approvals.md",
    """# 11 — Milestones, risks, and approval register

## M0–M15

### M0 Audit+baseline
- Outputs: frozen source/dump hashes, 90-table/1,245-column map, row/FK baseline, evidence and approval registers.
- Tests: parser repeatability; table/column completeness; no-PII artifact scan.
- Acceptance gate: current evidence totals reconcile and every discrepancy/UNVERIFIED item has an owner.

### M1 Foundation
- Outputs: PostgreSQL environments/schemas, deployment pipeline, observability, secrets and backup standards.
- Tests: migration rollback, restore drill, health checks, timezone/config tests.
- Acceptance gate: platform/security owners approve recoverability and environment parity.

### M2 Auth/RBAC/Audit
- Outputs: 13 stable role keys, 246 permission mappings, 714 grants, row scopes, append-only audit design.
- Tests: role × permission × route × row-scope matrix; document/report denial tests; audit tamper tests.
- Acceptance gate: all numeric-role compatibility mappings and exceptions are approved.

### M3 Candidate
- Outputs: candidate aggregate, profile/reference data, exact legacy lineage, status compatibility.
- Tests: 1:1 reconciliation, uniqueness/orphan checks, sensitive-field access and lifecycle tests.
- Acceptance gate: candidate owners approve fields, requiredness, duplicates and status semantics.

### M4 Recruitment
- Outputs: agent/sub-agent, agencier/companier masters, candidate agency/company snapshots, selection/employer flows.
- Tests: ownership/scope, matching without destructive merge, recruitment transition and report parity.
- Acceptance gate: master-versus-snapshot decisions and actor responsibilities are signed.

### M5 Training/Exam
- Outputs: separate profile training and manpower training modules, classes/schedules/exams/results.
- Tests: schedule ordering/conflicts, result publication, Teacher Schedule and Exam Result Sheet parity.
- Acceptance gate: training/exam owners approve domains, grading and publication rules.

### M6 Overseas Processing
- Outputs: medical, police clearance, ARC, visa, labour contract, license and flight workflows/documents.
- Tests: prerequisite transitions, expiry/date rules, live-status compatibility, document access.
- Acceptance gate: operations approves every transition, naming rule and exception path.

### M7 Finance
- Outputs: immutable balanced ledger, payment-request workflow, decimals/rates, reconciliation and reversal design.
- Tests: double-entry properties, idempotency/concurrency, rounding, opening balances, backup exclusion.
- Acceptance gate: Finance signs authoritative sources, balances, rounding and correction policy.

### M8 Invoice/Receipt/Ticket
- Outputs: versioned invoices/lines, receipts, allocations, ticket sub-ledger and print contracts.
- Tests: totals/tax/allocation, issue/void/reversal, numbering, all four print-output golden tests.
- Acceptance gate: Finance/Operations sign monetary and document parity.

### M9 Documents
- Outputs: private object metadata, checksums/MIME/size, Voyager JSON extraction, access/retention/orphan rules.
- Tests: byte checksum/size parity, MIME sniffing, malware workflow, authorization and orphan quarantine.
- Acceptance gate: Security/Legal approves classifications, retention and unresolved missing/orphan treatment.

### M10 Reports
- Outputs: 17 ACTIVE DB reports, 6 ACTIVE print outputs, legacy archive catalog and replacement contracts.
- Tests: golden rows/columns/totals/order/filters; PDF/Excel/CSV; authorization; empty/large datasets.
- Acceptance gate: each report has an owner and signed ACTIVE/LEGACY disposition with accepted defects fixed.

### M11 Notifications
- Outputs: notification/audience model, retention/partitioning, delivery preferences and retry/idempotency.
- Tests: volume/performance, duplicate delivery, retry/dead-letter, privacy and retention.
- Acceptance gate: Product/Legal approves retention and delivery semantics for dominant-volume tables.

### M12 Dashboard/Admin
- Outputs: scoped dashboards, admin/reference management, Voyager/BREAD replacement decisions.
- Tests: widget role visibility, admin CRUD policy, accessibility, performance and audit coverage.
- Acceptance gate: Product/Security approves every administrative capability and archived Voyager feature.

### M13 Migration
- Outputs: repeatable staging/transforms/loaders, lineage map, quarantine, files, manifests and reconciliations.
- Tests: two full rehearsals, restart/idempotency, 90-table/1,245-column/1,090,286-row accounting, FK/orphan checks.
- Acceptance gate: zero unexplained deltas; every signed exception is bounded and reversible.

### M14 Testing/UAT
- Outputs: integrated test evidence, UAT scripts/results, security/performance review and cutover rehearsal.
- Tests: end-to-end workflows, RBAC, finance, reports, documents, failure/rollback and operational support.
- Acceptance gate: business, security, finance and engineering sign production readiness.

### M15 Cutover
- Outputs: approved write freeze, final delta load/reconciliation, traffic switch, rollback point and hypercare.
- Tests: smoke/reconciliation/monitoring, backup verification, rollback trigger and support runbook.
- Acceptance gate: steering committee signs final metrics; legacy remains read-only until decommission approval.

## Principal risks and mitigations
- Nine declared FKs: profile the much larger logical relationship set and defer target constraints.
- Float money/mutable balances: exact decimals, immutable journal, signed reconciliation.
- Backup overlap: archive separately; approved authority/dedup rules.
- Dominant notification volume: partition/archive strategy and retention approval.
- Hard-coded role IDs/dynamic Voyager routes: stable keys and exhaustive policy tests.
- File JSON/path drift: tolerant parser, quarantine, checksum and orphan inventory.
- Unknown timezone/status semantics: compatibility adapter; no mandatory assumptions.
- Sensitive dump/source: generated artifacts contain metadata only, no row-level PII, secrets, hashes, or tokens.

## Explicit business approval register
| ID | Decision requiring approval | Owner | Status |
|---|---|---|---|
| A01 | Authoritative live payment table and backup exclusion/dedup | Finance + Data Owner | OPEN |
| A02 | Currency minor units, rate source, rounding, opening balances | Finance | OPEN |
| A03 | Candidate transition legality, actors, prerequisites, rollback | Operations | OPEN |
| A04 | Exact live-status compatibility labels/codes and retirement | Operations/Product | OPEN |
| A05 | Agency/company master versus candidate snapshot matching | Operations/Data | OPEN |
| A06 | Profile training versus manpower training semantics | Training/Operations | OPEN |
| A07 | Role stable-key names, row scopes, report/document permissions | Security/Business | OPEN |
| A08 | Legacy application timezone and DST interpretation | Business/Engineering | OPEN |
| A09 | File retention, classification, orphan handling, malware policy | Security/Legal | OPEN |
| A10 | ACTIVE/LEGACY disposition and owners for every report/print | Reporting Owners | OPEN |
| A11 | Data-quality exceptions and nullable-to-required constraints | Data Owners | OPEN |
| A12 | Notification retention/partition/archive horizon | Product/Legal | OPEN |
| A13 | Voyager pages/posts/settings/menu content to rebuild or archive | Product | OPEN |
| A14 | Police-clearance/ARC naming and external contract compatibility | Operations/API Owners | OPEN |
| A15 | Cutover strategy, write freeze, rollback point, legacy decommission | Steering Committee | OPEN |

Approval means named approver, date, decision, conditions, and linked evidence. OPEN items block only the affected mandatory rule/module—not evidence-preserving staging.
""",
)

evidence = {
    "generated_at_note": "Generation time intentionally omitted for reproducibility.",
    "canonical_source": "src/ (read-only)",
    "offline_sql": "docs/database-audit/u410970153_eujobbd.sql (read-only)",
    "sql_sha256": hashlib.sha256(SQL_PATH.read_bytes()).hexdigest(),
    "tables": len(tables),
    "columns": sum(len(x["columns"]) for x in tables.values()),
    "parsed_insert_rows": sum(x["rows"] for x in tables.values()),
    "declared_fk_constraints": len(declared_edges),
    "logical_relationships": len(logical_edges),
    "bread_data_types": len(records("data_types")),
    "roles": len(roles),
    "permissions": len(permissions),
    "role_grants": len(grants),
    "db_reports": len(records("reports")),
    "print_outputs": len(print_outputs),
    "legacy_report_rows": len([r for r in report_rows if r["classification"] == "LEGACY"]),
    "decisions": dict(sorted(decision_counts.items())),
    "unverified_categories": [
        "Legacy application timezone/DST interpretation",
        "Operational actor/prerequisite ownership for candidate transitions",
        "Physical file inventory, roots, MIME/size/checksum and retention",
        "Authoritative finance backup/deduplication policy",
        "Row scopes for Voyager dynamic routes and reports",
        "Business meaning of undocumented tables/columns beyond source evidence",
        "Status-domain completeness until distinct-value profiling is approved",
        "Report ownership and inactive/metadata-only disposition",
    ],
}
write("evidence-metrics.json", json.dumps(evidence, indent=2))

write(
    "evidence-register.md",
    f"""# Evidence register

| Evidence | Result | Classification |
|---|---:|---|
| CREATE TABLE definitions | {len(tables)} | CONFIRMED |
| Source columns | {evidence['columns']} | CONFIRMED |
| INSERT tuple rows (not AUTO_INCREMENT) | {evidence['parsed_insert_rows']:,} | CONFIRMED |
| Declared FK constraints | {len(declared_edges)} | CONFIRMED |
| Logical FK candidates | {len(logical_edges)} | INFERRED |
| Voyager/BREAD data types | {len(records('data_types'))} | CONFIRMED |
| Roles / permissions / grants | {len(roles)} / {len(permissions)} / {len(grants)} | CONFIRMED |
| DB reports / print outputs | {len(records('reports'))} / {len(print_outputs)} | CONFIRMED inventory; classifications mixed |

Canonical evidence is `src/`; dependencies under `src/vendor` were excluded from application-reference indexing. The SQL dump was read offline; no DB connection was used. `evidence-metrics.json` records the dump SHA-256 for provenance but no database row hashes, secrets, tokens, or row-level personal data are emitted.

## Prior-finding discrepancies

The supplied prior audit reported 1,082,959 parsed rows and eight declared FKs. The current dump independently yields **{sum(x['rows'] for x in tables.values()):,} INSERT tuples** (a **{sum(x['rows'] for x in tables.values()) - 1082959:+,}** difference) and **{len(declared_edges)} explicit FK constraints**. A second anchored count found {sum(1 for line in sql_lines if line.startswith('(')):,} tuple-like physical lines; the 47-line difference from the stateful count consists of tuple-like lines outside recognized INSERT blocks and is excluded. The ninth confirmed FK is `fk_candidates_replaced_by` on `candidates.replaced_by_candidate_id`. The current-file evidence is authoritative for this gate; the prior numbers are retained here as reconciliation evidence rather than silently overwritten.

Evidence labels: CONFIRMED = directly present in authorized source/dump; INFERRED = strong structural/code inference; PROPOSED = target design; UNVERIFIED = evidence absent or business confirmation required.
""",
)

readme_files = [
    "01-table-mapping.md", "table-mapping.csv", "column-mapping.csv", "source-references.csv",
    "02-relationships.md", "relationships.csv", "03-legacy-id.md", "04-workflow.md",
    "workflow-transitions.csv", "05-rbac.md", "rbac-permissions.csv", "rbac-role-grants.csv",
    "rbac-hardcoded-role-ids.csv", "06-finance.md", "finance-operations.csv", "07-reports.md",
    "report-catalog.csv", "08-documents.md", "document-fields.csv", "09-postgresql-prisma.md",
    "10-api-migration-testing.md", "11-milestones-risks-approvals.md", "evidence-register.md",
    "evidence-metrics.json",
    "generate_artifacts.py",
]
write(
    "README.md",
    "# ManPower modernization — final design gate\n\n"
    "This review pack is generated solely from the read-only canonical `src/` tree and offline SQL dump. "
    "Machine-readable CSV files are normative for complete matrices; Markdown files explain decisions and gates.\n\n"
    "## Index\n" + "\n".join(f"- [{x}]({x})" for x in readme_files) +
    "\n\n## Gate posture\n"
    "The design is evidence-complete at source inventory level. PROPOSED target rules are intentionally non-mandatory "
    "where evidence is UNVERIFIED. Open approvals are explicit in artifact 11. No raw row-level personal data, secrets, "
    "credential hashes, or tokens are included.\n",
)

print(json.dumps(evidence, indent=2))
