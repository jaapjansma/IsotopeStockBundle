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

use \Krabo\IsotopeStockBundle\EventListener\ProductListener;

$GLOBALS['TL_DCA']['tl_iso_product']['config']['onsubmit_callback'][] = [ProductListener::class, 'onSubmitCallback'];
$GLOBALS['TL_DCA']['tl_iso_product']['edit']['buttons_callback'][] = [ProductListener::class, 'editButtonCallback'];

$GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['stock_report'] = array
(
  'label'               =>  &$GLOBALS['TL_LANG']['tl_iso_product']['stock_report'],
  'route'               => 'tl_isotope_stock_booking_overview',
  'class'               => 'stock_report',
  'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"',
  'icon'                => 'tablewizard.svg',
);

$GLOBALS['TL_DCA']['tl_iso_product']['list']['global_operations']['stock_report_all'] = array
(
  'label'               =>  &$GLOBALS['TL_LANG']['tl_iso_product']['stock_report_all'],
  'route'               => 'tl_isotope_stock_booking_overview_all',
  'class'               => 'stock_report',
  'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"',
  'icon'                => 'tablewizard.svg',
);

$GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['stock'] = [
  'label'             => &$GLOBALS['TL_LANG']['tl_iso_product']['stock'],
  //'icon'              => 'rows.svg',
  'icon'              => '@IsotopeStock/Resources/public/stock_ok.png',
  'route'             => 'tl_isotope_stock_booking_product_info',
  'button_callback'  => [ProductListener::class, 'stockButtonCallback']
];

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['isostock_preorder'] = [
  'filter'                => true,
  'inputType'             => 'checkbox',
  'eval'                  => array('tl_class'=>'w50', 'submitOnChange' => true),
  'attributes'            => array( 'legend'=>'isostock_legend' ),
  'sql'                   => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['isostock_minimun_stock'] = [
  'inputType'               => 'text',
  'eval'                    => array('doNotCopy'=>true, 'rgxp' => 'natural', 'tl_class' => 'w50' ),
  'attributes'            => array( 'legend'=>'isostock_legend' ),
  'sql'                     => "int(10) unsigned NOT NULL default 0",
  'flag'                    => 11,
  'default'                 => '0',
  'filter'                => true,
];
