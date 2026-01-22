<?php
/*
   Generate QR by device type
*/

include('../../../inc/includes.php');
Session::checkLoginUser();
// Load plugin
Plugin::load('barcode');

Html::header(__('Generate QR by type', 'barcode'), $_SERVER['PHP_SELF'], "plugins", "barcode");

// Allowed types and DB tables mapping
$types = [
   'Computer' => 'glpi_computers',
   'Monitor'  => 'glpi_monitors',
   'Phone'    => 'glpi_phones',
];

if ((!empty($_REQUEST['itemtype']) && array_key_exists($_REQUEST['itemtype'], $types))) {
   global $DB;
   // allow GET or POST to avoid CSRF token issues when opened directly
   $itemtype = $_REQUEST['itemtype'];
   $table = $types[$itemtype];

   // Get active items (not deleted) or single id
   $ids = [];
   if (!empty($_REQUEST['singleid'])) {
      $single = intval($_REQUEST['singleid']);
      $ids[] = $single;
   } else {
      $res = $DB->request("SELECT id FROM `".$table."` WHERE is_deleted = 0");
      foreach ($res as $r) {
         $ids[] = $r['id'];
      }
   }

   if (count($ids) === 0) {
      Html::notification(__('No active items found for selected type.'), 'nf_warning');
      echo "<p><a href='".Plugin::getWebDir('barcode')."/front/generate_by_type.php'>".__('Back', 'barcode')."</a></p>";
      Html::footer();
      exit;
   }

   // Generate QR images
   $rand = mt_rand();
   $number = 0;
   $codes = [];
   $displayDataCollection = [];
   $pbQRcode = new PluginBarcodeQRcode();
   foreach ($ids as $id) {
      // Include the item NAME in both QR content and printed label
      $values = $pbQRcode->generateQRcode($itemtype, $id, $rand, $number, [
         'serialnumber' => 0,
         'inventorynumber' => 1,
         'id' => 0,
         'uuid' => 0,
         'name' => 1,
         'displayname' => 1,
         // ensure the QR encodes a URL that uses the inventory number lookup
         'inventorynumberURL' => 1,
         'url' => 1,
         'displayurl' => 0,
         'displaylabels' => 0,
         'displayinventorynumber' => 1
      ]);
      if ($values) {
         $codes[] = $values[0];
         $displayDataCollection[] = $values[1];
         $number++;
      }
   }

   if (count($codes) > 0) {
   $params = [];
   $params['codes'] = $codes;
   $params['displayData'] = $displayDataCollection;
   $params['type'] = 'QRcode';
   // Use LETTER (US Letter) for full-page labels; if only one QR is generated
   // we want it large and centered on a Letter page.
   $params['size'] = 'LETTER';
   $params['orientation'] = 'Portrait';
   // By default, enable border when generating a single full-page QR so
   // printed labels have a visible frame.
   // Enable border by default for generated labels. Allow override via ?border=0
   $params['border'] = isset($_REQUEST['border']) ? intval($_REQUEST['border']) : 1;
   $params['displaylabels'] = 0;
   // If we're generating a single QR, request full-page sizing in printPDF
   $params['fullpage'] = (count($codes) == 1) ? 1 : 0;

      $barcode = new PluginBarcodeBarcode();
      $file = $barcode->printPDF($params);
      $filePath = explode('/', $file);
      $filename = $filePath[count($filePath)-1];

   // Provide a visible link that the user can open in a new tab (avoids popup blockers)
   $pdfUrl = Plugin::getWebDir('barcode')."/front/send.php?file=".urlencode($filename);
   echo "<div class='center'><p>".__('Generated file', 'barcode')."</p>";
   echo "<p><a href='".$pdfUrl."' target='_blank' rel='noopener noreferrer' class='glpi_button'>".__('Open generated PDF', 'barcode')."</a></p>";
   echo "<p>".__('If your browser blocked automatic opening, click the link above to view the PDF.','barcode')."</p></div>";

   // clean tmp files
   $pbQRcode->cleanQRcodefiles($rand, $number);

   Html::footer();
   exit;
   } else {
      Html::notification(__('No QR generated.'), 'nf_warning');
   }
}

// Show form
echo "<form method=\"get\" action=\"".$_SERVER['PHP_SELF']."\">";
echo "<table class='tab_cadre'>";
echo "<tr><th colspan=2>" . __('Generate QR for all active devices', 'barcode') . "</th></tr>";
echo "<tr><td>" . __('Device type', 'barcode') . ":</td><td>";
echo "<select name='itemtype'>";
foreach ($types as $k => $v) {
   echo "<option value='".htmlspecialchars($k, ENT_QUOTES).'">'.htmlspecialchars($k)."</option>";
}
echo "</select>";
echo "</td></tr>";
echo "<tr><td colspan=2 align='center'><input type='submit' value='".__('Generate', 'barcode')."' class='submit'></td></tr>";
echo "</table>";
echo "</form>";

Html::footer();

?>