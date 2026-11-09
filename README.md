# Barcode GLPI plugin (ISPF fork)

Print barcodes and QR codes for GLPI assets and tickets.

Fork of [pluginsGLPI/barcode](https://github.com/pluginsGLPI/barcode) maintained by ISPF.

## Requirements

* GLPI 11.0 or 12.0
* PHP 8.2+ with the `gd` and `zip` extensions

## Features

* **Barcodes (PDF)**: massive action "Barcode - Print barcodes" on any item with an
  inventory number, and on tickets, changes and problems (barcode of the ID).
  Supported symbologies: Code39, code128, ean13, int25, postnet, upca.
  Optionally prints the item name under each barcode and a company logo above it.
* **Single barcode**: menu *Tools > Barcode* prints one value on a sheet.
* **QR codes (ZIP)**: massive action "Barcode - Print QRcodes" produces a ZIP archive with
  one 24-bit BMP per item, named after the item (MS Access / Brother P-touch labels).
  The QR code can contain the serial number, inventory number, ID, UUID, name,
  item URL and generation date.
* **Scan by inventory number**: QR codes can embed
  `/plugins/barcode/front/checkItemByInv.php?itemtype=...&inventoryNumber=...`
  which redirects to the item form.

## Rights

Configured in *Administration > Profiles > Barcode*:

| Right | Grants |
|---|---|
| Generation of barcode (Create) | massive actions, *Tools > Barcode*, helpdesk menu entry |
| Manage configuration (Update) | plugin configuration page (default type, page layouts, logo, cache) |

Generated files are stored in `files/_plugins/barcode/` and can only be downloaded by
the user who generated them.

## Installation

```sh
cd plugins
git clone https://github.com/ISPF/info-glpi-barcode-ispf.git barcode
cd barcode && composer install --no-dev
```

Then install and enable the plugin in *Setup > Plugins*.

## Translations

Run `tools/extract_template.sh` to regenerate `locales/barcode.pot`, then update the
`.po` files and compile them to `.mo`.

## Contributing

* Open an issue for each bug/feature so it can be discussed
* Follow the [development guidelines](https://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html)
* Work on a branch and open a pull request against `develop`
