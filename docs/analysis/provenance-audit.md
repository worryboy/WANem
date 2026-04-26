# Provenance Audit

## Scope

This audit records version and provenance evidence visible in the current repository tree. It does not prove upstream release history.

Related maintained notes:

- [Provenance](../files/provenance.md)
- [Compatibility notes](../files/compatibility-notes.md)
- [Version metadata](../../VERSION)
- [Preserved mirror README](../upstream/README.github-mirror-2016.md)

## Current Version Interpretation

| Claim | Status | Evidence | Notes |
|---|---|---|---|
| Upstream/source baseline is WANem Beta 3.0.2 | Plausible, not byte-for-byte proven | `docs/upstream/README.github-mirror-2016.md` says the files were corrected for Debian 8 based on Beta 3.0.2 | Requires original upstream artifacts for confirmation. |
| Local repository version is WANem 3.0.3 | Confirmed as local repository metadata | `VERSION`, `README.md`, `ATTRIBUTION.md`, `docs/files/provenance.md` | This is maintained repository metadata, not proof of an official upstream release. |
| 3.0.3 official upstream release status | Not verified | No original upstream ISO, tarball, release announcement, or checksum is present | Treat 3.0.3 as local repository metadata unless later evidence proves otherwise. |
| Current tree came from one exact official upstream release artifact | Not proven | Repository contains imported source plus later documentation cleanup | Needs upstream release artifact comparison. |

## Version References Found

| Version label | Files found | Interpretation |
|---|---|---|
| `WANem v3.0` | `var/www/WANem/About.txt`, `var/www/WANem/about.html`, plus maintained references in `VERSION`, `README.md`, `CHANGELOG.md`, and `docs/files/provenance.md` | Historical UI/about label in the current repository tree. |
| `WANem Beta 3.0.2` / `Beta 3.0.2` | `docs/upstream/README.github-mirror-2016.md`, `README.md`, `VERSION`, `ATTRIBUTION.md`, `CHANGELOG.md`, `docs/README.md`, `docs/files/provenance.md`, `docs/files/compatibility-notes.md` | Treated as the upstream/source baseline where the wording is maintained documentation. |
| `WANem Beta 3.0.3` | `docs/upstream/README.github-mirror-2016.md`, quoted or explained in `README.md`, `VERSION`, `CHANGELOG.md`, and `docs/files/provenance.md` | Preserved mirror/local marker only; not verified as an official upstream release. |
| `WANem 3.0.3` / local 3.0.3 | `README.md`, `VERSION`, `ATTRIBUTION.md`, `CHANGELOG.md`, `docs/README.md`, `docs/files/provenance.md`, `docs/files/compatibility-notes.md` | Local repository version metadata derived from the 3.0.2 baseline. |
| `3.0.3-local` | No direct file reference found in the current scan | Acceptable shorthand for future docs if defined as local repository metadata. |

## Files Containing Historical Version Labels

| File | Version text | Notes |
|---|---|---|
| `var/www/WANem/About.txt` | `WANem v3.0` | In-tree UI/about content; not changed by this audit. |
| `var/www/WANem/about.html` | `WANem v3.0` | In-tree UI/about content; not changed by this audit. |
| `docs/upstream/README.github-mirror-2016.md` | `WANem Beta 3.0.3` and `Beta 3.0.2` | Preserved historical mirror text. It should remain quoted as evidence, not rewritten as verified release history. |

## Local 3.0.3 Metadata References

The following maintained files intentionally describe 3.0.3 as local repository metadata:

- `VERSION`
- `README.md`
- `ATTRIBUTION.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/files/provenance.md`
- `docs/files/compatibility-notes.md`

These references should continue to avoid wording that presents 3.0.3 as an official upstream release unless original upstream evidence is later added.

## Single-Release Provenance Status

The current tree cannot yet be proven to come from a single official upstream release. The repository includes:

- historical WANem source files
- the preserved mirror README line that references both `WANem Beta 3.0.3` and `Beta 3.0.2`
- UI/about labels that still say `WANem v3.0`
- repository-maintained Markdown documentation and metadata added later

The safest current wording is:

> Upstream baseline: WANem Beta 3.0.2; local repository version: WANem 3.0.3.

## Evidence Still Needed

To make stronger provenance claims, collect and compare:

- original WANem Beta 3.0.2 ISO, tarball, or source package
- any original upstream 3.0.3 artifact, if one exists
- checksums from SourceForge, project pages, release notes, or signed manifests
- release announcement text or changelog from the original upstream project
- a file-by-file comparison between this repository and verified upstream artifacts
- dates and hashes for any known GitHub mirror imports

Until that evidence exists, 3.0.3 references remain local repository version metadata.
