# Network Emulation Concepts

## Scope

This document reshapes the historical WANem conceptual documentation into a version-neutral reference for the current repository baseline. It explains the main emulation concepts without presenting the old WANem 2.0 document as current release documentation.

## Why WAN Emulation Exists

WAN emulation introduces network characteristics that are common outside a local LAN but often absent during local development. The basic idea is to place a controlled intermediary in the traffic path so application behavior can be observed under more realistic conditions.

Typical effects being modeled include:

- limited bandwidth
- additional latency
- jitter
- packet loss
- duplication
- corruption
- reordering
- temporary disconnections

## Core Model

The historical WANem material and the current source tree both reflect the same core model:

- traffic must pass through the WANem host
- emulation is applied on egress interfaces
- rule sets may apply globally or be narrowed by packet filters

The current codebase implements this model primarily with Linux traffic control, packet filtering helpers, and supporting shell scripts.

## Packet Limit

Historical WANem material describes a packet limit that models a bounded forwarding queue. Conceptually, once the queue exceeds the limit, packets are dropped.

Status for the current tree:

- concept is documented historically
- not separately re-validated in runtime here

## Delay And Latency

Delay represents one-way travel time added to traffic. Historical WANem documentation also calls this latency and notes that ping measures round-trip time, not one-way delay.

Practical interpretation:

- configured one-way delay is often observed as roughly double in ping RTT when both directions are emulated

## Jitter

Jitter is variation around a base delay. Instead of every packet receiving exactly the same delay, packets can vary above or below the base value.

Why it matters:

- real networks rarely deliver packets with perfectly constant latency
- jitter is especially relevant for interactive and streaming traffic

## Correlation

Several impairments support a correlation setting. Conceptually, correlation makes successive packet behavior less independent, which can help model bursty or clustered conditions rather than perfectly random ones.

Examples:

- delay correlation makes the next packet more likely to resemble the previous packet's delay
- loss correlation can create burst-loss behavior

## Distribution

Historical WANem material describes support for multiple jitter distributions, including:

- normal
- pareto
- paretonormal

This is historical conceptual information. The current repository should be runtime-tested before treating every distribution option as fully confirmed in the local baseline.

## Packet Loss

Loss drops packets according to a configured rate. This models congestion, signal degradation, or other delivery failures.

Practical note from historical WANem documentation:

- high unfiltered loss can affect your ability to keep using the WANem management UI itself

## Duplication

Duplication causes a packet to appear more than once at the receiver. This wastes bandwidth and can expose protocol or application assumptions about uniqueness and ordering.

## Corruption

Corruption models transmission errors by introducing random bit-level damage into packets. Conceptually this is different from loss: the packet arrives, but its contents are altered.

## Reordering

Reordering changes packet arrival order. Historical WANem material describes more than one approach:

- fixed-gap reordering
- percentage-based reordering
- incidental reordering caused by delay and jitter variation

Important conceptual note:

- some form of delay is usually required for visible reordering to occur

## Bandwidth Limiting

Bandwidth limiting constrains throughput between two endpoints. The historical WANem material treats this as available bandwidth between network nodes rather than raw physical link speed.

The current tree contains bandwidth-related UI paths and `htb` usage in WANalyzer shell scripts, which is consistent with this concept.

## Disconnections

The repository includes a dedicated disconnect subsystem under `root/disc_new_port_int/`. Historical WANem material describes three broad disconnection patterns:

- idle-timer disconnection
- random network disconnection
- random connection disconnection

These concepts remain important because they go beyond simple delay and loss and attempt to model intermittent connectivity and reset behavior.

## Symmetric And Asymmetric Paths

Historical WANem material explains that WAN settings may be applied in both directions or only one direction, depending on rule construction and traffic routing.

Why this matters:

- symmetric behavior is often assumed by casual testing
- many real networks are asymmetric
- routing mistakes can make only one direction pass through WANem, producing misleading results

## Practical Warning

The historical documentation repeatedly reinforces one lesson that still applies to the current repository baseline:

- if traffic does not actually traverse WANem in the intended directions, the emulation results will be misleading

That is why route verification and management-path isolation matter as much as impairment settings themselves.
