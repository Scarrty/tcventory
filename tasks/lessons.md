# Lessons Learned

- 2026-03-12: Do not run `apply_patch` through `exec_command`; use direct file editing commands/tools instead.
- 2026-03-12: When asked to "plan next steps," deliver only planning artifacts; if user asks to implement afterward, execute concrete code/process changes (not another planning-only update).
- 2026-03-12: If the user asks to resolve PR conflicts, avoid broad unrelated doc churn; keep the fix minimal and scoped to conflict-causing files.
- 2026-03-12: If I start using `exec_command` with `apply_patch` and the user warns, immediately switch to direct file edits (e.g., heredoc/python) for all subsequent patches in the turn.
- 2026-03-12: When policy/tooling warns about patch method, immediately switch to direct file editing and avoid repeating the blocked method in the same run.
