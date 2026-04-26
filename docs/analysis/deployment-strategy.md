# Deployment Strategy

## Purpose

WANem should keep one source tree while clearly separating deployment-specific packaging and documentation. The current repository still reflects an appliance-style layout, but future work should distinguish appliance, native Linux, and Docker lab tracks.

## Track A: Appliance / Bootable Image

Use cases:

- physical hosts
- VM images
- USB or bootable appliance workflows
- realistic NIC behavior and appliance-like operation

Why this should be first-class:

- WANem mutates live networking state with `tc`, `iptables`, `brctl`, `conntrack`, and interface configuration commands.
- Appliance or VM deployment can provide predictable control over NICs, routing, kernel modules, and privileged services.
- This mode is likely the most faithful successor to historical WANem usage.

Documentation needs:

- boot and initialization flow
- NIC selection and safety checklist
- dependency list
- privileged command model
- backup/restore expectations
- benchmark procedure for routing/emulation accuracy

## Track B: Native Linux Installation

Use cases:

- installing WANem onto an existing Linux host
- lab machines where the operator controls the host OS
- environments where appliance images are not desired

Benefits:

- can reuse host packages and service management
- may be easier to debug than a sealed appliance image
- can support future package-managed installation

Risks:

- WANem can disrupt host networking.
- The current sudoers model is broad and should be narrowed before normal use.
- Existing scripts assume `/root`, `/var/www/WANem`, `/etc/apache2`, `/etc/php5`, `/tmp`, and legacy networking tools.

Future direction:

- introduce a privileged command wrapper or helper API
- document exact sudoers requirements
- make paths configurable
- provide dependency checks before applying network changes

## Track C: Docker / Container Lab

Use cases:

- fast experimentation
- development and smoke tests
- isolated topologies using network namespaces, veth pairs, or lab-only privileged containers

Limits:

- Docker should not automatically be recommended for high-performance or production-like routing.
- Performance and emulation accuracy require benchmark validation.
- Container behavior depends heavily on capabilities, namespace setup, host kernel, and interface attachment.

Likely requirements:

- `CAP_NET_ADMIN` at minimum for `tc` and network mutation
- possibly privileged mode for full bridge, iptables, or conntrack behavior
- explicit network namespace design
- clear distinction between container interfaces and host interfaces
- package installation for Apache/PHP, `tc`, `iptables`, `brctl`, `conntrack`, `ifconfig`, and shell tooling

The SourceForge 2022 `wanemDockerFiles` artifacts do not include a Dockerfile or compose file. They are useful as historical payload/provenance material, but not as a complete supported container strategy.

## Repository Organization Proposal

Keep one source tree. Add deployment-specific files later under dedicated packaging or deploy paths.

Suggested future structure:

```text
packaging/
  appliance/
  native-linux/
  docker/

docs/
  deployment/
    appliance.md
    native-linux.md
    docker-lab.md
```

Guidelines:

- Core source should not fork per deployment target.
- Deployment-specific files should be additive and reviewed separately.
- Documentation should clearly identify which runtime mode a procedure belongs to.
- Historical WANem 1.1/2.0 notes should remain provenance unless validated against the current repository tree.
- Local WANem 3.0.3 remains repository metadata derived from the 3.0.2 baseline unless upstream evidence proves otherwise.
