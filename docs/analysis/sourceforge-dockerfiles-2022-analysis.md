# SourceForge 2022 DockerFiles Artifact Analysis

## Scope

This document records a static comparison of the SourceForge `wanemDockerFiles` artifacts published in March 2022. The artifacts are treated as historical SourceForge Docker-related material, not as authoritative upstream release evidence.

Current repository version model remains:

- Upstream/source baseline: WANem Beta 3.0.2
- Local repository version: WANem 3.0.3
- 3.0.3 is local repository metadata unless original upstream evidence proves otherwise

SourceForge folder:

- `https://sourceforge.net/projects/wanem/files/WANem/wanemDockerFiles/`

Temporary local extraction area used for this audit:

- `.tmp/sourceforge-wanem-dockerfiles/`

The downloaded archives and extracted files were not committed.

## Artifact Provenance

| Artifact filename | SourceForge URL | SourceForge modified date | Download date | File size | SHA256 | Archive type |
|---|---|---:|---:|---:|---|---|
| `WANem.tar.gz` | `https://sourceforge.net/projects/wanem/files/WANem/wanemDockerFiles/WANem.tar.gz/download` | 2022-03-18 | 2026-04-26 | 821,949 bytes | `53a667940f47886f93e6eaa54e0f8de86b81c54a2fe336b8713ccf65b7d751b5` | gzip-compressed tar archive |
| `root.tar` | `https://sourceforge.net/projects/wanem/files/WANem/wanemDockerFiles/root.tar/download` | 2022-03-18 | 2026-04-26 | 440,320 bytes | `9ceeef21bc43945e644e35f01df1b8f753634667fe868367aaf5542ecbb65c54` | POSIX tar archive |
| `sudoers` | `https://sourceforge.net/projects/wanem/files/WANem/wanemDockerFiles/sudoers/download` | 2022-03-17 | 2026-04-26 | 668 bytes | `4aaba5940ef4625e8e63f0a645355ec0576c9d9d3f8b3ba9e35974acdc62dce0` | ASCII text |

## Extracted Top-Level Structure

| Artifact | Extracted top-level structure | Observed content |
|---|---|---|
| `WANem.tar.gz` | `WANem/` | PHP web UI files, include files, CSS, images under `WANem/images/`, `wanemulator_all_about.pdf`, WANalyzer UI files, `ntopng.php`, `indexA.html`, `validate-advanced.inc.php`. |
| `root.tar` | `root/` | Root helper scripts, WANem shell scripts, disconnect subsystem, WANalyzer scripts, shell profile files, stale runtime files such as `input.dsc`, `firewall.out`, `ctrack.out`, `timers.out`, and `nohup.out`. |
| `sudoers` | single file | Full `/etc/sudoers` style file including the same `%www-data ALL=NOPASSWD` WANem command rule as this repository. |

## Docker-Specific Findings

| Question | Finding |
|---|---|
| Is there a Dockerfile? | No Dockerfile was found in the downloaded artifacts. |
| Is there a `docker-compose.yml`? | No compose or YAML file was found. |
| Which base image does it use? | Not knowable from these artifacts; no build recipe is present. |
| Does it require privileged mode? | Not explicitly stated in the artifacts. Based on WANem behavior, container runtime would need at least network administration privileges for `tc`, `iptables`, `brctl`, and conntrack behavior. |
| Does it use `NET_ADMIN`? | Not explicitly stated; likely required for a containerized lab if not using full privileged mode. |
| Does it use host networking? | Not specified. The scripts assume visible Linux interfaces and manipulate routing/firewall state in the active namespace. |
| Does it install Apache/PHP/net-tools/iproute2/iptables/bridge-utils/conntrack? | No install script or package list was found. The files assume those tools exist. |
| Does it copy WANem into `/var/www/WANem`? | No Dockerfile is present, but `WANem.tar.gz` appears structured as the web payload that would be copied to `/var/www/WANem`. |
| Does it copy root helpers into `/root`? | No Dockerfile is present, but `root.tar` is structured as a `/root` payload. |
| Does it modify sudoers? | The standalone `sudoers` artifact provides a complete sudoers file with WANem's `%www-data` rule. |
| Does it define entrypoint/startup behavior? | No entrypoint script or Docker command was found. Existing `root/wanem_init.sh` still uses the appliance-style startup model. |

## Comparison Table

| Artifact | Path inside artifact | Matching repo path | Same/different/new | Relevance | Suggested action |
|---|---|---|---|---|---|
| `WANem.tar.gz` | `WANem/*.php`, `WANem/*.inc.php` | `var/www/WANem/*.php`, `var/www/WANem/*.inc.php` | Different | Contains broad PHP compatibility edits: `<?php` tags, quoted superglobal keys, `count((array)...)`, and `call_user_func_array()` for pass-by-reference call sites. | consider integration |
| `WANem.tar.gz` | `WANem/config.inc.php` | `var/www/WANem/config.inc.php` | Different only in opening tag | Keeps the same command paths and sudo assumptions. | keep as provenance only |
| `WANem.tar.gz` | `WANem/command.inc.php` | `var/www/WANem/command.inc.php` | Different | Mostly PHP compatibility adjustments; also changes `restart_disconnect()` from `exec()` to `shell_exec()`. | needs manual review |
| `WANem.tar.gz` | `WANem/index-basic.php`, `WANem/index-advanced.php` | matching repo files | Different | Adds PHP compatibility changes and safer `isset()` checks for some form/session keys. | consider integration |
| `WANem.tar.gz` | `WANem/indexA.html` | none | New in artifact | Alternate advanced-mode entry using `validate-advanced.inc.php`. | needs manual review |
| `WANem.tar.gz` | `WANem/validate-advanced.inc.php` | none | New in artifact | Separate advanced validation file; overlaps with existing validation logic. | needs manual review |
| `WANem.tar.gz` | `WANem/ntopng.php` | none | New in artifact | Adds Ntopng redirect helper using `exec("ping -c 1 ntopng | ...")`; introduces another shell-execution path. | keep as provenance only |
| `WANem.tar.gz` | `WANem/images/*` | images currently live directly under `var/www/WANem/` | Different layout/new directory | Same general visual assets appear to be organized under an `images/` directory. | needs manual review |
| `WANem.tar.gz` | `WANem/title.html` | `var/www/WANem/title.html` | Missing from artifact | Current repository has title navigation not present in artifact. | conflicts with current repo |
| `WANem.tar.gz` | `WANem/wanemulator_all_about.pdf` | `var/www/WANem/wanemulator_all_about.pdf` | Present in both | Historical bundled PDF remains provenance, not maintained docs. | keep as provenance only |
| `root.tar` | `root/*.sh` | `root/*.sh` | Mixed: many same names differ | Contains root helper scripts with small runtime changes and path changes. | needs manual review |
| `root.tar` | `root/disc_new_port_int/disconnect.sh` | `root/disc_new_port_int/disconnect.sh` | Different | Uses `/proc/sys/net/netfilter/nf_conntrack_tcp_timeout_established`, removes `modprobe ip_conntrack`, and runs `/usr/sbin/conntrack -L`. | consider integration |
| `root.tar` | `root/wanalyzer/tcs_wanem.sh` | `root/wanalyzer/tcs_wanem.sh` | Different | Accepts `$ETH` for some commands but still applies loss/bandwidth to hard-coded `eth0`; removes some sudo calls. | conflicts with current repo |
| `root.tar` | `root/reset_wanem.sh` | `root/reset_wanem.sh` | Different | Uses `/etc/init.d/apache2 stop` rather than `service apache2 stop`; otherwise appliance-like. | keep as provenance only |
| `root.tar` | `root/.bashrc`, `root/.profile`, `root/.bash_history`, `root/.ssh/` | none | New in artifact | Shell profile material; `.bash_history` is empty in the downloaded artifact. | ignore |
| `root.tar` | `root/disc_new_port_int/input.dsc`, `firewall.out`, `ctrack.out`, `timers.out`, `nohup.out` | matching or related runtime files | Different/new runtime state | Runtime leftovers/logs rather than source. | ignore |
| `root.tar` | `root/disc_new_port_int/disconnect.sh_BKP` | none | New in artifact | Backup copy with alternate conntrack module comments. | keep as provenance only |
| `root.tar` | `root/nmAppletStart.sh`, `root/wanem_Old.sh`, `root/intfs.txt` | current repo only | Missing from artifact | Current repository has additional local/imported files not represented in 2022 artifact. | no action |
| `sudoers` | `sudoers` | `etc/sudoers` | Different header, same WANem `%www-data` command rule | Provides full sudoers context around the WANem rule. | keep as provenance only |
| All artifacts | `Dockerfile`, `docker-compose.yml`, package install scripts | none | Not found | No complete container build/run recipe was present. | needs manual review |

## Notable Differences

- The web artifact appears to have been edited for newer PHP compatibility rather than for Docker alone.
- Several PHP files switch from short opening tags to `<?php`.
- Many direct function calls with reference parameters are wrapped in `call_user_func_array()`.
- Many `count($value)` calls become `count((array)$value)`.
- Some unquoted `$_POST[...]` and `$_SESSION[...]` keys are changed to quoted keys with `isset()` checks.
- The artifact adds `ntopng.php`, `indexA.html`, and `validate-advanced.inc.php`.
- The root artifact contains stale runtime outputs and state files that should not be integrated as source.
- The sudoers artifact keeps the same high-risk passwordless `%www-data` command surface already documented in the runtime command inventory.

## Container Runtime Considerations

Based only on these artifacts and the current repository behavior, the 2022 SourceForge folder does not provide enough information to define a supported Docker runtime. It appears to provide files that could be copied into a container image, but it does not define the image, package installation, entrypoint, capabilities, networking mode, or startup behavior.

WANem's runtime behavior suggests a container approach would be suitable first for lab experimentation, development, smoke tests, and isolated network namespaces. It should not automatically be treated as appropriate for high-throughput or production-like packet routing without benchmark validation.

Known runtime implications:

- `tc`, `iptables`, bridge operations, and conntrack behavior require network administration privileges in the active network namespace.
- A Docker lab would likely require `CAP_NET_ADMIN` at minimum, or privileged mode if bridge/iptables/conntrack behavior cannot be scoped more narrowly.
- Host networking is not specified by the artifacts. If host networking is used, WANem commands may affect host interfaces directly.
- If container networking is used, WANem only sees interfaces inside that namespace unless additional interfaces or veth pairs are attached.
- The existing startup model still assumes `/root`, `/var/www/WANem`, Apache, PHP, `/etc/init.d/*`, and direct access to kernel networking controls.
- Performance remains untested. Any throughput, latency, jitter, or packet-loss accuracy claims require benchmark validation against the intended runtime mode.

## Recommended Treatment

- Do not integrate the 2022 artifacts directly.
- Preserve this analysis as provenance.
- Review PHP compatibility edits separately from Docker packaging.
- Treat `ntopng.php` as optional/historical until a supported monitoring strategy exists.
- Treat the sudoers file as confirmation of the historical privileged web execution model, not as a security model to copy forward.
- If a Docker lab is pursued, create a new reviewed Dockerfile under a future `packaging/docker/` or `deploy/docker/` path rather than copying these artifacts wholesale.
