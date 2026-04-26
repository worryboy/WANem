# Runtime Assumptions Audit

## Scope

This audit organizes runtime assumptions already visible in the current repository tree. It is a baseline review only; no runtime behavior was exercised here.

Related maintained notes:

- [Setup](../files/setup.md)
- [Troubleshooting](../files/troubleshooting.md)
- [Legacy dependencies](../files/legacy-dependencies.md)
- [Compatibility notes](../files/compatibility-notes.md)
- [Environment assumptions](environment-assumptions.md)

## Runtime Assumptions

| Assumption / dependency | Where referenced | Purpose | Privilege requirement | Modernization risk | Verification status | Suggested follow-up |
|---|---|---|---|---|---|---|
| Apache `/var/www` layout | `etc/apache2/sites-available/000-default.conf`, `root/wanem_init.sh`, `docs/files/setup.md` | Serves the WANem PHP UI at `/WANem` | Service control requires elevated privileges | Medium | Confirmed in tree | Decide whether to preserve Apache layout first or introduce an adapter for container paths. |
| PHP 5 era layout | `etc/php5/apache2/php.ini`, `etc/php5/cli/php.ini`, PHP files under `var/www/WANem/` | Runtime configuration for legacy PHP UI | Web server executes PHP | High | Confirmed in tree | Inventory PHP syntax/runtime compatibility before selecting a target PHP version. |
| `sudo` from web UI | `etc/sudoers`, `var/www/WANem/config.inc.php`, `var/www/WANem/command.inc.php` | Allows web UI to run privileged networking helpers | High, runs commands as root via `www-data` | High | Confirmed in tree | Threat-model command construction and sudoers scope before running outside a controlled lab. |
| `tc` | `var/www/WANem/config.inc.php`, `var/www/WANem/status.php`, `root/wanalyzer/tcs_wanem.sh` | Core traffic shaping and netem behavior | Root or network admin capability | High | Confirmed in tree | Verify required kernel modules and command syntax on target OS. |
| `ifconfig` | `root/wanem_init.sh`, `root/wanem.sh`, helper scripts | Interface discovery and address handling | Often elevated for configuration | High | Confirmed in tree | Map usage to `iproute2` equivalents before modernization. |
| `iptables` | `root/wanem.sh`, `root/disc_new_port_int/*.awk` | NAT and disconnect rule handling | Root | High | Confirmed in tree | Decide iptables legacy vs nftables compatibility strategy. |
| `brctl` / bridge-utils | `var/www/WANem/config.inc.php`, `root/wanem.sh` | Bridge creation, deletion, and display | Root | Medium | Confirmed in tree | Verify whether bridge mode is required and map to modern `ip link` bridge commands if needed. |
| `conntrack` / `ip_conntrack` | `etc/sudoers`, `root/disc_new_port_int/disc.awk`, `root/disc_new_port_int/disco.awk`, `root/disc_new_port_int/fwconmon.sh` | Tracks or clears TCP flows for disconnect emulation | Root | High | Confirmed in tree | Verify kernel module/package names and conntrack behavior on target kernel. |
| `/etc/init.d/network-manager` | `root/wanem_init.sh`, `root/newNetworkDevices.sh` | Restarts networking during initialization | Root | Medium | Confirmed in tree | Decide whether service management remains in scope for container or VM runtime. |
| `/etc/init.d/apache2` | `root/wanem_init.sh`, `root/reset_wanem.sh`, Debian default page under `var/www/html/` | Starts Apache in appliance-style workflow | Root | Medium | Confirmed in tree | Replace or wrap service startup for systemd/container environments. |
| `/etc/init.d/ajaxterm` | `root/wanem_init.sh` | Starts browser-accessible terminal daemon | Root | Medium | Confirmed in tree | Decide whether remote terminal remains a supported feature. |
| `ajaxterm` | `root/wanem_init.sh`, `var/www/WANem/remoteTerminal.php` | Browser terminal access | Daemon plus shell access | High | Partially confirmed; daemon package not present in repo | Treat as optional until runtime image requirements are verified. |
| `startx` / desktop session | `root/wanem_init.sh`, `root/wanem.sh`, `var/www/WANem/remoteTerminal.php` | Starts local graphical desktop workflow | User/session level, possibly root in appliance | Medium | Confirmed in tree | Decide whether desktop boot behavior is still part of the maintained target. |
| Hard-coded `/root` scripts | `root/*`, `var/www/WANem/config.inc.php`, `var/www/WANem/command.inc.php`, `etc/sudoers` | Operational script location for reset, disconnect, and WANalyzer | Root-owned execution path | High | Confirmed in tree | Inventory every hard-coded path before packaging or containerization. |
| `eth0`-style interface naming | `root/wanem_init.sh`, `root/wanem_Old.sh`, `root/wanalyzer/*`, `var/www/WANem/status.php`, disconnect help examples | Assumes legacy interface names for routing, tc, and examples | Root when configuring interfaces | High | Confirmed in tree | Add an interface discovery plan before running on predictable-interface-name systems. |
| Webmin reverse proxy | `etc/apache2/sites-available/000-default.conf`, `docs/files/setup.md` | Proxies `/webmin/` to `localhost:10000` | Web service/proxy | Medium | Config confirmed; intent not verified | Decide if Webmin is part of WANem or an environment addition. |
| Netdata reverse proxy | `etc/apache2/sites-available/000-default.conf`, `docs/files/setup.md` | Proxies `/netdata/` to `localhost:19999` | Web service/proxy | Medium | Config confirmed; intent not verified | Decide if Netdata is part of maintained runtime or local appliance decoration. |
| Knoppix appliance model | historical PDFs, `var/www/WANem/about.html`, `docs/files/setup.md` | Historical bootable appliance distribution model | Full system image | Medium | Historical only | Keep as provenance unless original image artifacts are recovered. |
| WANalyzer | `var/www/WANem/help.htm`, `root/wanalyzer/*`, `etc/sudoers`, `docs/files/compatibility-notes.md` | Wide area network characterization and tc helper scripts | Root via sudo for tc helpers | Medium | Files confirmed; runtime not exercised | Decide if WANalyzer is in first supported runtime target. |
| NAT commands | `root/wanem.sh`, `docs/files/compatibility-notes.md` | Allows WANem to work across subnets | Root via iptables | High | Confirmed in tree | Test NAT behavior in an isolated lab topology before modernization. |
| Bridge commands | `root/wanem.sh`, `var/www/WANem/find_3.0.inc.php`, `docs/files/compatibility-notes.md` | Supports bridge-based traffic path | Root | Medium | Partially confirmed | Test bridge creation/destruction and browser management path safety. |
| Disconnect tooling | `root/disc_new_port_int/*`, `var/www/WANem/command.inc.php`, `var/www/WANem/status.php` | Emulates idle/random disconnections | Root via iptables/conntrack | High | Confirmed in tree; runtime not exercised | Verify dependency versions and isolate command input handling. |

## Cross-Cutting Observations

- The runtime model is appliance-like, not application-package-like.
- Privileged command execution is core behavior, not an incidental helper.
- Several assumptions are path-sensitive and likely to break in containers unless explicitly modeled.
- The current repository tree is enough to identify risk areas, but not enough to certify runtime behavior.
