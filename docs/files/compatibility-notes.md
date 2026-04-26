# Compatibility Notes

This table records how historical WANem documentation aligns with the current repository tree.

| Topic | Historical source | Historical version context | Verified in current repository tree? | Status | Notes |
|---|---|---:|---|---|---|
| Bootable Knoppix CD distribution | WANemv11-Setup-Guide.pdf | 1.1 | No | Historical only | The current repo is a source tree, not a bootable image. |
| Browser access at `/WANem` | WANemv11-Setup-Guide.pdf | 1.1 | Yes | Confirmed | `root/wanem_init.sh` prints `http://<IP>/WANem` and Apache serves `/var/www`. |
| Console command `reset` | WANemv11-Setup-Guide.pdf | 1.1 | Yes | Confirmed | Present in `root/wanem.sh`. |
| Console command `status` | WANemv11-Setup-Guide.pdf | 1.1 | Yes | Confirmed | Present in `root/wanem.sh`. |
| Console command `assign` | WANemv11-Setup-Guide.pdf | 1.1 | Yes | Confirmed | Present in `root/wanem.sh`. |
| Console command `wanemreset` | WANemv11-Setup-Guide.pdf, WANemv11-Troubleshooting-Guide.pdf | 1.1 | Yes | Confirmed | Present in `root/wanem.sh`; reset helper exists. |
| Basic mode | WANemv11-Setup-Guide.pdf | 1.1 | Yes | Confirmed | UI present in `index-basic.php`. |
| Advanced mode | WANemv11-Setup-Guide.pdf | 1.1 | Yes | Confirmed | UI present in `start_advance.php` and `index-advanced.php`. |
| Save/Restore available in next release | WANemv11-Setup-Guide.pdf | 1.1 | Yes | Conflicts with current tree | Current tree already includes `save-restore.php`, `download.php`, and `upload.php`. |
| WANalyzer section | WANem help and historical docs | 1.1-2.0 | Yes | Partially confirmed | UI and helper scripts exist; runtime behavior not exercised here. |
| NAT support for different subnets | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | `nat add`, `nat del`, and `nat show` are present in `root/wanem.sh`. |
| Bridge support | help.htm and current shell | 1.x-2.0 era | Yes | Partially confirmed | `bridge` commands and `brctl` usage exist; no runtime validation done. |
| Symmetrical network option | wanemulator_all_about_v2.0.pdf | 2.0 | No | Needs manual review | Historical docs mention it; current UI code should be checked in a running system before stronger claims. |
| Delay / latency | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | Supported conceptually and in UI/help. |
| Jitter | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | Supported conceptually and in UI/help. |
| Packet loss | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | Supported conceptually and in UI/help. |
| Duplication | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | Supported conceptually and in UI/help. |
| Corruption | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | Supported conceptually and in UI/help. |
| Reordering | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | Supported conceptually and in UI/help. |
| Disconnect tool | wanemulator_all_about_v2.0.pdf | 2.0 | Yes | Confirmed | `root/disc_new_port_int/` and PHP command paths exist. |
| Legacy route examples for Windows/Linux/AIX/Solaris | WANemv11-Setup-Guide.pdf, wanemulator_all_about_v2.0.pdf | 1.1-2.0 | No | Historical only | Useful as examples, not validated for modern hosts. |
| Internet Explorer recommendation | WANemv11-Setup-Guide.pdf, WANemv11-Troubleshooting-Guide.pdf | 1.1 | No | Historical only | Reflects the period, not a current requirement claim. |
| PuTTY / SSH remote administration | WANemv11-Setup-Guide.pdf | 1.1 | Partially | Partially confirmed | Console and remote-terminal assumptions remain, but exact login flow is not verified here. |
| Remote terminal limitations | current `remoteTerminal.php` | 3.0-era tree | Yes | Confirmed | `vi` and `startx` limitations are explicitly stated in the current tree. |
| WANem version label `v3.0` in UI | current tree | 3.0-era tree | Yes | Confirmed | Present in `title.html`, `about.html`, `About.txt`, and related files. |
| WANem 3.0.3 local repository version | top-level historical mirror README and local repository metadata | Local 3.0.3 marker derived from Beta 3.0.2 baseline | Yes | Local metadata only | The preserved mirror README includes a 3.0.3 marker but also says the files are based on Beta 3.0.2; official upstream 3.0.3 release status is not verified. |
| WANemv11-User-Guide.pdf content extraction | SourceForge document list | 1.1 | No | Not verified | Source URL identified, but extraction from temporary download was incomplete during this pass. |
