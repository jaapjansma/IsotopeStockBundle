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

use \Krabo\IsotopeStockBundle\EventListener\ProductCollectionListener;

if (!isset($GLOBALS['BE_MOD']['isotope_stock']) || !\is_array($GLOBALS['BE_MOD']['isotope_stock']))
{
  array_insert($GLOBALS['BE_MOD'], 2, array('isotope_stock' => array()));
}

array_insert($GLOBALS['BE_MOD']['isotope_stock'], 0, array
(
  'tl_isotope_stock_booking' => array
  (
    'tables'            => array('tl_isotope_stock_booking', 'tl_isotope_stock_booking_line'),
  ),
  'tl_isotope_stock_period' => array
  (
    'tables'            => array('tl_isotope_stock_period'),
  ),
  'tl_isotope_stock_account' => array
  (
    'tables'            => array('tl_isotope_stock_account'),
  ),
));

$GLOBALS['ISO_HOOKS']['addProductToCollection'][] = [ProductCollectionListener::class, 'addProductToCollection'];
$GLOBALS['ISO_HOOKS']['updateItemInCollection'][] = [ProductCollectionListener::class, 'updateItemInCollection'];
$GLOBALS['ISO_HOOKS']['itemIsAvailable'][] = [ProductCollectionListener::class, 'itemIsAvailable'];
$GLOBALS['ISO_HOOKS']['postOrderStatusUpdate'][] = [ProductCollectionListener::class, 'postOrderStatusUpdate'];

$GLOBALS['TL_MODELS']['tl_isotope_stock_account'] = \Krabo\IsotopeStockBundle\Model\AccountModel::class;
$GLOBALS['TL_MODELS']['tl_isotope_stock_period'] = \Krabo\IsotopeStockBundle\Model\PeriodModel::class;
$GLOBALS['TL_MODELS']['tl_isotope_stock_booking'] = \Krabo\IsotopeStockBundle\Model\BookingModel::class;
$GLOBALS['TL_MODELS']['tl_isotope_stock_booking_line'] = \Krabo\IsotopeStockBundle\Model\BookingLineModel::class;

