# Changelog

This changelog distinguishes between historical upstream notes found in the imported tree and changes made in this repository.

## Historical Upstream Notes Found In Tree

- `docs/upstream/README.github-mirror-2016.md` states: `Update 2016.08.23 : WANem Beta 3.0.3. >> These files are corrected for the depedency on a debian 8 environment based on Beta 3.0.2.`
- `var/www/WANem/About.txt` and `var/www/WANem/about.html` still identify the UI as `WANem v3.0`.
- `var/www/WANem/CopyrightInformation.txt` documents WANem as a TCS modification of PHPnetemGUI and references GPL distribution terms.

These upstream notes are preserved as evidence and are not rewritten here as a verified, complete upstream release history.

## Repository Changes

### 2026-04-26

- Corrected version provenance wording across maintained documentation.
- Clarified that the upstream/source baseline appears to be WANem Beta 3.0.2.
- Clarified that WANem 3.0.3 is this repository's local maintenance version derived from the 3.0.2 baseline.
- Removed maintained-documentation wording that implied an official upstream WANem 3.0.3 release.

### 2026-04-25

- Documented the repository baseline prior to functional changes.
- Added a top-level `LICENSE` file with the GNU GPL v2 text.
- Added `ATTRIBUTION.md` to record project provenance, attribution, and repository-maintainer boundaries.
- Added `VERSION` to record baseline version provenance and source references.
- Rewrote the top-level `README.md` to describe the repository factually as an imported baseline.
- Preserved the original top-level mirror README text at `docs/upstream/README.github-mirror-2016.md`.
- Added version-neutral Markdown documentation under `docs/` based on historical WANem upstream documents and verified source-tree behavior.
- Added provenance notes, compatibility notes, environment assumptions, and legacy dependency notes.
- Kept maintained documentation in Markdown form rather than retaining temporary PDF downloads as project documentation.
