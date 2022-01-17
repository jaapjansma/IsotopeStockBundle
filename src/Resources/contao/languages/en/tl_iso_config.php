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

$GLOBALS['TL_LANG']['tl_iso_config']['isotopestock'] = 'Isotope Stock Configuration';

$GLOBALS['TL_LANG']['tl_iso_config']['isotopestock_order_debit_account'] = ['Order Debit Account', 'The debit account is used to book sales orders. For example: Sales'];
$GLOBALS['TL_LANG']['tl_iso_config']['isotopestock_order_credit_account'] = ['Order Credit Account', 'The credit account is used to book sales orders. When this account is of type stock the total stock is deducted with the quanity ordered. For example: Sales - to deliver'];
$GLOBALS['TL_LANG']['tl_iso_config']['isotopestock_preorder_credit_account'] = ['Pre-Order Credit Account', 'The credit account is used to book sales pre-orders. When this account is of type Pre-Order the total stock available for pre-order is deducted with the quantity ordered. For example: Pre-Order - to deliver'];
$GLOBALS['TL_LANG']['tl_iso_config']['isotopestock_store_account'] = ['Store Account', 'The store account is the store with actual products in the inventory'];
