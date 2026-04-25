# WANem Beta 3.0.3 Baseline

This repository contains a local source baseline for WANem, the Wide Area Network Emulator. The tree appears to be based on the WANem Beta 3.0.3 material referenced by the existing mirror README and is being prepared as a documentation and attribution baseline before any functional changes are made.

## Background

WANem is an open source WAN emulation environment originally published through the WANem SourceForge project and distributed under the GNU General Public License version 2.

- Original project name: WANem / Wide Area Network Emulator
- Original project URL: http://wanem.sourceforge.net/
- GitHub reference repository: https://github.com/worryboy/WANem
- Upstream baseline referenced in this repository: WANem Beta 3.0.3

The original mirror README text that was present at the repository root has been preserved at [docs/upstream/README.github-mirror-2016.md](docs/upstream/README.github-mirror-2016.md).

## Repository Purpose

This repository is being used as a continuation and modernization baseline for WANem Beta 3.0.3. The current phase is limited to repository analysis, documentation cleanup, license clarity, attribution preservation, and version baseline recording.

Functional modernization, dependency updates, and behavioral changes are intentionally deferred to later commits.

## Baseline Version

The strongest version marker in this tree is the original top-level README note:

- `Update 2016.08.23 : WANem Beta 3.0.3. >> These files are corrected for the depedency on a debian 8 environment based on Beta 3.0.2.`

Other in-tree materials still reference older labels such as `WANem v3.0`, so the Beta 3.0.3 baseline should be treated as the imported repository baseline, with some historical UI text and documentation still reflecting earlier version wording.

## Repository Status

- Status: imported baseline / cleanup phase
- Functional state: not reviewed for modernization in this commit
- Documentation certainty: factual where supported by files in this tree; uncertain points are marked "to be verified"

## Structure Overview

Based on the current repository contents:

- `var/www/WANem/`: PHP web UI, help, about pages, images, and WANem application assets
- `var/www/html/`: web root landing page content
- `root/`: shell scripts, reset scripts, WAN analyzer scripts, and interface/disconnection helpers
- `etc/apache2/`: Apache site configuration
- `etc/php5/`: PHP CLI and Apache module configuration
- `docs/upstream/`: preserved historical documentation copied from the pre-cleanup repository root

## License

This repository is documented as GPLv2-licensed based on the existing WANem documentation and copyright notices in the source tree.

- Full license text: see [LICENSE](LICENSE)
- Copyright and attribution details: see [ATTRIBUTION.md](ATTRIBUTION.md)
- Inherited notices are not perfectly uniform: some files say `GNU GPL v2` or `version 2`, while some inherited notices from reused code say `either version 2` or `any later version`

## Attribution

Original WANem authorship and attribution notices are retained in the source tree, including:

- `var/www/WANem/CopyrightInformation.txt`
- `var/www/WANem/About.txt`
- header comments in multiple PHP and shell files

This repository does not claim ownership of the original WANem code. New repository-maintainer documentation added here is limited to baseline curation and traceability.

## License Compliance Notes

- The GPLv2 license text is included in this repository.
- Original copyright and attribution notices found in the source tree are retained.
- If modified versions are distributed to third parties, corresponding source code should also be made available under GPLv2.

## Notes To Be Verified

- Whether every file in this tree came directly from a single upstream WANem Beta 3.0.3 release package, or from a later community-maintained mirror import
- Whether the embedded `WANem v3.0` UI labels were intentionally left unchanged in the Beta 3.0.3 materials
