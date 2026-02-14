# Log Sentinel v3.0 — OpenCTI-Inspired Upgrade Plan

## Baseline (v2.0)

| Area | Status |
|------|--------|
| Tests | 43 passed (9.51s) |
| PHP | 8.2.28, Laravel 11.0 |
| Frontend | Velzon Bootstrap 5 + ApexCharts |
| ML Service | Flask 3.0 (Ensemble + SHAP + Temporal) |
| Models | User, ServerLog, Role |
| Auth | Laravel UI + Sanctum + Spatie Permission |

## Mapping: Existing → OpenCTI-Inspired

| Existing Feature | New Module | Notes |
|-----------------|------------|-------|
| Dashboard | Home/Dashboard | Keep + enhance with CTI widgets |
| Log Analysis | Observations | Anomalies become Sightings/Alerts |
| ML Predictions | Observations | Auto-create Observable nodes |
| Attack Simulation | Observations | Generates test observations |
| Feedback/Whitelist | Cases/Settings | Part of analyst workflow |
| — (new) | Knowledge | Node/Edge graph, STIX-inspired |
| — (new) | Threats | ThreatActor, Malware, Campaign |
| — (new) | Cases | Incident Response + Tasks |
| — (new) | Investigations | Graph workspace |
| — (new) | Data/Ingestion | Connectors, MITRE ATT&CK |
| — (new) | Settings | RBAC, Tokens, Taxonomy |

## Risk Mitigation

| Risk | Strategy |
|------|----------|
| Breaking existing tests | Run test suite after every migration |
| DB schema conflicts | UUID-based new tables, no FK to server_logs |
| UI conflicts | New layout extends existing master, sidebar swap |
| Performance | Indexed queries, pagination everywhere |

## Architecture

```
Browser → Laravel (Blade SSR)  → MySQL (Knowledge Graph + Cases + Logs)
              ↓ AJAX
        Flask ML Service (Python) → scikit-learn + SHAP
```

## New Database Tables

- `nodes` — Knowledge graph entities (UUID pk)
- `edges` — Relationships between nodes (UUID pk)
- `cases` — Incident response cases
- `case_tasks` — Tasks within cases
- `case_items` — Polymorphic pivot (node/edge → case)
- `tags` — Labels/taxonomy
- `taggables` — Polymorphic tagging
- `activity_logs` — Audit trail
- `integrations` — Connector/ingestion job registry
- `api_tokens` — Enhanced token tracking

## Module Menu (OpenCTI-inspired)

1. Home (Dashboard)
2. Knowledge (Entities + Graph)
3. Threats (Threat Actors, Malware, Campaigns)
4. Observations (Anomalies, Sightings, Alerts)
5. Cases (Incidents, Tasks)
6. Investigations (Graph workspace)
7. Data (Ingestion, Connectors)
8. Settings (Users, Roles, Tokens)
