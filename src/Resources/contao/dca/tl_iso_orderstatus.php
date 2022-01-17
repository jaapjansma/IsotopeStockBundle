<?php
/**
 * Copyright (C) 2022  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_iso_orderstatus']['fields']['isotopestock_process_delivery_booking'] = [
  'exclude'               => true,
  'inputType'             => 'checkbox',
  'eval'                  => array('tl_class'=>'w50'),
  'sql'                   => "char(1) NOT NULL default ''"
];

PaletteManipulator::create()
  ->addField('isotopestock_process_delivery_booking', 'paid', PaletteManipulator::POSITION_AFTER)
  ->applyToPalette('default', 'tl_iso_orderstatus');