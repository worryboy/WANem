# SourceForge 2022 Integration Candidate Review

## Scope

This document reviews the differences found in the SourceForge 2022 `wanemDockerFiles` payloads as possible future integration candidates. It is an integration review only; no source files from those artifacts have been integrated.

The previous artifact analysis found no `Dockerfile`, `docker-compose.yml`, package install script, entrypoint, capability declaration, or complete Docker runtime recipe. The artifacts should therefore be treated as historical payloads requiring comparison, not as an authoritative Docker implementation or upstream release.

Current repository version model remains:

- Upstream/source baseline: WANem Beta 3.0.2
- Local repository version: WANem 3.0.3
- 3.0.3 is local repository metadata unless original upstream evidence proves otherwise

This review is based on [SourceForge 2022 DockerFiles artifact analysis](sourceforge-dockerfiles-2022-analysis.md). The temporary extracted artifacts were not present when this review was written. Before any actual source integration, re-download and re-extract the artifacts, verify their SHA256 checksums, and repeat the file-level comparison.

## Candidate Summary

| Candidate | Source artifact path | Current repo path | Category | Potential benefit | Risk | Integration recommendation | Requires test? |
|---|---|---|---|---|---|---|---|
| Short PHP opening tag replacements | `WANem/*.php`, `WANem/*.inc.php` | `var/www/WANem/*.php`, `var/www/WANem/*.inc.php` | A. PHP compatibility candidates | Improves compatibility when `short_open_tag` is disabled. | Low for pure tag changes, but must be isolated from behavior changes. | integrate after review | Yes: PHP parse/static check |
| Quoted array and superglobal keys | `WANem/*.php`, `WANem/*.inc.php` | `var/www/WANem/*.php`, `var/www/WANem/*.inc.php` | A. PHP compatibility candidates | Avoids undefined constant behavior and newer PHP notices/errors. | Low to medium; safe when purely quoting keys, riskier when combined with changed branching. | integrate after review | Yes: UI form smoke tests |
| `isset()` guards for request/session keys | `WANem/index-basic.php`, `WANem/index-advanced.php`, related includes | Matching files under `var/www/WANem/` | A. PHP compatibility candidates | Reduces undefined index warnings and clarifies absent-form-field behavior. | Medium; missing values may now skip old fallback paths. | compare manually first | Yes: basic and advanced form workflows |
| `count((array)...)` compatibility edits | `WANem/*.php`, `WANem/*.inc.php` | Matching files under `var/www/WANem/` | A. PHP compatibility candidates | Avoids newer PHP `count()` warnings for null or scalar values. | Medium; casting scalar or null values can change branch decisions. | compare manually first | Yes: validation and generated-command tests |
| `call_user_func_array()` pass-by-reference adaptations | `WANem/*.php`, `WANem/*.inc.php` | Matching files under `var/www/WANem/` | A. PHP compatibility candidates | May preserve reference-parameter behavior on newer PHP versions. | Medium to high; can alter call semantics and hide argument-shape assumptions. | needs runtime test | Yes: command generation and validation tests |
| `restart_disconnect()` changed from `exec()` to `shell_exec()` | `WANem/command.inc.php` | `var/www/WANem/command.inc.php` | B. Security-sensitive command execution changes | May capture output or change blocking/error behavior. | High; changes shell execution semantics in privileged workflow. | needs security review | Yes: isolated disconnect restart test |
| `ntopng.php` monitoring redirect helper | `WANem/ntopng.php` | None | C. New optional features | Could add an optional link to Ntopng monitoring. | High; introduces `exec("ping ...")` and an additional optional service dependency. | needs security review | Yes: security review and service integration test |
| Alternate advanced entry page | `WANem/indexA.html` | None | C. New optional features | May represent an alternate advanced-mode entry flow. | Medium; overlaps current advanced page and could bypass existing validation assumptions. | compare manually first | Yes: advanced UI workflow tests |
| Separate advanced validation include | `WANem/validate-advanced.inc.php` | None | C. New optional features | May split advanced validation from general validation. | Medium to high; validation changes directly affect privileged command construction. | compare manually first | Yes: validation and command-output tests |
| Disconnect conntrack path changes | `root/disc_new_port_int/disconnect.sh` | `root/disc_new_port_int/disconnect.sh` | D. Runtime helper script changes | Uses newer `nf_conntrack` sysctl path and `/usr/sbin/conntrack`. | High; affects firewall/connection tracking behavior and depends on kernel/tool paths. | needs runtime test | Yes: isolated namespace or disposable VM test |
| Disconnect backup script | `root/disc_new_port_int/disconnect.sh_BKP` | None | G. Provenance only | Shows alternate historical comments around conntrack module handling. | Medium; backup file should not become maintained source. | keep as provenance only | No, unless manually mined |
| WANalyzer traffic shaping changes | `root/wanalyzer/tcs_wanem.sh` | `root/wanalyzer/tcs_wanem.sh` | E. WANalyzer changes | Some commands use `$ETH` instead of assuming one interface. | High; artifact still applies some shaping to hard-coded `eth0`, so behavior is inconsistent. | compare manually first | Yes: isolated WANalyzer test |
| Sudoers artifact | `sudoers` | `etc/sudoers` | G. Provenance only | Confirms historical `%www-data` passwordless command model. | High; preserves broad privileged web execution. | keep as provenance only | No for integration; yes for future sudoers redesign |
| Stale disconnect runtime files | `root/disc_new_port_int/input.dsc`, `firewall.out`, `ctrack.out`, `timers.out`, `nohup.out` | Runtime paths under `root/disc_new_port_int/` | F. Files to ignore | None as maintained source. | High if committed as source: stale state could be mistaken for configuration or test data. | do not integrate | No |
| Shell profile/history leftovers | `root/.bashrc`, `root/.profile`, `root/.bash_history`, `root/.ssh/` | None | F. Files to ignore | None unless a specific profile line is later proven useful. | Medium; personal or runtime environment leftovers do not belong in maintained source. | do not integrate | No |
| Image directory layout changes | `WANem/images/*` | Images currently live directly under `var/www/WANem/` | C. New optional features | Could clarify static asset organization. | Medium; path changes may break existing HTML references. | compare manually first | Yes: UI asset rendering check |
| Historical bundled PDF | `WANem/wanemulator_all_about.pdf` | `var/www/WANem/wanemulator_all_about.pdf` | G. Provenance only | Confirms historical documentation payload. | Low; maintained docs should remain Markdown and version-neutral. | keep as provenance only | No |

## PHP Compatibility Candidates

The most promising future integration candidates are the PHP compatibility edits, but they should be split into small patches instead of imported wholesale.

Likely lower-risk syntax compatibility changes:

- Replacing short opening tags with `<?php`.
- Quoting unquoted array keys and superglobal keys when no surrounding control flow changes.
- Adding simple `isset()` guards where missing values previously produced only undefined index noise.

Changes requiring closer review:

- `isset()` guards that change whether a branch runs. These can alter default behavior for unchecked checkboxes, missing form fields, or session state.
- `count((array)$value)` changes. They may avoid newer PHP warnings, but null, scalar, and string values can produce different counts than old assumptions intended.
- `call_user_func_array()` pass-by-reference adaptations. These may be necessary for newer PHP compatibility, but they should be reviewed at each call site because argument shape and reference behavior affect validation and command generation.

Behavior-changing edits must not be bundled with low-risk syntax cleanup. A future PHP compatibility branch should first apply mechanical parse-safe changes, then separately review validation and function-call behavior.

## Security-Sensitive Command Execution Changes

Any change that introduces, removes, or changes shell execution is security-sensitive because WANem's web UI can reach privileged networking commands through sudo.

| Change | Why it is sensitive | Recommended next step |
|---|---|---|
| `restart_disconnect()` changes from `exec()` to `shell_exec()` in `command.inc.php` | Changes command execution semantics and may alter output handling, blocking behavior, and error visibility. | Review exact command construction after re-extraction and test only in an isolated lab. |
| `ntopng.php` runs `exec("ping -c 1 ntopng | ...")` | Adds a new shell execution path in the web tree, even if the target string appears static. | Treat as optional feature work requiring security review before integration. |
| `disconnect.sh` switches to `nf_conntrack` sysctl paths and `/usr/sbin/conntrack -L` | Touches kernel networking state and relies on absolute tool paths. | Compare against supported target kernels and test in disposable namespace or VM. |
| `sudoers` keeps `%www-data ALL=NOPASSWD` WANem command access | Confirms the historical broad privileged web model rather than improving it. | Keep as provenance; redesign sudoers later around a narrow privileged wrapper. |

## New Optional Files

### `ntopng.php`

`ntopng.php` appears to discover or confirm an `ntopng` service by shelling out to `ping`, then redirecting the browser toward port `3000`. It overlaps with the broader optional monitoring-service discussion and should not be integrated as part of PHP compatibility work.

Recommendation: keep as optional future work requiring a feature decision, security review, dependency documentation, and a runtime test with an actual Ntopng service.

### `indexA.html`

`indexA.html` appears to be an alternate advanced-mode entry page that uses `validate-advanced.inc.php`. It overlaps with the current advanced workflow and may represent either an experiment or an incomplete UI split.

Recommendation: compare manually after re-extraction. Do not integrate until the current advanced workflow has form-level tests.

### `validate-advanced.inc.php`

`validate-advanced.inc.php` appears to separate advanced validation from existing validation logic. Because validation feeds command construction, this is not a harmless documentation or UI-only change.

Recommendation: compare manually and test generated commands before any integration. Treat it as behavior-changing until proven otherwise.

## Runtime Helper And WANalyzer Changes

The `disconnect.sh` changes may be useful because they reference the newer `nf_conntrack` sysctl path and use `/usr/sbin/conntrack`. They should be evaluated as a runtime modernization candidate, not imported as part of Docker packaging.

The `tcs_wanem.sh` differences are mixed. The artifact appears to use `$ETH` in some places while retaining hard-coded `eth0` in other shaping commands. That makes it a candidate for manual study, not direct integration.

## Files To Ignore

The following artifact content should not be integrated as maintained source:

- `root/disc_new_port_int/input.dsc`
- `root/disc_new_port_int/firewall.out`
- `root/disc_new_port_int/ctrack.out`
- `root/disc_new_port_int/timers.out`
- `root/disc_new_port_int/nohup.out`
- Shell history, profile, or SSH leftovers unless a specific line is later proven to be intentional maintained configuration

These files are runtime state, logs, backups, or environment leftovers. They may be useful only as historical clues.

## Recommended Future Integration Order

1. Re-download and re-extract `WANem.tar.gz`, `root.tar`, and `sudoers`; verify checksums against the previous artifact analysis.
2. Create a mechanical PHP syntax patch for short tags and plainly quoted keys only.
3. Add PHP parse checks and form workflow smoke tests before changing validation behavior.
4. Review `isset()`, `count((array)...)`, and `call_user_func_array()` changes as separate behavior-sensitive patches.
5. Review `disconnect.sh` conntrack changes in an isolated runtime environment.
6. Decide whether `ntopng.php` is a supported optional feature before integrating any monitoring UI.
7. Keep stale runtime files and sudoers payloads as provenance only.
