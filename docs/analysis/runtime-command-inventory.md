# Runtime Command Inventory

## Scope

This is a static inventory of command execution, external tool usage, and hard-coded paths in the current repository tree. It does not change behavior and does not validate runtime safety.

Related analysis:

- [Runtime assumptions audit](runtime-assumptions-audit.md)
- [Modernization backlog](modernization-backlog.md)
- [Legacy dependencies](../files/legacy-dependencies.md)
- [Compatibility notes](../files/compatibility-notes.md)

## Command Execution Inventory

| Caller file | Function / section | Command or tool | Execution method | Uses sudo? | Input source | Hard-coded paths | Risk | Notes |
|---|---|---|---|---|---|---|---|---|
| `var/www/WANem/config.inc.php` | global config | `brctl`, `tc`, `ifconfig`, disconnect helpers, WANalyzer | Defines command strings | `tc` and WANalyzer include `sudo` | Static config | `/sbin/brctl`, `/sbin/tc`, `/sbin/ifconfig`, `/tmp/netemstate.txt`, `/root/disc_new_port_int`, `/root/wanalyzer` | High | Central command/path configuration used by PHP command builders. |
| `var/www/WANem/command.inc.php` | `make_command()` | `tc qdisc add`, `tc filter add` | PHP `exec($command)` | Yes, via `$tc_CMD` | Interfaces from `/sys/class/net`; POST-derived delay/loss/jitter/filter values after validation | `/sbin/tc` via config | High | Core WANem rule creation path; mutates live qdisc state. |
| `var/www/WANem/command.inc.php` | `bandwidth_command()` | `tc qdisc add`, `tc class add` | PHP `exec($command)` | Yes, via `$tc_CMD` | POST-derived bandwidth and selected interface | `/sbin/tc` via config | High | Applies HTB bandwidth controls. |
| `var/www/WANem/command.inc.php` | `ip_command()` | `tc filter add ... match ip ...` | PHP `exec($command)` | Yes, via `$tc_CMD` | POST-derived source, destination, subnet, port, symmetry | `/sbin/tc` via config | High | User-entered filter values reach shell command after validation. |
| `var/www/WANem/command.inc.php` | `disconnect_command()` | `sudo echo "... " >> input.dsc` | PHP `exec($command)` with shell redirection | Yes | POST-derived disconnect type/timers/source/destination/port/interface | `/root/disc_new_port_int/input.dsc` | High | Writes disconnect rules through shell command construction and redirection. |
| `var/www/WANem/command.inc.php` | `reset_tc()` | `tc qdisc del dev ... root` | PHP `exec($command)` | Yes, via `$tc_CMD` | Interface list from current tree/UI state | `/sbin/tc` via config | High | Removes qdisc state from selected interfaces. |
| `var/www/WANem/command.inc.php` | `reset_tc()` | `sudo reset_disc.sh ... > /dev/null &` | PHP `shell_exec($command)` | Yes | Interface list from current tree/UI state | `/root/disc_new_port_int/reset_disc.sh` | High | Background reset of disconnect subsystem. |
| `var/www/WANem/command.inc.php` | `restart_disconnect()` | `sudo disconnect.sh ... > /dev/null &` | PHP `exec($command)` | Yes | Advanced-mode disconnect settings | `/root/disc_new_port_int/disconnect.sh` | High | Starts long-running disconnect manager from PHP. |
| `var/www/WANem/index-basic.php` | Start/stop WANem | Stored commands from `netemstate.txt` | PHP `exec($storedCommands)` | Stored commands may include sudo | Previously generated command file | `/tmp/netemstate.txt` | High | Replays command text read from writable state file. |
| `var/www/WANem/index-advanced.php` | Start/stop WANem | Stored commands from `netemstate.txt` | PHP `exec($storedCommands)` | Stored commands may include sudo | Previously generated command file | `/tmp/netemstate.txt` | High | Same replay model as basic mode. |
| `var/www/WANem/upload.php` | Restore saved state | Uploaded `netemstate.txt` contents | PHP `copy()`, then `exec($storedCommands)` | Uploaded commands may include sudo | Uploaded file `$_FILES['ufile']` | `/tmp/netemstate.txt` | Critical | Uploaded file content can become command text for execution. |
| `var/www/WANem/status.php` | interface status | `tc filter show`, `tc -s qdisc`, `tc class show` | PHP `shell_exec()` | Usually yes through `$tc_CMD`; one direct `/sbin/tc` call omits sudo | POST interface list or parsed current state | `/sbin/tc` | Medium | Read-oriented status commands, but selected interface is concatenated into shell command. |
| `var/www/WANem/status.php` | disconnect status | `check_disco.sh`, `sudo grep ... input.dsc | grep <interface>` | PHP `shell_exec()` | Yes for grep pipeline | Selected interface | `/root/disc_new_port_int`, `/bin/grep` via sudoers | Medium | Pipeline includes interface text in shell command. |
| `var/www/WANem/currval.inc.php` | current values | `tc -d qdisc`, `tc filter show`, `tc class show` | PHP `shell_exec()` | Yes, via `$tc_CMD` | Interface list | `/sbin/tc` via config | Medium | Read-oriented, but interface names are concatenated. |
| `var/www/WANem/currval-advanced.inc.php` | current values | `tc filter show`, `tc class show`, `check_disco.sh`, `grep input.dsc` | PHP `shell_exec()` | Yes | Selected interface | `/sbin/tc`, `/root/disc_new_port_int` | Medium | Same pattern as status/current value inspection. |
| `var/www/WANem/result.php` | WANalyzer result | `sudo /root/wanalyzer/tcs_wanc_menu.sh <ip>` | PHP `shell_exec($command." 2>&1")` | Yes, via `$wanchar_DIR` | `$_REQUEST['pc']` | `/root/wanalyzer` | Critical | Request parameter is appended to sudo shell command. |
| `var/www/WANem/wanem.php` | WANalyzer apply | `sudo /root/wanalyzer/tcs_wanem_main.sh` | PHP `shell_exec($command." 2>&1")` | Yes, via `$wanchar_DIR` | Static command; input comes from `/tmp/tcs_wanc_report.csv` | `/root/wanalyzer`, `/tmp` | High | Runs WANalyzer-emulation script with sudo. |
| `etc/sudoers` | `www-data` rule | `tc`, `echo`, `mv`, `grep`, disconnect scripts, `conntrack`, WANalyzer scripts | sudoers policy | Yes, passwordless | Web server user | `/sbin/tc`, `/bin/echo`, `/bin/mv`, `/bin/grep`, `/root/...`, `/usr/sbin/conntrack` | Critical | Broad root command surface for PHP/web context. |
| `root/wanem.sh` | console NAT commands | `iptables -t nat`, `ifconfig`, `route` | Shell commands and backticks | Script expected to run as root | Interactive console input | `/sbin/iptables`, `/sbin/route` | High | Adds/deletes NAT on selected interface. |
| `root/wanem.sh` | bridge commands | `brctl`, `ifconfig`, `route`, `pump` | Shell commands and backticks | Script expected to run as root | Interactive bridge name/NIC list | `/sbin/brctl`, `/sbin/ifconfig`, `/sbin/route`, `/sbin/pump` | High | Creates/deletes bridges and reconfigures interfaces. |
| `root/wanem.sh` | reset/status/startx | `/root/reset_wanem.sh`, `/root/wanem_reset.sh`, `startx`, `ifconfig`, `route`, `ping` | Shell commands/backticks | Script expected to run as root | Interactive console input for ping/route helper | `/root`, `/var/www/WANem/About.txt` | Medium | Appliance console entry point. |
| `root/wanem_init.sh` | boot initialization | `ifconfig`, `network-manager`, `apache2`, `ajaxterm`, `startx`, helper scripts | Shell commands/backticks | Root | Runtime NIC/service state | `/root`, `/etc/init.d/*`, `/usr/bin/startx`, `/proc/sys/.../eth0/...` | High | Starts services, mutates MTU and redirect settings, launches desktop and shell. |
| `root/reset_wanem.sh` | appliance reset | `service apache2 stop`, `ifconfig`, `/root/networkReset.sh`, `/root/eth_setup.sh`, `/etc/init.d/apache2 start`, `rm` | Shell commands/backticks | Root | Runtime interface state | `/root`, `/etc/init.d/apache2` | High | Restarts network and web service. |
| `root/wanem_reset.sh` | WANem reset | `ifconfig`, `tc qdisc del`, `reset_all_disc.sh` | Shell commands | Root | Interfaces from `ifconfig` | `/root/disc_new_port_int` | High | Deletes qdisc state for all non-loopback interfaces. |
| `root/networkDevices.sh`, `root/newNetworkDevices.sh` | NIC detection | `ifconfig`, `ethtool`, `pump`, `ifup`, `network-manager restart` | Shell commands/backticks | Root for configuration | `/proc/net/dev` and NIC names | `/etc/init.d/network-manager` | Medium | Interface discovery and activation. |
| `root/networkReset.sh` | NetworkManager reset | `nmcli con down/up` | Shell backticks and commands | Root or NetworkManager privileges | UUIDs from `nmcli` | `/usr/bin/nmcli` | Medium | Resets all NetworkManager connections. |
| `root/disc_new_port_int/disconnect.sh` | disconnect manager | `iptables -F FORWARD`, `modprobe ip_conntrack`, AWK scripts | Shell commands | Root | `input.dsc` | `/proc/sys/net/ipv4/netfilter/...` | High | Flushes FORWARD chain and runs disconnect AWK processor. |
| `root/disc_new_port_int/disc.awk`, `disco.awk` | disconnect rules | `iptables -I/-D FORWARD`, `conntrack -D/-L`, `rm timers.out`, `sleep` | AWK `system(cmd)` | Root when run through sudo/script | `input.dsc`, conntrack output | `/tmp` indirectly, disconnect directory | High | Constructs firewall commands from disconnect rule fields. |
| `root/disc_new_port_int/reset_disc.sh`, `reset_all_disc.sh` | disconnect reset | `mv`, `rm`, `chown -R www-data` | Shell commands | Root | Script arguments | `$1/input.dsc`, disconnect directory | High | File operations and ownership changes on caller-supplied directory argument. |
| `root/wanalyzer/tcs_wanc_menu.sh` | WANalyzer menu | `ping`, `tcs_wanc_main.sh`, `tcs_bw_main.sh`, writes report | Shell commands | Via sudo from PHP | Target IP argument | `/tmp/tcs_wanc_report.csv`, `/root/wanalyzer` | High | User-supplied target reaches ping scripts. |
| `root/wanalyzer/tcs_wanc_main.sh`, `tcs_bw_main.sh` | WAN characterization | `ping`, `awk`, `rm`, writes dumps | Shell commands/pipelines | Via sudo from PHP | Target host argument | `/tmp/*.dmp`, `/root/wanalyzer/*.awk` | High | Target host argument is passed to shell commands. |
| `root/wanalyzer/tcs_wanem_main.sh`, `tcs_wanem.awk`, `tcs_wanem.sh` | Apply WANalyzer-derived emulation | `awk`, `sudo /sbin/tc qdisc/class` | Shell and AWK `system(cmd)` | Yes | `/tmp/tcs_wanc_report.csv` | `/root/wanalyzer`, `/tmp`, hard-coded `eth0` | High | Applies tc rules to `eth0` from report-derived values. |
| `etc/apache2/sites-available/000-default.conf` | Apache vhosts | DocumentRoot, SSL cert, reverse proxies | Apache config | Service/root during deployment | HTTP request routing | `/var/www`, `/etc/apache2/ssl/apache2.pem` | Medium | Proxies Webmin and Netdata; assumes appliance layout. |

## Hard-Coded Path Inventory

| Path | Referenced in | Purpose | Runtime assumption | Modernization concern | Suggested handling |
|---|---|---|---|---|---|
| `/root` | PHP config, shell scripts, sudoers, docs | Operational script home | WANem runs as root-owned appliance tree | Not portable to containers or package layouts | Inventory every `/root` script and decide preserve-vs-configure. |
| `/root/disc_new_port_int` | `config.inc.php`, `command.inc.php`, `status.php`, sudoers, reset scripts | Disconnect tool directory and state files | Writable/owned for web helper workflow | High privilege and shell redirection target | Move behind one configuration value before refactor. |
| `/root/wanalyzer` | `config.inc.php`, WANalyzer scripts, sudoers | WANalyzer scripts and AWK processors | Web UI can run scripts through sudo | Coupled to root path and sudoers | Treat as privileged helper subsystem. |
| `/var/www` | Apache config, docs, shell console | Apache document root | Debian/Apache appliance layout | Modern images may use `/var/www/html` or app-specific paths | Document target layout before packaging. |
| `/var/www/WANem` | console scripts and docs | WANem web UI path | App served at `/WANem` | Path-sensitive links and scripts | Keep as compatibility path until routing is redesigned. |
| `/etc/apache2` | Apache config and scripts | Apache service/config/SSL path | Debian Apache layout | Not portable to other distros/images | Preserve in VM baseline; parameterize later. |
| `/etc/php5` | PHP config files | PHP 5 era runtime config | Legacy PHP packaging | Incompatible with modern PHP packages | Keep as historical runtime evidence. |
| `/tmp/netemstate.txt` | `config.inc.php`, basic/advanced/upload flows | Stored command replay state | Web process can read/write `/tmp` | Command text in shared temp path | Replace with controlled state storage after audit. |
| `/tmp/tcs_wanc_report.csv` | WANalyzer scripts and AWK | WANalyzer result exchange | Shared temp report file | Collision/tampering risk | Use private runtime directory in future design. |
| `/tmp/*.dmp`, `/tmp/tempf` | WANalyzer scripts | Ping/bandwidth temporary dumps | Shared writable temp directory | Collision/tampering risk | Use `mktemp` or private working dir later. |
| `/etc/init.d/apache2` | `wanem_init.sh`, `reset_wanem.sh`, Debian default page | Apache service control | SysV init script exists | Not container/systemd friendly | Replace with target runtime service plan. |
| `/etc/init.d/network-manager` | `wanem_init.sh`, `newNetworkDevices.sh` | Network service restart | NetworkManager SysV script exists | Host/container mismatch | Decide whether WANem owns network service management. |
| `/etc/init.d/ajaxterm` | `wanem_init.sh` | Remote terminal daemon startup | Ajaxterm installed as SysV service | Optional/high-risk service | Decide supported vs historical. |
| `/sbin/tc` | PHP config, status paths, WANalyzer | Traffic control/netem | iproute2 installed at legacy path | Path differs on some systems | Resolve via config and capability checks. |
| `/sbin/ifconfig` | PHP config, shell scripts | Interface discovery/config | net-tools installed | Legacy/deprecated tool | Map to `iproute2` later. |
| `/sbin/iptables` | shell scripts | NAT/firewall mutation | iptables legacy path | nftables/iptables compatibility varies | Decide backend support. |
| `/sbin/brctl`, `/usr/sbin/brctl` | bridge scripts/current and old | Bridge control | bridge-utils installed | Legacy bridge tooling | Test bridge behavior before replacement. |
| `/usr/sbin/conntrack` | sudoers and disconnect scripts | Conntrack inspection/deletion | conntrack installed | Kernel/tool version sensitive | Validate package/module names. |
| `/usr/bin/startx` | `wanem_init.sh` | Desktop startup | X desktop exists | Not headless/container friendly | Treat as appliance/historical unless retained. |
| `/usr/bin/nmcli` | `networkReset.sh` | NetworkManager connection reset | NetworkManager CLI exists | May reset unrelated host connections | Isolate to lab before running. |
| `/proc/net/dev` | interface helper scripts | Interface enumeration | Linux procfs available | Container namespace dependent | Model namespace expectations. |
| `/proc/sys/net/ipv4/...` | `wanem_init.sh`, `disconnect.sh` | Kernel networking controls | Writable sysctls/modules | Requires host-level privileges | Document required capabilities. |
| `/sys/class/net` | `find_3.0.inc.php` | PHP interface/bridge discovery | Linux sysfs readable by web process | Namespace/path dependency | Preserve read-only discovery or abstract later. |

## External Dependencies

| Dependency | Referenced in | Required for feature | Privilege level | Present in repo? | Modernization note |
|---|---|---|---|---|---|
| apache2 | `etc/apache2`, `wanem_init.sh`, `reset_wanem.sh` | Web UI serving | Root for service management | Config only | Define Apache vs alternate web runtime. |
| PHP | `etc/php5`, `var/www/WANem/*.php`, `*.inc.php` | Web UI | Web server user plus sudo helpers | Source/config only | PHP 5 assumptions need compatibility testing. |
| sudo | `config.inc.php`, `command.inc.php`, `etc/sudoers` | Privileged web execution | Root policy, web user invocation | Policy file only | Central security review item. |
| tc / iproute2 | PHP command builders, status, WANalyzer | Netem, qdisc, class/filter control | Root or `CAP_NET_ADMIN` | No binary | Core runtime dependency. |
| ifconfig / net-tools | shell scripts, PHP config | Interface discovery/config | Root for config | No binary | Legacy; map to `ip` later. |
| iptables | NAT and disconnect scripts | NAT/firewall/disconnect | Root | No binary | Decide legacy vs nftables strategy. |
| bridge-utils / brctl | PHP config, bridge shell commands | Bridge mode | Root | No binary | Legacy; test before replacing. |
| conntrack | sudoers, disconnect scripts/AWK | Disconnect flow tracking | Root | No binary | Package/kernel coupling likely. |
| ajaxterm | `wanem_init.sh`, `remoteTerminal.php` | Browser terminal | Daemon/shell access | No binary | Treat as optional/high-risk until validated. |
| webmin | Apache reverse proxy | Admin UI proxy | Separate service | No binary | Intent not verified; likely optional/appliance-specific. |
| netdata | Apache reverse proxy | Monitoring proxy | Separate service | No binary | Intent not verified; likely optional/appliance-specific. |
| xinit / startx | `wanem_init.sh`, `wanem.sh`, `remoteTerminal.php` | Desktop session | Local session/root in appliance | No binary | Not container-friendly. |
| bash | Shell scripts under `root/` | Runtime scripting | Varies by script | Scripts only | Preserve until scripts are characterized. |
| awk | WANalyzer, disconnect scripts, shell pipelines | Parsing and command generation | Sometimes root via script | AWK source only | AWK `system()` paths need review. |
| sed / grep / cut / sort / wc | shell and PHP command pipelines | Text parsing | Usually unprivileged; grep allowed via sudoers | No binaries | Prefer structured parsing in later modernization. |
| ping | WANalyzer, console status | Reachability and measurement | May require capability/setuid | No binary | Validate package/capability in runtime image. |
| NetworkManager / nmcli | network reset scripts | Network connection management | Root or policy privileges | No binary | Dangerous on non-appliance hosts. |
| pump / ifup / ethtool | interface helper scripts | DHCP/link/interface activation | Root | No binary | Legacy network stack assumptions. |

## Top 10 Highest-Risk Command Execution Paths

| # | Source file | Command pattern | Why it is risky | User-controlled input reaches command? | Recommended next step |
|---:|---|---|---|---|---|
| 1 | `var/www/WANem/upload.php` | Uploaded `netemstate.txt` copied to `/tmp/netemstate.txt`, then `exec($storedCommands)` | Uploaded file content can become shell command text. | Yes, via uploaded file content. | Disable in any runtime test until restore format and command replay model are redesigned. |
| 2 | `var/www/WANem/result.php` | `sudo /root/wanalyzer/tcs_wanc_menu.sh $ip` | Request parameter is appended to a sudo shell command. | Yes, `$_REQUEST['pc']`. | Validate or remove direct shell concatenation before exposure. |
| 3 | `var/www/WANem/command.inc.php` | `sudo echo "... POST-derived fields ..." >> /root/disc_new_port_int/input.dsc` | Shell redirection and quoted string construction write privileged rule input. | Yes, advanced disconnect fields after validation. | Replace with file API writes and strict serialization before modernization. |
| 4 | `var/www/WANem/index-basic.php`, `index-advanced.php` | `exec($storedCommands)` from `/tmp/netemstate.txt` | Replays command text from shared temp state. | Indirectly, from prior generated or restored state. | Replace command replay with structured state representation. |
| 5 | `var/www/WANem/command.inc.php` | `exec($tc_CMD ... POST-derived parameters ...)` | Web UI mutates qdisc/filter/class state with root privileges. | Yes, validated form fields and selected interfaces. | Threat-model validation and isolate execution helper. |
| 6 | `root/disc_new_port_int/disc.awk`, `disco.awk` | `system("iptables ...")`, `system("conntrack ...")` from rule fields | AWK constructs root firewall commands from disconnect rule file. | Yes, via `input.dsc` generated from UI or restored state. | Define strict `input.dsc` schema and parser tests before running. |
| 7 | `etc/sudoers` | `%www-data ALL=NOPASSWD: ... /bin/echo, /bin/mv, /bin/grep, /root/...` | Broad passwordless root surface for web server user. | Yes, through PHP command paths. | Narrow sudoers only after command inventory is converted to explicit helper API. |
| 8 | `root/wanem.sh` | Interactive `iptables`, `brctl`, `ifconfig`, `route` operations | Console input can mutate NAT, bridge, and interface state. | Yes, interactive shell input. | Treat console as root-only appliance interface; add lab checklist. |
| 9 | `root/wanem_init.sh`, `root/reset_wanem.sh` | Service and network reset commands | Can restart services, reset network, start desktop/terminal daemons. | Mostly runtime state, not web input. | Run only in disposable VM until service ownership is decided. |
| 10 | `root/wanalyzer/tcs_wanem.awk`, `tcs_wanem.sh` | AWK runs script that applies `sudo /sbin/tc ... eth0` | Applies traffic control to hard-coded `eth0` from report data. | Indirectly, from WANalyzer report derived from target input. | Remove hard-coded interface assumption before using WANalyzer on modern hosts. |

## Static Analysis Notes

- This inventory documents risk only; it does not assert exploitability.
- Many inputs pass through validation functions, but validation was not proven complete here.
- Several read-oriented status commands still concatenate interface names into shell commands.
- The safest next step is to design a minimal, isolated runtime lab before executing any of these paths.
