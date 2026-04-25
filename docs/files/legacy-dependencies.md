# Legacy Dependencies

This document records dependencies and environment assumptions that appear legacy or high-risk for future modernization.

| Dependency or assumption | Where referenced | Why it matters | Modernization risk | Verification status |
|---|---|---|---|---|
| Apache 2 with `/var/www` layout | `etc/apache2/sites-available/000-default.conf`, `root/wanem_init.sh` | Required for the current web UI path and service startup model | Medium | Confirmed in tree |
| PHP 5 era layout | `etc/php5/`, PHP files under `var/www/WANem/` | Indicates old runtime expectations and likely syntax/runtime compatibility concerns | High | Confirmed in tree |
| `sudo` from web UI | `var/www/WANem/config.inc.php`, `etc/sudoers`, `command.inc.php` | Core privileged command path for `tc`, disconnect helpers, and WANalyzer | High | Confirmed in tree |
| `tc` | `config.inc.php`, status and command files, WANalyzer scripts | Central network emulation primitive | High | Confirmed in tree |
| `ifconfig` | `root/wanem_init.sh`, `root/wanem.sh`, helper scripts | Used pervasively for interface detection and configuration | High | Confirmed in tree |
| `iptables` | `root/wanem.sh`, disconnect scripts | Required for NAT and disconnection handling | High | Confirmed in tree |
| `brctl` / bridge-utils | `config.inc.php`, `root/wanem.sh` | Required for bridge commands and experimental bridging support | Medium | Confirmed in tree |
| `conntrack` / `ip_conntrack` | `etc/sudoers`, `root/disc_new_port_int/*` | Used by disconnect emulation logic | High | Confirmed in tree |
| `/etc/init.d/network-manager` | `root/wanem_init.sh`, `root/newNetworkDevices.sh` | Startup depends on this service path | Medium | Confirmed in tree |
| `/etc/init.d/apache2` | `root/wanem_init.sh`, `root/reset_wanem.sh` | Service control path is hard-coded | Medium | Confirmed in tree |
| `/etc/init.d/ajaxterm` | `root/wanem_init.sh` | Remote terminal path depends on this daemon | Medium | Confirmed in tree |
| `ajaxterm` | `root/wanem_init.sh`, `remoteTerminal.php` | Web terminal integration depends on it | Medium | Partially confirmed |
| `startx` / desktop session | `root/wanem_init.sh`, `root/wanem.sh`, `remoteTerminal.php` | Indicates GUI-desktop expectations in the runtime image | Medium | Confirmed in tree |
| hard-coded `/root` scripts | many files under `root/`, PHP command construction | Assumes privileged local appliance layout | High | Confirmed in tree |
| interface naming like `eth0` | scripts and historical docs | Can break on modern predictable interface names | Medium | Confirmed in tree |
| `webmin` reverse proxy | Apache config | May be an environment addition rather than core WANem behavior | Medium | Not verified |
| `netdata` reverse proxy | Apache config | Same concern as `webmin` | Medium | Not verified |
| Knoppix appliance model | historical setup docs, `about.html` mentions Knoppix reuse | Shapes historical expectations but not proven as current source packaging | Medium | Historical only |

## Notes

- These entries are not a removal plan.
- They are an audit list for future compatibility and security review.
- Anything in this list that is both privileged and path-sensitive should be treated as a first-class migration risk.
