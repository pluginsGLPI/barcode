<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI - Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

// --- Vaciar caché de imágenes generadas ---
if (isset($_POST['dropCache'])) {
   $dir = GLPI_PLUGIN_DOC_DIR . '/barcode';
   if (is_dir($dir)) {
      if ($dh = opendir($dir)) {
         while (($file = readdir($dh)) !== false) {
            // No eliminar el logo ni los directorios de navegación
            if ($file !== '.' && $file !== '..' && $file !== 'logo.png') {
               @unlink($dir . '/' . $file);
            }
         }
         closedir($dh);
      }
   }
   Session::addMessageAfterRedirect(__('The cache has been emptied.', 'barcode'));

// --- Eliminar logo ---
} elseif (isset($_POST['dropLogo'])) {
   if (is_file(GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png')) {
      @unlink(GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png');
   }
   Session::addMessageAfterRedirect(__('The logo has been removed.', 'barcode'));

// --- Subir nuevo logo ---
} elseif (!empty($_FILES['logo']['name'])) {
   $logoPath = GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png';

   // Eliminar logo anterior si existe
   if (is_file($logoPath)) {
      @unlink($logoPath);
   }

   // Validar que sea una imagen PNG válida antes de mover
   $tmpName = $_FILES['logo']['tmp_name'];
   $imgInfo = @getimagesize($tmpName);
   if ($imgInfo && in_array($imgInfo['mime'], ['image/png', 'image/jpeg', 'image/gif'])) {
      // Convertir a PNG si no lo es ya
      if ($imgInfo['mime'] === 'image/jpeg') {
         $img = imagecreatefromjpeg($tmpName);
         imagepng($img, $logoPath);
         imagedestroy($img);
      } elseif ($imgInfo['mime'] === 'image/gif') {
         $img = imagecreatefromgif($tmpName);
         imagepng($img, $logoPath);
         imagedestroy($img);
      } else {
         rename($tmpName, $logoPath);
      }
      Session::addMessageAfterRedirect(__('Logo uploaded successfully.', 'barcode'));
   } else {
      Session::addMessageAfterRedirect(
         __('Invalid image file. Only PNG, JPG, and GIF are accepted.', 'barcode'),
         false,
         ERROR
      );
   }

// --- Actualizar tipo de código predeterminado ---
} elseif (isset($_POST['type'])) {
   $pbconf = new PluginBarcodeConfig();
   $pbconf->update(['id' => 1, 'type' => $_POST['type']]);
}

Html::back();
