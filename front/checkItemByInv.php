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
use Glpi\Exception\Http\BadRequestHttpException;
use Glpi\Exception\Http\NotFoundHttpException;


// Target page of the "URL by inventory number" QR codes: any logged-in user
// may scan a label, the item form itself checks the view right.
Session::checkLoginUser();

$itemtype        = $_GET['itemtype'] ?? '';
$inventoryNumber = $_GET['inventoryNumber'] ?? '';

if (!is_a($itemtype, CommonDBTM::class, true)) {
   throw new BadRequestHttpException(__('Invalid item type', 'barcode'));
}
if (!is_string($inventoryNumber) || $inventoryNumber === '') {
   throw new BadRequestHttpException(__('Missing inventory number', 'barcode'));
}

/** @var CommonDBTM $item */
$item = new $itemtype();
if (!$item->isField('otherserial')) {
   throw new BadRequestHttpException(__('Invalid item type', 'barcode'));
}

$found = $item->find(['otherserial' => $inventoryNumber], [], 2);
if (count($found) === 0) {
   throw new NotFoundHttpException(__('No item found', 'barcode'));
}
if (count($found) > 1) {
   throw new BadRequestHttpException(__('Several items share this inventory number', 'barcode'));
}

$id = (int) array_key_first($found);
Html::redirect($itemtype::getFormURLWithID($id));
