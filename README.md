# WANem Local 3.0.3 Maintenance Baseline

This repository contains a local source baseline for WANem, the Wide Area Network Emulator. The tree appears to be derived from an upstream WANem Beta 3.0.2 baseline, with local repository metadata identifying the maintained tree as WANem 3.0.3. The local 3.0.3 marker should not be treated as a verified official upstream WANem release unless later upstream evidence proves otherwise.

## Background

WANem is an open source WAN emulation environment originally published through the WANem SourceForge project and distributed under the GNU General Public License version 2.

- Original project name: WANem / Wide Area Network Emulator
- Original project URL: http://wanem.sourceforge.net/
- GitHub reference repository: https://github.com/worryboy/WANem
- Upstream/source baseline: WANem Beta 3.0.2
- Local repository version: WANem 3.0.3
- Local version provenance: maintainer metadata derived from the 3.0.2 baseline; not verified as an official upstream release

The original mirror README text that was present at the repository root has been preserved at [docs/upstream/README.github-mirror-2016.md](docs/upstream/README.github-mirror-2016.md).

## Repository Purpose

This repository is being used as a continuation and modernization baseline for a local WANem 3.0.3 maintenance tree derived from the WANem Beta 3.0.2 source baseline. The current phase is limited to repository analysis, documentation cleanup, license clarity, attribution preservation, and version baseline recording.

Functional modernization, dependency updates, and behavioral changes are intentionally deferred to later commits.

## Version Provenance

The preserved mirror README note says:

- `Update 2016.08.23 : WANem Beta 3.0.3. >> These files are corrected for the depedency on a debian 8 environment based on Beta 3.0.2.`

That line is treated here as local repository or mirror metadata, not as proof of an official upstream WANem Beta 3.0.3 release. Because the same line explicitly says the files are based on Beta 3.0.2, the documented upstream/source baseline is WANem Beta 3.0.2, while WANem 3.0.3 is this repository's local maintenance version.

Other in-tree materials still reference older labels such as `WANem v3.0`, so version-specific references should be read as historical or local repository context unless supported by original upstream release artifacts.

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

## Documentation

Maintained repository documentation now lives under [docs/README.md](docs/README.md). The `docs/files/` directory contains version-neutral Markdown documentation for the current local baseline, while historical WANem PDFs are treated only as source material and provenance inputs.

## License Compliance Notes

- The GPLv2 license text is included in this repository.
- Original copyright and attribution notices found in the source tree are retained.
- If modified versions are distributed to third parties, corresponding source code should also be made available under GPLv2.

## Notes To Be Verified

- Whether every file in this tree came directly from a single upstream WANem Beta 3.0.2 release package, or from a later community-maintained mirror import
- Whether the embedded `WANem v3.0` UI labels were intentionally left unchanged in the upstream baseline or added to later local mirror metadata
