# M5 Permission Catalogue

**Exact approved matrix. Runtime grants seeded with implementation GO.**

12 keys. No `*.delete`. `candidate.training.*` remains M3 CV/profile and is not merged with `training.manpower.*`.

| Key | super_admin | owner | administrator | employee | teacher | agent / sub_agent / agency | company / candidate / employer |
|---|---|---|---|---|---|---|---|
| `training.teacher.read` | all | all | all | all | **self only** | — | — |
| `training.teacher.manage` | yes | yes | yes | yes | — | — | — |
| `training.class_group.read` | yes | yes | yes | — | — | — | — |
| `training.class_group.manage` | yes | yes | yes | — | — | — | — |
| `training.schedule.read` | yes | yes | yes | — | — | — | — |
| `training.schedule.manage` | yes | yes | yes | — | — | — | — |
| `training.exam.read` | yes | yes | **NO** | — | **NO** | — | — |
| `training.exam.manage` | yes | yes | **NO** | — | **NO** | — | — |
| `training.exam_result.read` | yes | yes | **NO** | — | **NO** | — | — |
| `training.exam_result.manage` | yes | yes | **NO** | — | **NO** | — | — |
| `training.manpower.read` | yes | yes | yes | yes | — | candidate-scoped | — |
| `training.manpower.manage` | yes | yes | yes | yes | — | candidate-scoped | — |
| `candidate.read` (M4) | yes | yes | yes | yes | **NO** | scoped | company scoped / candidate self / employer no |

Teacher self-scope: `teacher.id = current_user.teacher_id`. Unbound teacher sees zero teacher rows.

Administrator exam-grant widening remains **NOT APPROVED**.
