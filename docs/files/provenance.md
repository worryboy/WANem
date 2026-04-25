# Provenance

## Original Project

- Original project name: WANem / Wide Area Network Emulator
- Original SourceForge project URL: https://sourceforge.net/projects/wanem/
- Original project website URL: https://wanem.sourceforge.net/
- GitHub mirror reference already used in repository docs: https://github.com/worryboy/WANem
- Current local baseline: WANem Beta 3.0.3

## Historical PDF Sources Used As Input Material

The maintained Markdown documentation in this repository was written using the following historical WANem documents as source material:

1. `https://excellmedia.dl.sourceforge.net/project/wanem/Documents/WANemv11-Setup-Guide.pdf?viasf=1`
2. `https://master.dl.sourceforge.net/project/wanem/Documents/WANemv11-Troubleshooting-Guide.pdf?viasf=1`
3. `https://excellmedia.dl.sourceforge.net/project/wanem/Documents/wanemulator_all_about_v2.0.pdf?viasf=1`
4. `https://master.dl.sourceforge.net/project/wanem/Documents/WANemv11-User-Guide.pdf?viasf=1`

The SourceForge Documents listing also shows these files with historical timestamps:

- `WANemv11-Setup-Guide.pdf` dated `2007-09-05`
- `WANemv11-Troubleshooting-Guide.pdf` dated `2007-09-05`
- `WANemv11-User-Guide.pdf` dated `2007-09-05`
- `wanemulator_all_about_v2.0.pdf` dated `2008-12-01`

## Date Accessed

- Historical sources accessed or attempted: `2026-04-25`

## Temporary PDF Handling

Temporary PDF downloads were written to `/tmp` for extraction only and removed after extraction. They are not maintained as repository documentation and are not committed to this repository.

The repository already contained one historical PDF at:

- `var/www/WANem/wanemulator_all_about.pdf`

That in-tree PDF remains part of the imported source baseline, but it is not treated as maintained project documentation. The maintained documentation set for this repository is Markdown only.

## Checksums

Successfully extracted temporary or in-tree source files:

- `WANemv11-Setup-Guide.pdf`
  - SHA256: `05a57993b038f0e2a02a2780c3dad0d778cc3a59afa963aa0150971d436d5497`
- `WANemv11-Troubleshooting-Guide.pdf`
  - SHA256: `a79e325bc75e1d1aade9d42fecedc35b3dd6f9157081e85e0d26b852af9da381`
- `wanemulator_all_about.pdf` from the local repository tree
  - SHA256: `809c871f3dfb1de70c90e218e4daf04068cd447455fef7b3f71c028391e648b2`

`WANemv11-User-Guide.pdf` was identified and temporarily downloaded, but the retrieved file was truncated during this pass and could not be cleanly extracted. It was therefore not used as a reliable content source for the maintained Markdown files.

## How The Markdown Docs Were Derived

The maintained Markdown files were produced by:

- extracting text from the historical PDFs where extraction succeeded
- comparing historical instructions to the current local WANem Beta 3.0.3 tree
- keeping only source-tree-confirmed instructions as current guidance
- moving uncertain, legacy, or conflicting material into compatibility and provenance notes

## Observed Differences Between Historical Material And The Local Tree

- The local tree identifies itself most strongly as `WANem Beta 3.0.3` in the preserved mirror README, while many UI files still say `WANem v3.0`.
- The historical 1.1 setup guide describes a Knoppix bootable CD distribution, while this repository is a source tree with Apache, PHP, and shell assets.
- The historical setup guide says save/restore is planned for a later release; the current tree already contains `save-restore.php`, `download.php`, and `upload.php`.
- NAT support described in the historical 2.0 conceptual document is reflected in the current console shell via `nat add`, `nat del`, and `nat show`.
- The current Apache config includes reverse proxies for `webmin` and `netdata`, which were not part of the historical documents reviewed here.

## Limitations

- A full byte-for-byte provenance comparison against an original WANem Beta 3.0.3 ISO or release tarball is still pending.
- The GitHub mirror was referenced as a known related repository, but no line-by-line divergence study was completed in this documentation pass.
- The historical user guide PDF could not be fully extracted during this run, so any information unique to that document still needs manual follow-up.

## Documentation Policy For This Repository

- Final maintained documentation is Markdown only.
- Historical PDFs are source material, not maintained documentation.
- Old version names are preserved in provenance and compatibility notes where relevant, but not used as the maintained documentation filenames for this repository.
