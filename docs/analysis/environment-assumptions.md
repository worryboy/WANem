# Environment Assumptions

## Purpose

This note records runtime assumptions that should be audited before any modernization work begins.

## Operating System And Layout

The repository assumes a Linux filesystem and an appliance-like runtime layout:

- `/root/` contains operational shell scripts
- `/var/www/WANem/` contains the PHP UI
- `/etc/apache2/` contains web server configuration
- `/etc/php5/` contains PHP configuration

This is closer to a prebuilt system image layout than a modern application package.

## Web Stack

Observed assumptions:

- Apache is the web server.
- The document root is `/var/www`.
- PHP is organized under `php5`, which strongly suggests an older Debian-family packaging layout.
- The application expects direct filesystem access to privileged scripts and temporary files.

Audit questions:

- Is the intended modernization target still Apache plus mod_php?
- Should PHP be updated in place or isolated first?
- Are the SSL, proxy, and auxiliary service settings in `000-default.conf` part of WANem itself or environment-specific additions?

## Shell And Service Model

Observed assumptions:

- `/bin/bash`
- `/etc/init.d/*` service control
- interactive console administration through `root/wanem.sh`
- desktop startup through `startx`
- remote terminal support through `ajaxterm`

These assumptions indicate a legacy system image workflow rather than a service-only deployment model.

## Networking Toolchain

The current tree directly references:

- `tc`
- `ifconfig`
- `iptables`
- `brctl`
- `conntrack`

These tools are central to WANem behavior and should be treated as core compatibility points. Several paths are hard-coded.

## PHP And Privilege Model

The PHP UI relies on:

- `sudo`
- a permissive `sudoers` rule for the web server user
- write access to `/tmp`
- root-owned scripts under `/root/`

Before modernization, this trust model needs careful review because it affects both security and portability.

## Networking Behavior Assumptions

The current tree assumes:

- one or more non-loopback NICs are present
- interface names are simple legacy names such as `eth0`
- traffic will be routed through the WANem host
- MTU may be forced to `1500`
- IP forwarding is enabled
- NAT and bridge operations may be required depending on topology

## What Needs Audit Before Modernization

- hard-coded filesystem paths
- service startup assumptions
- interface naming assumptions
- PHP 5 era compatibility
- whether `ajaxterm`, `webmin`, and `netdata` are still intended features
- whether the current Apache config reflects upstream WANem or a later local environment adaptation
- whether the privileged execution model can be narrowed safely without breaking WANem behavior
