# Barcode Plugin for GLPI 11

> **Migration to GLPI 11.x** by [Dawing S.A.S.](https://dawing.com.co) — original plugin by [David DURIEUX / pluginsGLPI](https://github.com/pluginsGLPI/barcode)

## What this plugin does

| Feature | Description |
|---|---|
| **Linear barcodes** | Generates Code39, Code128, EAN13, INT25, Postnet, UPC-A from the asset inventory number (`otherserial`) |
| **QR codes** | Generates QR codes with configurable content: serial number, inventory number, ID, UUID, name, URL, date |
| **PDF export** | Automatic grid layout, multiple codes per page |
| **Massive action** | Available from any GLPI asset list: "Print barcodes" and "Print QRcodes" |
| **Per-type config** | Margins, page size, orientation, max code dimensions, text size |
| **Company logo** | Upload a PNG logo that appears above each code in the PDF |

## Requirements

| Component | Minimum version |
|---|---|
| GLPI | 11.0.0 |
| PHP | 8.1 (tested on 8.4) |
| PHP extensions | `gd`, `mbstring`, `xml`, `curl`, `zip` |
| Composer | 2.x |

## Installation

```bash
# 1. Place plugin in GLPI plugins directory
cp -r barcode/ /var/www/glpi/plugins/

# 2. Install dependencies
cd /var/www/glpi/plugins/barcode
composer install --no-dev --optimize-autoloader

# 3. Set permissions (Rocky Linux / CentOS / AlmaLinux)
chown -R apache:apache /var/www/glpi/plugins/barcode
mkdir -p /var/www/glpi/files/_plugins/barcode
chown -R apache:apache /var/www/glpi/files/_plugins/barcode

# 4. SELinux (if enforcing)
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/glpi/plugins/barcode(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/glpi/files/_plugins/barcode(/.*)?"
restorecon -Rv /var/www/glpi/plugins/barcode
restorecon -Rv /var/www/glpi/files/_plugins/barcode
```

Then go to **Setup → Plugins → Barcode → Install → Enable**

## Changes from v2.7.1 (GLPI 10) to v3.0.0 (GLPI 11)

| File | Issue | Fix |
|---|---|---|
| `setup.php` | Version bound `10.0.99` blocked install on GLPI 11 | Updated to `11.0.0–11.0.99` |
| `setup.php` | `csrf_compliant` hook removed in GLPI 11 | Removed |
| `setup.php` | `Plugin::getWebDir()` deprecated in GLPI 11 | Replaced with direct paths `/plugins/barcode/...` |
| `hook.php` | `$DB->query() or die()` forbidden in GLPI 11 | Replaced with `$DB->doQueryOrDie()` |
| `hook.php` | `PRIMARY KEY (ID)` uppercase bug (MariaDB strict) | Fixed to lowercase `id` |
| `inc/qrcode.class.php` | Special chars (tildes, ñ) broken in QR content | Removed `__()` from QR embedded text |
| `inc/qrcode.class.php` | Massive action UI column purpose was unclear | Added descriptive labels explaining each column |
| `inc/config.class.php` | `array_keys()[0]` deprecated pattern | Updated to `array_key_first()` |
| `inc/config.class.php` | Raw `<input type=submit>` | Replaced with `Html::submit()` |
| `front/*.php` | `include('../../../inc/includes.php')` broken path | Fixed for GLPI 11 router |
| `front/send.php` | No filename validation | Added strict PDF-only validation |
| `front/document.send.php` | No MIME validation on logo | Added `finfo` MIME type check |
| `front/checkItemByInv.php` | No itemtype class validation | Added `is_subclass_of(CommonDBTM)` check |
| `composer.json` | PHP platform forced to `7.4` | Updated to `>=8.1` |

## Tested environment

- **GLPI** 11.0.6
- **Rocky Linux** 10
- **PHP** 8.4.19 (Remi repository)
- **MariaDB** (centralized server)
- **Apache** 2.4.63

## Usage

### From asset lists (Massive action)

1. Go to any asset list (Computers, Monitors, Printers, etc.)
2. Select one or more assets
3. In the massive action bar: **Barcode – Print barcodes** or **Barcode – Print QRcodes**
4. Configure options — left column = include in QR data, right column = show as visible label under the code
5. Click **Create** → download link for the generated PDF appears

### QR code content

The QR code embeds plain ASCII text (no special characters) to ensure maximum scanner compatibility:

```
Serial number = ABC123
Inventory number = IM00001
ID = 42
Name = LAPTOP-001
URL = https://glpi.example.com/plugins/barcode/front/checkItemByInv.php?inventoryNumber=IM00001&itemtype=Computer
QRcode date = 2026-03-30
```

### Company logo

Go to **Setup → Plugins → Barcode config** and upload a PNG logo (recommended: 300×60px, transparent background).

## License

[AGPL v3+](LICENSE)

## Credits

- Original plugin: [David DURIEUX](mailto:d.durieux@siprossii.com) and contributors
- GLPI 11 migration: [Cristian David Baquero / Dawing S.A.S.](https://dawing.com.co)
