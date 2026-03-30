<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI
   Migrado a GLPI 11.x por Dawing S.A.S.
   Original: Copyright (C) 2009-2016 by the Barcode plugin Development Team.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

class PluginBarcodeProfile extends Profile {

   static $rightname = "config";

   /**
    * Nombre de la pestaña que se agrega al formulario de Perfil
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getID() > 0
              && $item->fields['interface'] == 'central') {
         return self::createTabEntry(__('Barcode', 'barcode'));
      }
      return '';
   }

   /**
    * Contenido de la pestaña del perfil
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $pfProfile = new self();
      $pfProfile->showForm($item->getID());
      return true;
   }

   /**
    * Formulario de derechos del plugin en el perfil
    *
    * @param  int   $ID      ID del perfil
    * @param  array $options Opciones adicionales
    */
   function showForm($ID, array $options = []) {

      echo "<div class='firstbloc'>";
      $canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]);

      if ($canedit) {
         $profile = new Profile();
         echo "<form method='post' action='" . $profile->getFormURL() . "'>";
      }

      $profile = new Profile();
      $profile->getFromDB($ID);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, [
         'canedit'       => $canedit,
         'default_class' => 'tab_bg_2',
         'title'         => __('Barcode', 'barcode'),
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
    * Elimina los derechos del plugin (para uninstall)
    */
   static function uninstallProfile() {
      $pfProfile  = new self();
      $a_rights   = $pfProfile->getAllRights();
      foreach ($a_rights as $data) {
         ProfileRight::deleteProfileRights([$data['field']]);
      }
   }

   /**
    * Lista de todos los derechos del plugin
    *
    * @return array
    */
   function getAllRights() {
      return [
         [
            'rights' => [UPDATE => __('Update')],
            'label'  => __('Manage configuration', 'barcode'),
            'field'  => 'plugin_barcode_config',
         ],
         [
            'rights' => [CREATE => __('Create')],
            'label'  => __('Generation of barcode', 'barcode'),
            'field'  => 'plugin_barcode_barcode',
         ],
      ];
   }

   /**
    * Agrega derechos por defecto a un perfil
    *
    * @param  int   $profiles_id  ID del perfil
    * @param  array $rights       Mapa de derechos a agregar
    */
   static function addDefaultProfileInfos($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   ['profiles_id' => $profiles_id, 'name' => $right])) {
            $myright = [
               'profiles_id' => $profiles_id,
               'name'        => $right,
               'rights'      => $value,
            ];
            $profileRight->add($myright);
            // Actualizar sesión activa
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   /**
    * Crea accesos completos para el primer perfil administrador
    *
    * @param  int $profiles_id  ID del perfil
    */
   static function createFirstAccess($profiles_id) {
      include_once(Plugin::getPhpDir('barcode') . "/inc/profile.class.php");
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         self::addDefaultProfileInfos($profiles_id, [$right['field'] => ALLSTANDARDRIGHT]);
      }
   }

   /**
    * Elimina los derechos del plugin de todos los perfiles y de la sesión
    */
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
    * Inicializa los perfiles del plugin durante la instalación
    * - Registra los derechos en glpi_profilerights
    * - El perfil activo del usuario instalador recibe todos los derechos
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

      // Asignar todos los derechos al perfil activo del usuario instalador
      if (isset($_SESSION['glpiactiveprofile'])) {
         $dataprofile       = [];
         $dataprofile['id'] = $_SESSION['glpiactiveprofile']['id'];
         $profile->getFromDB($_SESSION['glpiactiveprofile']['id']);

         foreach ($a_rights as $info) {
            if (is_array($info)
                && (!empty($info['itemtype']) || !empty($info['rights']))
                && !empty($info['label'])
                && !empty($info['field'])) {

               $rights = isset($info['rights'])
                  ? $info['rights']
                  : $profile->getRightsFor($info['itemtype']);

               foreach (array_keys($rights) as $right) {
                  $dataprofile['_' . $info['field']][$right] = 1;
                  $_SESSION['glpiactiveprofile'][$info['field']] = $right;
               }
            }
         }
         $profile->update($dataprofile);
      }
   }

   /**
    * Limpia los perfiles al purgar un perfil GLPI
    * (Callback del hook pre_item_purge)
    *
    * @param  Profile $item  Objeto Profile que se está purgando
    */
   static function cleanProfiles(Profile $item) {
      // No hay tabla local de perfiles en esta versión
      // Los derechos están en glpi_profilerights manejados por GLPI core
   }
}
