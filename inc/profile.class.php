<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2026 by the Barcode plugin Development Team.

   https://github.com/ISPF/info-glpi-barcode-ispf
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
   @copyright Copyright (c) 2009-2026 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/ISPF/info-glpi-barcode-ispf
   @since     2009

   ------------------------------------------------------------------------
 */

class PluginBarcodeProfile extends Profile {

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item instanceof Profile && $item->getID() > 0) {
         return self::createTabEntry(__('Barcode', 'barcode'));
      }
      return '';
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $pfProfile = new self();
      $pfProfile->showForm($item->getID());
      return true;
   }



   /**
    * Show the plugin rights of a profile
    *
    * @param int   $ID      id of the profile
    * @param array $options not used
    *
    * @return void
    **/
   function showForm($ID, array $options = []) {

      $profile = new Profile();
      if (!$profile->getFromDB($ID)) {
         return;
      }

      // The form is saved by front/profile.form.php which requires the profile right
      $canedit = Session::haveRight('profile', UPDATE);

      echo "<div class='firstbloc'>";
      if ($canedit) {
         echo "<form method='post' action='" . htmlescape($profile->getFormURL()) . "'>";
      }

      $rights = $this->getAllRights($profile->fields['interface'] === 'helpdesk');
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                    'default_class' => 'tab_bg_2',
                                                    'title'         => __('Barcode', 'barcode')
                                                   ]);
      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $ID]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }



   /**
    * Rights managed by the plugin
    *
    * @param bool $helpdesk only rights available in the simplified interface
    *
    * @return array
    */
   function getAllRights(bool $helpdesk = false) {
      $a_rights = [
          ['rights'    => [CREATE  => __('Create')],
                'label'     => __('Generation of barcode', 'barcode'),
                'field'     => 'plugin_barcode_barcode'
          ],
      ];
      if (!$helpdesk) {
         $a_rights[] = ['rights'    => [UPDATE  => __('Update')],
                'label'     => __('Manage configuration', 'barcode'),
                'field'     => 'plugin_barcode_config'
          ];
      }
      return $a_rights;
   }



   static function removeRights() {
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
         ProfileRight::deleteProfileRights([$right['field']]);
      }
   }



   /**
   * Init profiles during installation :
   * - add rights in profile table for the current user's profile
   * - current profile has all rights on the plugin
   */
   static function initProfile() {
      $pfProfile = new self();
      $profile   = new Profile();
      $a_rights  = $pfProfile->getAllRights();

      foreach ($a_rights as $data) {
         if (!countElementsInTable("glpi_profilerights", ['name' => $data['field']])) {
            ProfileRight::addProfileRights([$data['field']]);
            $_SESSION['glpiactiveprofile'][$data['field']] = 0;
         }
      }

      // Add all rights to current profile of the user
      if (isset($_SESSION['glpiactiveprofile'])) {
         $dataprofile       = [];
         $dataprofile['id'] = $_SESSION['glpiactiveprofile']['id'];
         $profile->getFromDB($_SESSION['glpiactiveprofile']['id']);
         foreach ($a_rights as $info) {
            if (is_array($info)
                && ((!empty($info['itemtype'])) || (!empty($info['rights'])))
                  && (!empty($info['label'])) && (!empty($info['field']))) {

               if (isset($info['rights'])) {
                  $rights = $info['rights'];
               } else {
                  $rights = $profile->getRightsFor($info['itemtype']);
               }
               foreach (array_keys($rights) as $right) {
                  $dataprofile['_'.$info['field']][$right] = 1;
                  $_SESSION['glpiactiveprofile'][$info['field']] = $right;
               }
            }
         }
         $profile->update($dataprofile);
      }
   }
}
