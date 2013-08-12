<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2013 by the Barcode plugin Development Team.

   https://forge.indepnet.net/projects/barscode
   ------------------------------------------------------------------------

   LICENSE

   This file is part of barcode plugin project.

   Plugin Barcode is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Barcode is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Plugin Barcode. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Barcode
   @author    David Durieux
   @co-author
   @copyright Copyright (c) 2009-2013 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/barscode
   @since     2009

   ------------------------------------------------------------------------
 */

class PluginBarcodeProfile extends CommonDBTM {

   static function canView() {
      return Session::haveRight('profile','r');
   }

   
   
   static function canCreate() {
      return Session::haveRight('profile','w');
   }
   
   

   function getSearchOptions() {

      $tab = array();

      $tab['common'] = __('Characteristics');

      $tab['table']     = $this->getTable();
      $tab['field']     = 'use';
      $tab['linkfield'] = 'id';
      $tab['datatype']  = 'bool';

      return $tab;
   }


   function createProfile($profile) {

      return $this->add(array('id'      => $profile->getField('id'),
                              'profile' => $profile->getField('name')));
   }


   //if profile deleted
   static function cleanProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->delete(array('id'=>$prof->getField("id")));
   }


   function showForm($ID, $options=array()) {
      global $DB;

      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit = $this->can($ID,'w');

      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2' class='center b'>".
            __('Barcode', 'barcode'). " - " .$this->fields["profile"]."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Generate barcode pdf', 'barcode')."&nbsp;:</td><td>";
      Dropdown::showYesNo("generate",(isset($this->fields["generate"])?$this->fields["generate"]:''));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Configuration')."&nbsp;:</td><td>";
      Dropdown::showYesNo("config",(isset($this->fields["config"])?$this->fields["config"]:''));
      echo "</td></tr>\n";

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2' class='center'>";
         echo "<input type='hidden' name='id' value=$ID>";
         echo "<input type='submit' name='update_user_profile' value='".__('Save').
               "' class='submit'>&nbsp;";
         echo "</td></tr>\n";
      }
      echo "</table>";
      Html::closeForm();
   }


   static function changeprofile() {

      $tmp = new self();
       if ($tmp->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
          $_SESSION["glpi_plugin_barcode_profile"] = $tmp->fields;
       } else {
          unset($_SESSION["glpi_plugin_barcode_profile"]);
       }
   }
}
?>