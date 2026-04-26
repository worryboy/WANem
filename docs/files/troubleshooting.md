# Troubleshooting

## Scope

This guide combines historically documented WANem issues with checks that can be verified from the current repository tree. Historical advice is retained only where it still maps cleanly to the current repository or is clearly marked as legacy.

## GUI Does Not Load Or Loads Very Slowly

Symptoms:

- `/WANem` does not open
- the UI becomes very slow after applying settings
- browser access worked before settings were applied and then degraded

Possible causes:

- Extremely aggressive latency or loss values
- Rules applied without narrowing filters, so the browser-to-WANem management path is affected
- Apache or supporting services not running

Checks:

- From the WANem console, run `wanemreset` as documented in `root/wanem.sh`.
- Confirm Apache assumptions in `root/wanem_init.sh` and `root/reset_wanem.sh`.
- Review `var/www/WANem/help.htm`, which warns that extreme settings can destabilize results.
- Confirm that your management browser host is not inside the same packet filter scope you are emulating.

Verified current-tree references:

- `root/wanem.sh`
- `root/wanem_reset.sh`
- `root/reset_wanem.sh`
- `var/www/WANem/help.htm`

## Ping Delay Is About Double The Configured One-Way Delay

Symptoms:

- A configured delay of `100 ms` results in roughly `200 ms` ping time

Explanation:

- Historical WANem troubleshooting documentation explains this as expected behavior because ICMP echo request and reply both traverse the emulated path.

Status:

- Historical guidance
- Still conceptually consistent with current WAN emulation behavior

## Ping Delay Is Only About Half Of What Was Expected

Symptoms:

- You expected roughly `200 ms` RTT from a `100 ms` one-way delay, but you observe about `100 ms`

Possible causes:

- Asymmetric routing
- Only one traffic direction is passing through WANem

Checks:

- Run `traceroute` or `tracert` from both endpoints and verify both directions traverse the WANem host.
- Confirm route placement and NAT behavior if you are using separate subnets.
- Review the console NAT commands in `root/wanem.sh`.

Status:

- Historical upstream troubleshooting note
- Consistent with how the current tree is structured

## No Network Interfaces Detected

Symptoms:

- Startup reports missing interfaces

Possible causes:

- No NICs visible to the runtime environment
- Missing drivers in the legacy appliance-style environment
- VM configured without a network adapter

Checks:

- Review `root/wanem_init.sh`, which explicitly checks for non-loopback interfaces and prints guidance about drivers and virtual machine NIC configuration.
- Confirm your runtime environment presents the expected interfaces to Linux.

Status:

- Verified in current tree

## Remote Session Drops During Reconfiguration

Symptoms:

- SSH or remote console access disconnects while changing WANem IP settings

Possible causes:

- Interface address changes on the active management path

Checks:

- Expect a disconnect when changing the currently used IP address.
- Reconnect using the updated address after configuration is saved.

Status:

- Historical upstream note
- Consistent with the current reset/status model

## Remote Terminal Limitations

Symptoms:

- Terminal editing behaves strangely
- graphical start commands are unreliable from the browser terminal

Checks:

- Review `var/www/WANem/remoteTerminal.php`.
- Treat the remote terminal as an administration aid, not a full interactive desktop session.

Verified notes from current tree:

- `vi` is not fully supported
- `startx` is not recommended from the remote terminal

## Status And Connectivity Checks

Useful current-tree checks:

- `status` in `root/wanem.sh` shows interface and routing information
- `status` also prompts for an IP reachability test
- `var/www/WANem/status.php` inspects active `tc` state and disconnect settings

## Legacy Service Assumptions To Review

These may affect troubleshooting on modern systems:

- `/etc/init.d/apache2`
- `/etc/init.d/network-manager`
- `/etc/init.d/ajaxterm`
- `/sbin/ifconfig`
- `/sbin/brctl`
- `/usr/sbin/conntrack`

If those paths or services differ in a modern environment, troubleshooting steps may need adaptation even if the WANem logic is unchanged.
