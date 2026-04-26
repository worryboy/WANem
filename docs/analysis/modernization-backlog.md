# Modernization Backlog

## Scope

This backlog is for planning only. It does not authorize functional modernization in the baseline documentation phase.

Related audits:

- [Provenance audit](provenance-audit.md)
- [Runtime assumptions audit](runtime-assumptions-audit.md)
- [Compatibility notes](../files/compatibility-notes.md)
- [Legacy dependencies](../files/legacy-dependencies.md)

## P0: Must Understand Before Running

| Title | Current behavior | Risk | Suggested next action | Type |
|---|---|---|---|---|
| Privileged web execution model | PHP builds shell commands that run through `sudo`; `etc/sudoers` grants `www-data` access to networking and helper commands | Command injection, privilege escalation, and unintended host networking changes | Threat-model command construction and run only in an isolated lab until reviewed | Decision |
| Host networking mutation | `tc`, `iptables`, `brctl`, `ifconfig`, and `conntrack` are invoked against real interfaces | Can disrupt local or production network paths | Define a disposable VM/container lab topology before runtime testing | Decision |
| Provenance uncertainty | Upstream/source baseline appears to be Beta 3.0.2; local 3.0.3 metadata is not verified as official upstream | Incorrect release claims or wrong comparison target | Obtain original upstream artifacts and checksums before stronger provenance claims | Documentation |
| Hard-coded privileged paths | Scripts and PHP assume `/root`, `/var/www`, `/etc/apache2`, `/etc/php5`, and `/tmp` | Running outside the appliance layout may fail or modify unexpected locations | Inventory path usage and define expected runtime root filesystem | Documentation |
| Interface selection safety | Legacy `eth0` assumptions coexist with broad interface discovery | Wrong interface could be shaped, bridged, or NATed | Add a manual verification checklist before applying rules | Test |

## P1: Must Address Before Containerization

| Title | Current behavior | Risk | Suggested next action | Type |
|---|---|---|---|---|
| Linux capability model | Network operations expect root-level host privileges | Ordinary containers cannot safely run the full feature set | Decide VM-first, privileged container, or split controller/worker architecture | Decision |
| Service startup model | Scripts call `/etc/init.d/apache2`, `/etc/init.d/network-manager`, and `/etc/init.d/ajaxterm` | Init scripts are awkward or absent in minimal containers | Replace with explicit process supervision plan for the target runtime | Code |
| Apache/PHP coupling | Apache config and PHP 5 layout are committed as filesystem configuration | Modern images may use different PHP SAPI and config paths | Build a compatibility matrix before changing PHP or Apache | Test |
| Network namespace assumptions | WANem expects visible host NICs and routes | Container network namespace may hide or virtualize needed interfaces | Prototype in a disposable namespace and document required capabilities | Test |
| Persistent helper locations | PHP and sudoers reference `/root/disc_new_port_int` and `/root/wanalyzer` | Container paths and ownership may diverge | Decide whether to preserve paths or introduce configuration indirection | Code |
| Auxiliary services | Webmin, netdata, ajaxterm, and desktop startup are referenced | Optional services can expand attack surface and packaging scope | Decide supported vs historical status for each auxiliary service | Decision |

## P2: Modernization Candidate

| Title | Current behavior | Risk | Suggested next action | Type |
|---|---|---|---|---|
| Replace `ifconfig` usage | Interface scripts parse legacy `ifconfig` output | Breakage on modern distributions without net-tools | Map each call to `ip addr`, `ip link`, or structured discovery | Code |
| Review iptables vs nftables | NAT and disconnect tooling uses iptables syntax | Modern systems may default to nftables compatibility layers | Test on target distributions and document supported backend | Test |
| Bridge command modernization | Bridge management uses `brctl` | `bridge-utils` is legacy on modern Linux | Test equivalent `ip link` bridge operations after behavior is characterized | Code |
| PHP runtime compatibility | PHP files come from a PHP 5 era codebase | Newer PHP may surface syntax warnings or fatal behavior changes | Run static checks and runtime smoke tests under chosen PHP versions | Test |
| Configuration centralization | Command paths live in `config.inc.php`, scripts, and sudoers | Drift between config, docs, and runtime image | Create a single documented runtime configuration inventory | Documentation |
| WANalyzer validation | WANalyzer scripts exist and call tc on `eth0` | Tool may not work or may affect wrong interface | Add isolated runtime tests for WANalyzer before declaring support | Test |

## P3: Historical Or Optional

| Title | Current behavior | Risk | Suggested next action | Type |
|---|---|---|---|---|
| Knoppix appliance documentation | Historical docs describe bootable Knoppix workflows | Could be mistaken for current setup guidance | Keep as provenance and compatibility context only | Documentation |
| Internet Explorer references | Historical docs mention period-specific browser assumptions | Could confuse current users | Keep only in compatibility notes unless runtime evidence requires more | Documentation |
| Webmin proxy | Apache proxies `/webmin/` to local port 10000 | May not be part of core WANem | Mark optional until runtime artifact confirms intent | Decision |
| Netdata proxy | Apache proxies `/netdata/` to local port 19999 | May not be part of core WANem | Mark optional until runtime artifact confirms intent | Decision |
| Desktop session startup | `startx` is exposed through console flow and discouraged in remote terminal | Desktop may be irrelevant for headless maintenance | Decide whether to document as historical or preserve for appliance mode | Decision |

## Backlog Notes

- P0/P1 items should be resolved before any real network execution outside a disposable lab.
- P2 items are good modernization candidates only after current behavior is characterized.
- P3 items should remain clearly separated from maintained runtime requirements unless evidence shows they are required.
