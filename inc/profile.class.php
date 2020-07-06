<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2016 by the Barcode plugin Development Team.

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
   @copyright Copyright (c) 2009-2016 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/barscode
   @since     2009

   ------------------------------------------------------------------------
 */

class PluginBarcodeProfile extends Profile {

   static $rightname = "config";


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getID() > 0
              && $item->fields['interface'] == 'central') {
         return self::createTabEntry(__('Barcode', 'barcode'));
      }
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $pfProfile = new self();
      $pfProfile->showForm($item->getID());
      return true;
   }



    /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id = 0, $openform = true, $closeform = true) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                    'default_class' => 'tab_bg_2',
                                                    'title'         => __('Barcode', 'barcode')
                                                   ]);
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }



   static function uninstallProfile() {
      $pfProfile = new self();
      $a_rights = $pfProfile->getAllRights();
      foreach ($a_rights as $data) {
         ProfileRight::deleteProfileRights([$data['field']]);
      }
   }



   function getAllRights() {
      $a_rights = [
          ['rights'    => [UPDATE  => __('Update')],
                'label'     => __('Manage configuration', 'barcode'),
                'field'     => 'plugin_barcode_config'
          ],
          ['rights'    => [CREATE  => __('Create')],
                'label'     => __('Generation of barcode', 'barcode'),
                'field'     => 'plugin_barcode_barcode'
          ]
      ];
      return $a_rights;
   }



   static function addDefaultProfileInfos($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   ['profiles_id' => $profiles_id, 'name' => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   /**
    * @param $profiles_id  integer
    */
   static function createFirstAccess($profiles_id) {
      include_once(Plugin::getPhpDir('barcode')."/inc/profile.class.php");
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         self::addDefaultProfileInfos($profiles_id,
                                      [$right['field'] => ALLSTANDARDRIGHT]);
      }
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

   static function migrateProfiles() {
      //Get all rights from the old table
      $profiles = getAllDataFromTable(getTableForItemType(__CLASS__));

      //Load mapping of old rights to their new equivalent
      $oldrights = self::getOldRightsMappings();

      //For each old profile : translate old right the new one
      foreach ($profiles as $id => $profile) {
         switch ($profile['right']) {
            case 'r' :
               $value = READ;
               break;
            case 'w':
               $value = ALLSTANDARDRIGHT;
               break;
            case 0:
            default:
               $value = 0;
               break;
         }
         //Write in glpi_profilerights the new fusioninventory right
         if (isset($oldrights[$profile['type']])) {
            //There's one new right corresponding to the old one
            if (!is_array($oldrights[$profile['type']])) {
               self::addDefaultProfileInfos($profile['profiles_id'],
                                            [$oldrights[$profile['type']] => $value]);
            } else {
               //One old right has been splitted into serveral new ones
               foreach ($oldrights[$profile['type']] as $newtype) {
                  self::addDefaultProfileInfos($profile['profiles_id'],
                                               [$newtype => $value]);
               }
            }
         }
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
                  $_SESSION['glpiactiveprofile'][$data['field']] = $right;
               }
            }
         }
         $profile->update($dataprofile);
      }
   }
}
