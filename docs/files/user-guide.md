# User Guide

## What WANem Does

WANem is a web-driven and shell-assisted WAN emulation environment. In the current repository baseline, the web UI under `var/www/WANem/` configures Linux traffic control and related helper scripts to emulate network characteristics such as delay, loss, bandwidth limits, jitter, duplication, corruption, reordering, and disconnection behavior.

Verified supporting files include:

- `var/www/WANem/help.htm`
- `var/www/WANem/index-basic.php`
- `var/www/WANem/index-advanced.php`
- `var/www/WANem/status.php`
- `var/www/WANem/save-restore.php`
- `root/wanem.sh`

## Basic Usage Model

The current source tree presents the following top-level UI sections in `var/www/WANem/title.html`:

- About
- WANalyzer
- Basic Mode
- Advanced Mode
- Save/Restore
- Remote Terminal
- Help

The browser entry point is `var/www/WANem/index.html`, which frames `title.html` and the main content pane.

## Common Workflow

Based on the current tree and historically consistent upstream material:

1. Configure the WANem host with reachable network settings.
2. Ensure traffic between the communicating endpoints is routed through the WANem host.
3. Open the WANem web interface.
4. Choose basic mode for simpler interface-wide settings or advanced mode for filtered rule sets.
5. Apply settings.
6. Verify the resulting behavior with status views and endpoint-side network tests.

The routing-through-WANem requirement is historical documentation, but it is also consistent with the current code structure and use of Linux traffic control.

## Basic Mode

Verified from `help.htm` and `index-basic.php`:

- Basic mode supports one rule set per displayed interface.
- The interface presents buttons for:
  - `Apply settings`
  - `Reset settings`
  - `Refresh settings`
  - `Check current status`
- `help.htm` describes basic mode as focused on bandwidth and latency.

Because the current source tree still contains the full advanced-mode machinery for additional impairments, basic mode should be understood as the simpler UI path rather than the full feature set of the system.

## Advanced Mode

Verified from `help.htm`, `start_advance.php`, and `index-advanced.php`:

- Advanced mode supports multiple rule sets per interface.
- Rule sets can be differentiated by source address, destination address, subnet, and application port filters.
- The interface presents buttons for:
  - `Add a rule set`
  - `Delete last rule set`
  - `Apply settings`
  - `Reset settings`
  - `Refresh settings`
  - `Check current status`

Historical upstream material described this as the path for applying more than one IP-address-matched rule set to a single interface. That remains consistent with the current code.

## Network Emulation Concepts In The UI

Verified from `help.htm`, the PHP code, and the included historical conceptual document:

- Bandwidth limiting
- Delay / latency
- Jitter
- Packet loss
- Packet duplication
- Packet corruption
- Packet reordering
- Disconnection behavior

For detailed conceptual definitions, see [network-emulation-concepts.md](network-emulation-concepts.md).

## WANalyzer

Verified from `help.htm`, `wanc.html`, `result.php`, and `root/wanalyzer/*`:

- WANalyzer is presented as a WAN characterization tool.
- Historical and in-tree help describe it as measuring available bandwidth, latency, loss, and jitter for a remote host.
- The results path can feed measured characteristics back into emulation.

The UI wording is verified, but the exact runtime behavior of WANalyzer has not yet been exercised in this repository.

## Save And Restore

Verified from `save-restore.php` and `download.php`:

- WANem can save current state to a client as `netemstate.txt`.
- WANem can upload a saved state back through `upload.php`.

This is verified as a feature in the current tree, though no runtime validation has been performed yet.

## Remote Terminal

Verified from `remoteTerminal.php`:

- A remote terminal entry exists.
- The page expects a WANem machine IP and opens an HTTPS target.
- The page warns that it is intended for commands and basic administration only.
- It explicitly warns that `vi` is not fully supported and `startx` is not recommended from the remote terminal.

## Console Usage

Verified from `root/wanem.sh`:

- `help`
- `about`
- `assign`
- `bridge`
- `nat`
- `reset`
- `status`
- `restart`
- `shutdown`
- `startx`
- `wanemreset`

These commands support the UI by handling network setup, NAT, bridge operations, service visibility, and reset behavior.

## Legacy UI Notes

Historical PDFs refer to WANem 1.1 or 2.0 and include screenshots that are not carried forward here. The current tree still exposes older labels such as `WANem 3.0` in some UI files, so the maintained docs intentionally describe features neutrally and record version-specific wording separately in [compatibility-notes.md](compatibility-notes.md).
