# Code Review — Plugin Barcode v3.0.4 (GLPI 12 compatibility)

**Reviewer:** Roo (AI Code Assistant)  
**Date:** 2026-11-09  
**Repo:** `ISPF/info-glpi-barcode-ispf` — branche `develop`  
**Commit:** `0500056` (Modif Tamatoa)

---

## 🔴 BUGS — À corriger immédiatement

### BUG-1: `$DB->query()` résiduel dans hook.php (ligne 192)
**Fichier:** `hook.php:192`  
**Sévérité:** Critique (deprecation GLPI 12, cassera dans GLPI 13)

Toutes les occurrences de `$DB->query()` ont été remplacées par `$DB->doQuery()` sauf **une** :
```php
// Ligne 192 — MANQUÉE
$DB->query($query) or die("error deleting glpi_plugin_barcode_profiles");
```
**Fix:** Remplacer par `$DB->doQuery($query)`

---

### BUG-2: Champ `maxCodeHeight` dupliqué dans config.class.php (lignes 183-184)
**Fichier:** `inc/config.class.php:183-184`  
**Sévérité:** Mineure (bug d'affichage, la 2ème valeur écrase la 1ère en POST)

```php
echo "<input type='text' size='20' name='maxCodeHeight' value='".$config['maxCodeHeight']."'>";
echo "<input type='text' size='20' name='maxCodeHeight' value='" . $config['maxCodeHeight'] . "'>";
```
Deux `<input>` identiques avec le même `name`. L'utilisateur voit 2 champs.  
**Fix:** Supprimer la ligne 184.

---

### BUG-3: Méthode `getOldRightsMappings()` non définie dans profile.class.php (ligne 169)
**Fichier:** `inc/profile.class.php:169`  
**Sévérité:** Haute (fatal error si `migrateProfiles()` est appelée)

```php
$oldrights = self::getOldRightsMappings();
```
Cette méthode n'existe nulle part dans la classe `PluginBarcodeProfile`. Si `migrateProfiles()` est invoquée (migration depuis une version très ancienne), PHP lèvera un **Fatal Error**.  
**Fix:** Soit implémenter la méthode, soit ajouter un guard `method_exists()`.

---

### BUG-4: Variable `$data` au lieu de `$info` dans profile.class.php (ligne 238)
**Fichier:** `inc/profile.class.php:238`  
**Sévérité:** Moyenne (utilise la mauvaise variable d'itération)

```php
foreach ($a_rights as $info) {
   // ...
   $_SESSION['glpiactiveprofile'][$data['field']] = $right;  // ← $data au lieu de $info
}
```
`$data` fait référence au dernier élément du tableau `$a_rights` (de la boucle précédente ligne 214), pas à l'élément courant `$info`.  
**Fix:** Remplacer `$data['field']` par `$info['field']`.

---

## 🟡 SÉCURITÉ — À corriger

### SEC-1: Injection de classe dans checkItemByInv.php (ligne 38-39)
**Fichier:** `front/checkItemByInv.php:38-39`  
**Sévérité:** Haute

```php
$itemtype = $_GET['itemtype'];
$item = new $itemtype();
```
`$_GET['itemtype']` est utilisé directement pour instancier une classe sans aucune validation. Un attaquant pourrait instancier n'importe quelle classe PHP accessible.  
**Fix:** Valider que `$itemtype` est un `CommonDBTM` valide :
```php
$itemtype = $_GET['itemtype'] ?? '';
if (!is_a($itemtype, CommonDBTM::class, true)) {
   Html::displayErrorAndDie(__('Invalid item type'), true);
}
```

---

### SEC-2: Variable `$send` non vérifiée dans document.send.php (ligne 53-63)
**Fichier:** `front/document.send.php:53-63`  
**Sévérité:** Basse

`$send` est initialisé à `false`, mis à `true` si `$splitter[0] == "barcode"`, mais **le fichier est envoyé que `$send` soit true ou false**. Le check est mort.  
**Fix:** Ajouter `if ($send)` avant `Toolbox::sendFile()`.

---

### SEC-3: Upload logo sans validation de type dans config.form.php (ligne 67)
**Fichier:** `front/config.form.php:67`  
**Sévérité:** Moyenne

```php
rename($_FILES['logo']['tmp_name'], GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png');
```
Aucune vérification que le fichier uploadé est réellement une image PNG. Un fichier PHP pourrait être renommé en `logo.png`.  
**Fix:** Ajouter une validation `getimagesize()` ou `mime_content_type()` avant le rename.

---

## 🟠 COHÉRENCE — Versions, auteurs, métadonnées

### COH-1: Mention "GLPI 11" alors que c'est GLPI 12
**Fichier:** `setup.php:93`

```php
'author' => 'David DURIEUX, Jean Marc GRISARD, Vincent MAZZONI (GLPI 11 Update by Tamatoa)',
```
**Fix:** Changer en `(GLPI 12 Update by Tamatoa)` ou `(GLPI 10+ Update by Tamatoa)`.

---

### COH-2: Copyright obsolète dans tous les headers
**Fichiers:** Tous les fichiers PHP (14 fichiers)

```php
Copyright (C) 2009-2016 by the Barcode plugin Development Team.
@copyright Copyright (c) 2009-2016 Barcode plugin Development team
```
Le plugin est activement développé en 2026. **Fix:** Mettre à jour en `2009-2026`.

---

### COH-3: URLs mortes dans tous les headers
**Fichiers:** Tous les fichiers PHP

```php
https://forge.indepnet.net/projects/barscode
```
- `forge.indepnet.net` n'existe plus
- Typo : « barscode » au lieu de « barcode »

**Fix:** Remplacer par `https://github.com/ISPF/info-glpi-barcode-ispf`

---

### COH-4: Homepage pointe vers le repo upstream
**Fichier:** `setup.php:94`

```php
'homepage' => 'https://github.com/pluginsGLPI/barcode',
```
Le repo réel est le fork ISPF.  
**Fix:** Mettre `https://github.com/ISPF/info-glpi-barcode-ispf` (ou laisser si on veut pointer vers l'upstream).

---

### COH-5: AUTHORS.txt incomplet
**Fichier:** `AUTHORS.txt`

Ne mentionne pas Tamatoa ni les contributions ISPF.  
**Fix:** Ajouter :
```
- Tamatoa (tamatoad@ispf.pf) : GLPI 10/11/12 compatibility, QR code ZIP export
```

---

## 🔵 QUALITÉ DE CODE — Améliorations recommandées

### QC-1: `PluginBarcodeBarcode` n'étend pas `CommonDBTM`
**Fichier:** `inc/barcode.class.php:49`

La classe est enregistrée via `Plugin::registerClass()` mais n'étend aucune classe GLPI. Cela fonctionne pour les hooks massive actions mais n'est pas standard. Le `static string $rightname` est une convention GLPI pour les classes héritant de `CommonGLPI`.

---

### QC-2: `<select>` URL orphelin dans config.class.php (lignes 188-193)
**Fichier:** `inc/config.class.php:188-193`

```php
echo "<select> URL:";
echo "<option value='id'>".__('By ID', 'barcode')."</option>";
echo "<option value='serial'>".__('By serial number', 'barcode')."</option>";
echo "</select>";
```
Ce `<select>` n'a pas de `name` et n'est pas lié à un champ de formulaire. Il apparaît dans l'UI mais ne fait rien. Soit le compléter, soit le supprimer.

---

### QC-3: Pas de validation CSRF dans barcode.form.php
**Fichier:** `front/barcode.form.php:58`

```php
$barcode = new PluginBarcodeBarcode();
$file = $barcode->printPDF($_POST);
```
`$_POST` est passé directement à `printPDF()` sans validation CSRF ni sanitization.

---

## 📊 Résumé

| Catégorie | Nombre | Action |
|-----------|--------|--------|
| 🔴 Bugs | 4 | Fix immédiat |
| 🟡 Sécurité | 3 | Fix prioritaire |
| 🟠 Cohérence | 5 | Fix dans ce commit |
| 🔵 Qualité | 3 | Backlog |
| **Total** | **15** | |
