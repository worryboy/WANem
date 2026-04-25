# Setup

## Purpose

This document describes the setup assumptions that can be verified from the current WANem Beta 3.0.3 source tree, plus a small number of historical setup notes preserved as legacy references.

## Current Baseline Summary

The repository contains a PHP web UI under `var/www/WANem/`, shell-based administration scripts under `root/`, Apache configuration under `etc/apache2/`, and PHP configuration under `etc/php5/`.

Verified entry points in the current tree include:

- Web UI frame entry: `var/www/WANem/index.html`
- Main navigation: `var/www/WANem/title.html`
- Basic mode UI: `var/www/WANem/index-basic.php`
- Advanced mode launcher: `var/www/WANem/start_advance.php`
- Console shell: `root/wanem.sh`
- Initialization script: `root/wanem_init.sh`

## Runtime Assumptions

Verified from the current tree:

- Linux is assumed.
- The web application expects Apache with document root `/var/www`.
- The application path is `/WANem`.
- PHP is expected through the `php5` layout present in `etc/php5/`.
- Legacy networking tools are called directly from scripts:
  - `/sbin/tc`
  - `/sbin/ifconfig`
  - `/sbin/iptables`
  - `/sbin/brctl`
  - `/usr/sbin/conntrack`
- Root-owned shell scripts under `/root/` are part of normal operation.
- `sudo` is expected for the web server user to execute selected networking commands, as documented in `etc/sudoers`.
- The initialization path references `/etc/init.d/apache2`, `/etc/init.d/network-manager`, and `/etc/init.d/ajaxterm`.
- The console workflow expects a local X session and calls `startx`; `root/wanem_init.sh` also prints guidance about the WANem shell and browser access.

## Installation And Deployment Notes

Verified from the current tree:

- This repository is a source baseline, not a packaged installer.
- The current tree still resembles an appliance-style layout rather than a conventional application package.
- Apache is configured to serve `/var/www`, where `var/www/WANem/` contains the WANem UI.
- The repository includes Apache and PHP configuration fragments, but no modern install script or package manifest was found.

Historical source material, not verified as current Beta 3.0.3 packaging behavior:

- The WANem 1.1 setup guide described WANem as a bootable Knoppix CD image with no installation step beyond booting the media.
- That same guide described initial NIC setup through a console-driven reset flow and browser access at `http://<wanemip>/WANem`.

Those appliance-distribution details should be treated as legacy unless reconfirmed from a matching Beta 3.0.3 image.

## Network And Interface Assumptions

Verified from the current tree:

- WANem expects one or more non-loopback interfaces and inspects them with `ifconfig`.
- `root/wanem_init.sh` checks interface presence, optionally restarts NetworkManager, and forces MTU `1500`.
- `root/wanem.sh` and `root/wanem_reset.sh` assume interface-level `tc` and `iptables` manipulation.
- `root/wanem.sh` includes console commands for:
  - `reset`
  - `status`
  - `assign`
  - `nat add|del|show`
  - `bridge add|del|show`
  - `wanemreset`
- `var/www/WANem/help.htm` and the PHP UI confirm basic and advanced rule configuration, status inspection, and save/restore support.

Historical but still broadly plausible:

- Older upstream guides assume traffic must be routed through the WANem host for emulation to take effect.
- Historical route examples for Windows, Linux, AIX, and Solaris should be treated as reference material only; they are not validated by this repository.

## Service Assumptions

Verified from the current tree:

- Apache service is expected.
- `ajaxterm` is expected for the remote terminal path.
- The Apache config includes reverse proxies for:
  - `/netdata/` to `localhost:19999`
  - `/webmin/` to `localhost:10000`
- `remoteTerminal.php` expects HTTPS access to the WANem host IP.

Not verified as fully functional in this repository alone:

- Whether `ajaxterm`, `netdata`, and `webmin` were present in the original Beta 3.0.3 runtime image
- Whether the current Apache SSL proxy configuration was part of upstream WANem or a later environment adaptation

## Configuration Files And Scripts

Relevant verified files:

- `etc/apache2/sites-available/000-default.conf`
- `etc/php5/apache2/php.ini`
- `etc/php5/cli/php.ini`
- `etc/sudoers`
- `var/www/WANem/config.inc.php`
- `root/wanem_init.sh`
- `root/wanem.sh`
- `root/reset_wanem.sh`
- `root/wanem_reset.sh`
- `root/newNetworkDevices.sh`
- `root/newCheckIPAddress.sh`
- `root/disc_new_port_int/*`
- `root/wanalyzer/*`

## Known Legacy Assumptions

- Knoppix-based bootable CD distribution
- i386 hardware target
- Internet Explorer references in historical documentation
- SSH or PuTTY-centric remote administration guidance
- `ifconfig` and `/etc/init.d/*` service control rather than newer Linux equivalents
- hard-coded interface names such as `eth0`

These assumptions remain useful for historical context, but they should be audited before any modernization work.

## Verification Checklist

- Confirm Apache serves `/var/www/WANem/`.
- Confirm `config.inc.php` paths match the runtime system.
- Confirm `sudoers` permits the commands WANem invokes from PHP.
- Confirm `tc`, `ifconfig`, `iptables`, `brctl`, and `conntrack` exist at the referenced paths.
- Confirm `network-manager`, `apache2`, and `ajaxterm` service assumptions are still valid for the target environment.
- Confirm interface naming assumptions if running on a modern Linux host.
- Confirm whether remote terminal, `webmin`, and `netdata` are still intended parts of the deployment.
