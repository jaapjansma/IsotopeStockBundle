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

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['isotopestock_order_debit_account'] = [
  'inputType'               => 'select',
  'eval'                    => array('doNotCopy'=>true),
  'foreignKey'              => 'tl_isotope_stock_account.title',
  'sql'                     => "int(10) unsigned NOT NULL default 0",
  'default'                 => '0',
];

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['isotopestock_order_credit_account'] = [
  'inputType'               => 'select',
  'eval'                    => array('doNotCopy'=>true),
  'foreignKey'              => 'tl_isotope_stock_account.title',
  'sql'                     => "int(10) unsigned NOT NULL default 0",
  'default'                 => '0',
];

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['isotopestock_preorder_credit_account'] = [
  'inputType'               => 'select',
  'eval'                    => array('doNotCopy'=>true),
  'foreignKey'              => 'tl_isotope_stock_account.title',
  'sql'                     => "int(10) unsigned NOT NULL default 0",
  'default'                 => '0',
];

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['isotopestock_store_account'] = [
  'inputType'               => 'select',
  'eval'                    => array('doNotCopy'=>true),
  'foreignKey'              => 'tl_isotope_stock_account.title',
  'sql'                     => "int(10) unsigned NOT NULL default 0",
  'default'                 => '0',
];

PaletteManipulator::create()
  ->addLegend('isotopestock', 'products_legend', PaletteManipulator::POSITION_BEFORE)
  ->addField('isotopestock_order_debit_account', 'isotopestock', PaletteManipulator::POSITION_APPEND)
  ->addField('isotopestock_order_credit_account', 'isotopestock', PaletteManipulator::POSITION_APPEND)
  ->addField('isotopestock_preorder_credit_account', 'isotopestock', PaletteManipulator::POSITION_APPEND)
  ->addField('isotopestock_store_account', 'isotopestock', PaletteManipulator::POSITION_APPEND)
  ->applyToPalette('default', 'tl_iso_config');