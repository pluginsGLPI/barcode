# Code Review n°2 — Plugin Barcode v3.0.4 (après corrections Roo)

**Reviewer :** Claude (Fable 5.1)
**Date :** 2026-11-09
**Commit revu :** `462c2eb` (develop, 4 commits après `0500056` qui appliquent CODE_REVIEW.md)
**Environnement de vérification :** conteneur `glpi-dev` = GLPI **12.0.1**, PHP **8.5.12**. Chaque appel au cœur cité ci‑dessous a été vérifié par grep dans `/var/www/glpi/src`.

---

## 0. Bilan des corrections issues de CODE_REVIEW.md (Roo)

| Réf. Roo | Statut | Commentaire |
|---|---|---|
| BUG-1 `$DB->query()` | ✅ OK | |
| BUG-2 `maxCodeHeight` dupliqué | ✅ OK | |
| BUG-3 `getOldRightsMappings()` | ⚠️ Discutable | La méthode a été « inventée » (clés `barcode`/`config` sans source). `migrateProfiles()` n'est appelé nulle part et la table qu'il lit est supprimée à l'install (`hook.php:190`). C'est du code mort : à **supprimer**, pas à compléter. |
| BUG-4 `$data` → `$info` | ✅ OK | |
| SEC-1 `checkItemByInv.php` | ❌ **Régression** | Le fix appelle `Html::displayErrorAndDie()` qui **n'existe pas en GLPI 12** → Fatal error (voir F-1). |
| SEC-2 `document.send.php` | ❌ **Régression** | Idem `Html::displayErrorAndDie()`. Et la ligne suivante appelle `Toolbox::sendFile()` qui n'existe pas non plus (F-2). Le fichier était déjà cassé, le fix n'a pas pu être testé. |
| SEC-3 upload logo | ✅ OK | Petit reste : `rename()` au lieu de `move_uploaded_file()` et pas de test de `$_FILES['logo']['error']`. |
| COH-1 → COH-5 | ✅ OK | |
| QC-1 | — | Non traité, acceptable (voir Q-2). |
| QC-2 `<select>` orphelin | ❌ Toujours présent | `inc/config.class.php:188-192`. |
| QC-3 « pas de CSRF » | ℹ️ Diagnostic erroné | Le CSRF est imposé par le cœur sur tout POST (`CheckCsrfListener`, et `inc/includes.php` en GLPI 10). Le vrai manque est l'**absence de contrôle de droits** (S-1). |

Conclusion : 2 des 3 correctifs « sécurité » introduisent un fatal sur GLPI 12. Les fixes n'ont visiblement pas été exécutés sur l'instance cible.

---

## 1. 🔴 Bloquants GLPI 12 (erreurs fatales)

### F-1 — `Html::displayErrorAndDie()` n'existe pas
**Fichiers :** `front/checkItemByInv.php:41,49` · `front/document.send.php:54,60,63`
Méthode absente de `src/Html.php` en GLPI 12 → `Error: Call to undefined method Html::displayErrorAndDie()` dès qu'un chemin d'erreur est atteint (itemtype invalide, item non trouvé, fichier absent).
**Fix :** lever les exceptions HTTP du cœur (GLPI 11+) :
```php
use Glpi\Exception\Http\BadRequestHttpException;
use Glpi\Exception\Http\NotFoundHttpException;
use Glpi\Exception\Http\AccessDeniedHttpException;

throw new BadRequestHttpException(__('Invalid item type', 'barcode'));
```

### F-2 — `Toolbox::sendFile()` n'existe pas
**Fichier :** `front/document.send.php:58`
Supprimé du cœur. Conséquence : l'aperçu du logo dans la page de configuration (`<img src=".../document.send.php?file=barcode/logo.png">`, `inc/config.class.php:93`) renvoie une erreur 500.
**Fix :** supprimer `document.send.php` (doublon de `send.php`, qui autorise déjà `.png`) et pointer l'`<img>` vers `send.php?file=logo.png`. Alternative cœur : `Toolbox::getFileAsResponse()` (`src/Toolbox.php:558`).

### F-3 — `MassiveAction::$items` est **private**
**Fichier :** `inc/barcode.class.php:223` et `:522` (`key($ma->items)`)
`src/MassiveAction.php:91` : `private array $items`. Accès → `Error: Cannot access private property`. L'action de masse **« Barcode - Print barcodes » plante à l'ouverture du sous‑formulaire** et au traitement.
De plus la condition ligne 522 (`== 'CommonITILObject'`) ne peut jamais être vraie : les clés sont `Ticket`, `Change`, `Problem` → les tickets ne génèrent jamais de code (B-6).
**Fix :**
```php
// ligne 223
if (is_a($ma->getItemtype(false), CommonITILObject::class, true)) { ... }
// ligne 522
if ($item instanceof CommonITILObject) { $codes[] = $item->getID(); }
```

### F-4 — Hook `pre_item_purge` vers une méthode inexistante
**Fichier :** `setup.php:64-65` → `['PluginBarcodeProfile', 'cleanProfiles']`
`cleanProfiles()` n'existe ni dans le plugin ni dans `Profile`. Purger un profil (par un utilisateur ayant un droit barcode) → fatal « Call to undefined method ».
**Fix :** supprimer le hook. Le cœur supprime déjà les lignes `glpi_profilerights` à la purge d'un profil.

### F-5 — Incompatible GLPI 10 malgré `PLUGIN_BARCODE_MIN_GLPI = '10.0.0'`
**Fichiers :** `inc/barcode.class.php:52`, `inc/config.class.php:48`, `inc/profile.class.php:44` (`static string $rightname`)
En GLPI 10, `CommonGLPI::$rightname` est déclaré **sans type** (`public static $rightname = '';`, vérifié sur la branche `10.0/bugfixes`). Une classe fille qui ajoute `string` → `Fatal error: Type of PluginBarcodeConfig::$rightname must not be defined (as in class CommonDBTM)`. `__s()`, les exceptions HTTP et la suppression d'`inc/includes.php` (Q-4) vont dans le même sens.
**Fix :** assumer GLPI 11+ : `PLUGIN_BARCODE_MIN_GLPI = '11.0.0'` et retirer les 7 `include('../../../inc/includes.php')`.

---

## 2. 🟡 Sécurité

### S-1 — Aucun contrôle de droits sur les endpoints POST (Haute)
**Fichiers :** `front/config.form.php` (changer le type par défaut, vider le cache, supprimer/uploader le logo), `front/config_type.form.php` (`$pbcconf->update($_POST)` sans `check()`), `front/barcode.form.php` (génération PDF).
En GLPI 12 le Firewall applique aux scripts legacy des plugins la stratégie `authenticated` uniquement (`src/Glpi/Http/Firewall.php:91`). **Tout utilisateur connecté, y compris en interface simplifiée**, peut modifier la configuration du plugin. Le cœur ne vérifie que le CSRF, pas les droits.
**Fix :** en tête de chaque script :
```php
Session::checkRight('config', UPDATE);                   // config*.form.php
Session::checkRight('plugin_barcode_barcode', CREATE);   // barcode.form.php
```
et donner un `$rightname` à `PluginBarcodeConfig_Type` (`'plugin_barcode_config'`) pour que `check()` fonctionne.

### S-2 — Écriture de fichiers à chemin contrôlé par l'utilisateur (Moyenne)
**Fichier :** `inc/barcode.class.php:462-477` (`create()`), `:459` (nom du PDF)
Le nom de fichier est `docsPath . $p_code . '_' . $p_type . '.png'` où `$p_code` = `$_POST['code']` (via `barcode.form.php`) et `$p_type` = `$_POST['type']`, sans filtrage. `code=../../../../tmp/x` écrit `x_code128.png` hors du répertoire du plugin (contenu = image PEAR ou fichier vide). `Image_Barcode::draw()` filtre `$type` mais pas le plugin.
Effet de bord fonctionnel : un n° d'inventaire contenant `/` ne produit jamais d'étiquette (fichier introuvable, ignoré silencieusement).
**Fix :** `in_array($type, $this->getCodeTypes(), true)` sinon rejet ; nommer les fichiers cache `md5($code) . '_' . $type . '.png'`.

### S-3 — Téléchargement des fichiers des autres utilisateurs (Basse)
**Fichiers :** `front/send.php`, `front/document.send.php`
Les noms sont prévisibles (`{glpiID}_{type}.pdf`, `barcode_qrcodes_user_{id}.zip`) et tout utilisateur connecté peut les télécharger (fuite de n° de série/inventaire). `document.send.php:44` permet même un accès **anonyme** si la FAQ publique est activée.
**Fix :** n'autoriser que les fichiers préfixés par `Session::getLoginUserID()`, ou stocker un jeton aléatoire en session et l'exiger dans l'URL.

### S-4 — Upload logo (Basse)
`front/config.form.php:75` : utiliser `move_uploaded_file()` et vérifier `$_FILES['logo']['error'] === UPLOAD_ERR_OK` avant `getimagesize()`.

### S-5 — `checkItemByInv.php` exige `config` UPDATE (fonctionnel, mais lié aux droits)
**Fichier :** `front/checkItemByInv.php:34`
C'est la page cible des QR codes « URL par n° d'inventaire » ; seuls les super‑admins peuvent donc scanner une étiquette. Il manque aussi `isset($_GET['inventoryNumber'])`, et la redirection est faite *après* `Html::header()`.
**Fix :** `Session::checkLoginUser()` puis `Html::redirect($item->getFormURLWithID(...))` avant tout affichage ; la fiche cible vérifiera `canView()`.

### S-6 — Échappement HTML
`inc/barcode.class.php:191` : `value='$code'` (otherserial) non échappé (code mort aujourd'hui, voir B-4). `inc/config.class.php` : valeurs DB injectées sans `htmlescape()`. Faible, à corriger au passage.

---

## 3. 🟠 Bugs fonctionnels

### B-1 — ZIP QR codes : `ZipArchive::CREATE` sans `OVERWRITE`
**Fichier :** `inc/qrcode.class.php:586`
Si une archive de moins de 5 min existe pour l'utilisateur (lignes 578-583), elle est **rouverte et complétée** : la nouvelle archive contient aussi les étiquettes de la génération précédente.
**Fix :** `$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)` et supprimer le test d'âge.

### B-2 — Types de message en chaîne de caractères
**Fichier :** `inc/qrcode.class.php:562,613,618` (`'warning'`, `'information'`, `'error'`)
Le cœur attend les constantes `INFO`(0) / `ERROR`(1) / `WARNING`(2). Le template de toasts ne reconnaît pas ces clés : **toutes les erreurs ZIP s'affichent en bleu « info »**.
**Fix :** `WARNING`, `INFO`, `ERROR`.

### B-3 — `itemDone()` appelé avec un compteur au lieu des IDs
**Fichiers :** `inc/qrcode.class.php:651` (`count($ids)`), `inc/barcode.class.php:541` (`0`)
`itemDone($itemtype, $id, $result)` attend un ID ou un tableau d'IDs : les vrais éléments restent dans `$remainings` et le cœur affiche un décompte faux (« 0 réussi, N non traités »).
**Fix :** `$ma->itemDone($item->getType(), $ids, $actionResult);`

### B-4 — Entrée de menu « Outils › Barcode » inutilisable
**Fichiers :** `setup.php:70`, `inc/barcode.class.php:66-85`, `front/barcode.form.php:58`
Le menu pointe sur `barcode.form.php` en GET, qui appelle immédiatement `printPDF($_POST)` avec un tableau vide → warnings (`type` indéfini), retour `0`, message « Fichier généré » vers `send.php?file=0`, puis `Html::back()`. `PluginBarcodeBarcode::showForm()` (ligne 170) n'est appelé nulle part.
Idem `setup.php:73` : `helpdesk_menu_entry = true` alors que `Html.php:1145` attend une chaîne (chemin) → entrée vide en interface simplifiée.
**Fix :** dans `barcode.form.php`, si pas de POST : `Html::header()` + `showForm()` ; sinon générer. Mettre `'/front/barcode.form.php'` ou retirer l'entrée helpdesk.

### B-5 — `create()` : fatal PHP 8 sur code invalide + cache empoisonné
**Fichier :** `inc/barcode.class.php:462-477`
`@imagepng(@$barcode->draw(...))` : si `draw()` renvoie une `PEAR_Error` (ean13/upca exigent 12-13 chiffres), `imagepng()` lève une **TypeError** que `@` ne supprime pas → 500. Si `draw()` échoue silencieusement, un `.png` **vide** est écrit et mis en cache ; `file_exists()` est ensuite vrai pour toujours, `getimagesize()` renvoie `false`, `imagecreatefrompng()` renvoie `false` et `Cezpdf::addImage(false)` plante — jusqu'à « Vider le cache ».
**Fix :** tester `$img instanceof GdImage` avant `imagepng()`, ne pas écrire le fichier en cas d'échec, valider le code selon le type en amont.

### B-6 — Tickets/changements/problèmes : action proposée mais jamais de code généré
Voir F-3 (`inc/barcode.class.php:522`). Le libellé ligne 223 ne couvre que `Ticket`.

### B-7 — Options d'affichage sans effet
- `inc/qrcode.class.php:430` : la liste « QR Code format » (bmp/png/jpg) n'est **jamais lue** → toujours BMP.
- Tous les « Display … » du formulaire QR (`displayserialnumber`, `displayid`, `displayname`, `displayurl`, `displayqrcodedate`, …) alimentent `$b_content` (ligne 258) qui est **ignoré** par `processMassiveActionsForOneItemtype` (ligne 541 n'utilise que `$values[0]`). L'archive ne contient que l'image.
- `inc/barcode.class.php:277/534` : « Display labels » est posté mais `printPDF()` n'utilise que `displayData`, que personne ne fournit.
**Fix :** retirer ces contrôles ou les implémenter (texte sous le QR via GD avant conversion).

### B-8 — Précédence d'opérateur et variable morte
`inc/qrcode.class.php:159,172,185,211` : `$item->fields['x'] ?? '' !== ''` est lu `$item->fields['x'] ?? true`. `$have_content` n'est jamais utilisé → supprimer les 5 blocs.

### B-9 — URL par n° d'inventaire non encodée
`inc/qrcode.class.php:153` : `$itemByInvNumber` et `$itemtype` concaténés sans `rawurlencode()` → QR invalide si le n° contient espace, `&`, `#`, `+`.

### B-10 — Chemins absolus `/plugins/barcode/...` et `/files/_plugins/...`
`inc/barcode.class.php:71-80,185,459,538`, `inc/config.class.php:52,93,138`, `front/*.php`. Sans `$CFG_GLPI['root_doc']`, tout casse si GLPI est servi dans un sous‑répertoire (`https://host/glpi/`). `Plugin::getWebDir()` utilisé par l'upstream n'existe plus en 12 ; utiliser `$CFG_GLPI['root_doc'] . '/plugins/barcode/…'`.

### B-11 — Divers
- `inc/barcode.class.php:341` : `ezStartPageNumbers(...)**.**` — un point au lieu d'un `;` (fonctionne par accident via concaténation).
- `inc/barcode.class.php:445` : `$p_params['border']` indéfini depuis `barcode.form.php` → warning.
- `front/checkItemByInv.php:46` : `getFromDBByCrit()` renvoie `false` si plusieurs items partagent le n° d'inventaire → « No item found » trompeur.
- `hook.php` : `$DB->doQuery(...) or die(...)` — `doQuery()` lève `QueryException` en 11+ ; le `or die` est mort. Passer par `Migration`/`$DB->insert()`.
- `inc/config.class.php:188-192` : `<select> URL:` orphelin (QC-2 Roo, toujours là).

---

## 4. 🔵 Qualité / dette technique

### Q-1 — Code mort à supprimer (≈ 400 lignes)
- `inc/qrcode.class.php:46-122` : polyfill `imagebmp()` — natif depuis PHP 7.2 (plugin exige ≥ 7.4), et bogué (`pack('V')` écrit 4 octets/pixel dans un BMP 24 bits).
- `inc/qrcode.class.php:665-838` : `pngToBmp()` réécrit l'encodeur BMP à la main (~170 lignes). Équivalent natif : `imagecropauto($img, IMG_CROP_WHITE)` + `imagepalettetotruecolor()` + `imagebmp($img, $file, false)`.
- `inc/barcode.class.php` : `showForm()` (170, jamais appelé — ou à réactiver, cf. B-4), `getSpecificMassiveActions()` (481, classe non `CommonDBTM`), branche `QRcode` de `printPDF()` (303-305, 383-385 ; plus aucun appelant), `cleanQRcodefiles($rand, 0)` (543).
- `inc/profile.class.php` : `uninstallProfile()`, `createFirstAccess()`, `addDefaultProfileInfos()`, `migrateProfiles()`, `getOldRightsMappings()`.
- `front/document.send.php` entier (cf. F-2) ; `$doc = new Document` ; `$_POST['length'|'prefixe'|'format']` dans `barcode.form.php` jamais lus ; `global $CFG_GLPI` inutilisés.

### Q-2 — Droits incohérents
Le droit `plugin_barcode_config` (UPDATE) est créé dans les profils mais **jamais vérifié** : les pages de config testent `config`. `plugin_barcode_barcode` ne protège que l'affichage du menu et du hook massive action, pas les traitements. L'onglet profil utilise `$rightname = 'config'` pour `canedit` alors que le POST va vers `profile.form.php` (droit `profile`).
**Fix :** décider : soit supprimer `plugin_barcode_config` et garder `config`, soit l'utiliser partout.

### Q-3 — i18n
36 appels `__('…')` **sans domaine `'barcode'`** dans `inc/qrcode.class.php` et `front/*.php` (ex. `'Display serial number'`, `'URL by inventory number'`, `'QR Code format'`, `'Invalid item type'`). Ils ne passent pas par `locales/*.po`. `locales/barcode.pot` n'a pas été régénéré depuis les ajouts Tamatoa.

### Q-4 — `inc/includes.php` obsolète
7 fichiers `front/` l'incluent ; en GLPI 12 le fichier ne contient plus que `Toolbox::deprecated('The inc/includes.php file is obsolete.')` → une dépréciation loguée à chaque requête. À supprimer dès que le min passe à 11 (F-5).

### Q-5 — Métadonnées / outillage
- `composer.json` : `php >= 7.4`, `platform.php = 7.4.0` alors que GLPI 12 impose PHP ≥ 8.2 → résolution de dépendances faussée. Passer à `>=8.2`.
- `.travis.yml` (PHP 7.2/7.4) obsolète → supprimer ; `.github/workflows/release.yml` à vérifier.
- `barcode.xml` : pointe toujours vers `pluginsGLPI/barcode`, dernière version 2.7.1, aucune 3.0.x.
- `README.md` : parle du plugin « Tag » (copier‑coller) et de Transifex upstream.

### Q-6 — Performance mineure
`hook.php:plugin_barcode_MassiveActions()` instancie `new $itemtype()` + `getEmpty()` à chaque affichage de liste pour chaque itemtype. Acceptable, mais `$DB->fieldExists($itemtype::getTable(), 'otherserial')` est moins coûteux.

### Q-7 — Environnement de dev (hors code)
`plugins/` contient **trois copies** du plugin (`barcode`, `barcode_current`, `barcode_roo_modified`) toutes avec la clé `barcode`. GLPI scanne tous les répertoires de `plugins/` ; risque de « Cannot redeclare plugin_init_barcode() » ou de chargement de la mauvaise copie. Sortir les copies de `plugins/`.

---

## 5. Résumé

| Catégorie | Nb | Points clés |
|---|---|---|
| 🔴 Fatals GLPI 12 | 5 | `displayErrorAndDie`, `sendFile`, `$ma->items` privé, `cleanProfiles`, min GLPI 10 mensonger |
| 🟡 Sécurité | 6 | Aucun contrôle de droits sur les POST (S-1), écriture de fichier non filtrée (S-2) |
| 🟠 Fonctionnel | 11 | ZIP cumulatif (B-1), menu cassé (B-4), cache empoisonné (B-5), options sans effet (B-7) |
| 🔵 Qualité | 7 | ~400 lignes de code mort, droits incohérents, i18n, métadonnées |

### Ordre de correction recommandé
1. **F-1 → F-4** : rendre le plugin exécutable sur GLPI 12 (4 fatals, ~20 lignes). Tester réellement chaque chemin (massive action Barcode, scan QR, aperçu logo, purge d'un profil).
2. **F-5 + Q-4** : `MIN_GLPI = 11.0.0`, retirer `inc/includes.php`.
3. **S-1, S-2** : `Session::checkRight()` sur les 3 formulaires, whitelist du type et hash du code dans les noms de fichiers.
4. **B-1, B-2, B-3** : ZIP `OVERWRITE`, constantes de message, `itemDone($ids)`.
5. **B-4 / B-5 / B-7** : décider si le formulaire unitaire et les options d'affichage sont conservés ; sinon les retirer avec le code mort (Q-1).
6. Q-2, Q-3, Q-5 en fond de tâche.

### Méthode
Les correctifs de Roo n'ont pas été exécutés : deux « fixes » sécurité appellent des méthodes absentes du cœur cible. Pour la suite, chaque correction devrait être validée sur `glpi-dev` (GLPI 12.0.1 / PHP 8.5) avec au minimum : ouverture du sous‑formulaire des deux actions de masse, génération d'un PDF et d'un ZIP, scan d'une URL par n° d'inventaire, upload/suppression du logo, purge d'un profil de test.

---

## 6. Statut des corrections (branche `fix/glpi12-review-2`, version 3.1.0)

Toutes les corrections ont été testées sur `glpi-dev` (GLPI 12.0.1 / PHP 8.5) : actions de masse exécutées via `MassiveAction`, pages appelées en HTTP avec un compte super-admin et un compte self-service, installation neuve et mise à jour depuis 3.0.4.

| Réf. | Statut | Commit |
|---|---|---|
| F-1 `displayErrorAndDie` | ✅ exceptions HTTP du cœur | `fix: GLPI 12 fatal errors` |
| F-2 `sendFile` | ✅ `document.send.php` supprimé, logo servi par `send.php` | idem |
| F-3 `$ma->items` privé | ✅ `getItemtype()` / `instanceof CommonITILObject` | idem |
| F-4 `cleanProfiles` | ✅ hook supprimé | idem |
| F-5 min GLPI 10 | ✅ min 11.0.0, PHP ≥ 8.2 | `chore: require GLPI 11+` |
| S-1 droits | ✅ `plugin_barcode_config` / `plugin_barcode_barcode` vérifiés partout, `can(READ)` par élément | `security: …` |
| S-2 chemins | ✅ type en liste blanche, validation par symbologie, cache `cache_<md5>_<type>.png` | idem |
| S-3 téléchargements | ✅ fichiers préfixés par l'ID du propriétaire, seul lui peut les télécharger | idem |
| S-4 upload logo | ✅ `UPLOAD_ERR_OK`, `move_uploaded_file()` | idem |
| S-5 scan QR | ✅ connexion seule, redirection avant affichage, doublons gérés | `fix: GLPI 12 fatal errors` |
| S-6 échappement | ✅ formulaires de config et formulaire unitaire | `security: …` |
| B-1 ZIP cumulatif | ✅ `CREATE \| OVERWRITE` | `fix: QR code ZIP export` |
| B-2 types de message | ✅ constantes `INFO/WARNING/ERROR` | idem |
| B-3 `itemDone` | ✅ IDs transmis, compteurs justes | idem + `security: …` |
| B-4 menu Outils | ✅ GET affiche le formulaire, POST génère ; entrée helpdesk fonctionnelle | `security: …` |
| B-5 cache empoisonné | ✅ pas de fichier écrit en cas d'échec, message listant les valeurs refusées | idem |
| B-6 tickets | ✅ code-barres de l'ID | idem |
| B-7 options sans effet | ✅ options « Display… » et « QR Code format » retirées ; « Display labels » imprime le nom sous le code-barres | idem + `fix: QR code ZIP export` |
| B-8 `$have_content` | ✅ supprimé | `fix: QR code ZIP export` |
| B-9 URL non encodée | ✅ RFC 3986 | idem |
| B-10 chemins absolus | ✅ `$CFG_GLPI['root_doc']` | `security: …` |
| B-11 divers | ✅ | plusieurs |
| Q-1 code mort | ✅ ~450 lignes retirées ; conversion BMP native GD, sortie identique à l'octet | `fix: QR code ZIP export` + `refactor: …` |
| Q-2 droits | ✅ droit plugin pour la config, onglet profil aussi pour les profils helpdesk | `refactor: …` |
| Q-3 i18n | ✅ `.pot` régénéré, 21 traductions françaises ajoutées | `chore: translations…` |
| Q-4 `includes.php` | ✅ supprimé | `chore: require GLPI 11+` |
| Q-5 métadonnées | ✅ composer, `barcode.xml`, README, `.travis.yml` supprimé | `chore: …` |
| Q-6 performance | ✅ `isField()` | `perf: …` |
| Q-7 copies du plugin | ✅ déplacées dans `/opt/glpi-dev/plugin-backups/` (hors dépôt) | — |
| QC-1 Roo (`CommonDBTM`) | ⏸ non traité : sans impact fonctionnel | — |

### Points d'attention pour le déploiement
- **Mise à jour requise** : la version passe à 3.1.0, GLPI désactive le plugin jusqu'à ce qu'un administrateur lance la mise à jour dans *Configuration > Plugins*.
- **Droit de configuration** : la page de configuration exige désormais le droit plugin « Gérer la configuration » et non plus le droit global `config`. Seul Super-Admin l'a par défaut ; l'attribuer aux autres profils si besoin.
- **Contenu des QR codes inchangé**, sauf l'URL par n° d'inventaire désormais encodée (`INV%20002` au lieu de `INV 002`).
- **Noms des fichiers téléchargés** : `barcode_qrcodes.zip` et `barcode_<type>.pdf`.
- Les traductions autres que le français n'ont pas été mises à jour (passage par Transifex).
