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

namespace Krabo\IsotopeStockBundle\Helper;

use Contao\Image;
use Contao\StringUtil;
use Isotope\Model\Product;
use Krabo\IsotopeStockBundle\Model\AccountModel;

class ProductHelper {

  /**
   * @var array
   */
  private static $productAccounts = [];

  /**
   * @var array
   */
  private static $productAccountTypes = [];

  /**
   * Returns information of how many products are booked on a certain account.
   *
   * Returns the account id, account title, account type, debit, credit and balance
   *
   * @param int $product_id
   * @return array
   */
  public static function getProductStockPerAccount(int $product_id) {
    self::loadStockInfoForProducts([$product_id]);
    return self::$productAccounts[$product_id];
  }

  /**
   * Returns the number of products available at this account.
   *
   * @param int $product_id
   * @param int $account_id
   *
   * @return int
   */
  public static function getProductCountPerAccount(int $product_id, int $account_id): int {
    self::loadStockInfoForProducts([$product_id]);
    if (isset(self::$productAccounts[$product_id][$account_id])) {
      return self::$productAccounts[$product_id][$account_id]['balance'];
    }
    return 0;
  }

  /**
   * Check whether a product is available to order
   *
   * @param int $product_id
   * @param int $quantity
   *
   * @return bool
   */
  public static function isProductAvailableToOrder(int $product_id, int $quantity=1): bool {
    $objProduct = Product::findByPk($product_id);
    $stockAccountType = ProductHelper::getProductStockPerAccountType($product_id);
    if ($objProduct->isostock_preorder) {
      if (isset($stockAccountType[AccountModel::PRE_ORDER_TYPE]) && isset($stockAccountType[AccountModel::PRE_ORDER_TYPE]['balance']) && $stockAccountType[AccountModel::PRE_ORDER_TYPE]['balance'] >= $quantity) {
        return true; // Product is available for pre-order.
      }
    } elseif (isset($stockAccountType[AccountModel::STOCK_TYPE]) && isset($stockAccountType[AccountModel::STOCK_TYPE]['balance']) && $stockAccountType[AccountModel::STOCK_TYPE]['balance'] >= $quantity) {
      return true; // Product is available for ordering
    }
    return false; // Product is not available for ordering.
  }

  /**
   * Returns information on how many products are booked on a certain account type.
   * Excludes the other account type.
   *
   * @param int $product_id
   * @return array
   */
  public static function getProductStockPerAccountType(int $product_id) {
    self::loadStockInfoForProducts([$product_id]);
    return self::$productAccountTypes[$product_id];
  }

  /**
   * Load information about the stock for a certain product.
   *
   * @param array $product_ids
   * @return void
   */
  public static function loadStockInfoForProducts(array $product_ids) {
    $pids = [];
    foreach($product_ids as $product_id) {
      if (isset(self::$productAccounts[$product_id])) {
        continue;
      }
      $pids[] = $product_id;
    }

    if (count($pids)) {
      \Contao\System::loadLanguageFile('tl_isotope_stock_account');
      $db = \Database::getInstance();
      $accountQueryResult = $db->prepare("SELECT * FROM `tl_isotope_stock_account` ORDER BY `type`, `title`")->execute();
      $accounts = [];
      $accountTypeBalance = [];
      while($accountQueryResult->next()) {
        $account_id = $accountQueryResult->id;
        $accounts[$account_id] = $accountQueryResult->row();
        $accounts[$account_id]['title'] = html_entity_decode($accounts[$account_id]['title']);
        $accounts[$account_id]['type_label'] = $GLOBALS['TL_LANG']['tl_isotope_stock_account']['type_options'][$accountQueryResult->type];
        $accounts[$account_id]['debit'] = 0;
        $accounts[$account_id]['credit'] = 0;
        $accounts[$account_id]['balance'] = 0;
        if ($accountQueryResult->type && !isset($accountTypeBalance[$accountQueryResult->type])) {
          $accountTypeBalance[$accountQueryResult->type]['balance'] = 0;
          $accountTypeBalance[$accountQueryResult->type]['label'] = $GLOBALS['TL_LANG']['tl_isotope_stock_account']['type_options'][$accountQueryResult->type];
        }
      }

      $productInfoQuery = "
      SELECT
        SUM(`tl_isotope_stock_booking_line`.`debit`) AS `debit`,
        SUM(`tl_isotope_stock_booking_line`.`credit`) AS `credit`,
        (SUM(`tl_isotope_stock_booking_line`.`debit`) - SUM(`tl_isotope_stock_booking_line`.`credit`)) AS `balance`,
         `tl_isotope_stock_booking_line`.`account`,
         `tl_isotope_stock_account`.`type`,
        `tl_isotope_stock_booking`.`product_id`
      FROM `tl_isotope_stock_booking_line`
      LEFT JOIN `tl_isotope_stock_booking` ON `tl_isotope_stock_booking`.`id` = `tl_isotope_stock_booking_line`.`pid`
      LEFT JOIN `tl_isotope_stock_period` ON `tl_isotope_stock_period`.`id` = `tl_isotope_stock_booking`.`period_id` AND `tl_isotope_stock_period`.`active` = '1'
      LEFT JOIN `tl_isotope_stock_account` ON `tl_isotope_stock_account`.`id` = `tl_isotope_stock_booking_line`.`account`
      WHERE `tl_isotope_stock_booking`.`product_id` IN(".implode(",", $pids).") OR `tl_isotope_stock_booking`.`product_id` IS NULL 
      GROUP BY `tl_isotope_stock_booking_line`.`account`, `tl_isotope_stock_booking`.`product_id`
      ORDER BY `tl_isotope_stock_booking`.`product_id`
    ";
      $productInfoQueryResult = $db->prepare($productInfoQuery)->execute();

      $productAccounts = [];
      $productAccountTypeBalance = [];
      while ($productInfoQueryResult->next()) {
        if (!isset($productAccounts[$productInfoQueryResult->product_id])) {
          $productAccounts[$productInfoQueryResult->product_id] = $accounts;
        }
        if (!isset($productAccountTypeBalance[$productInfoQueryResult->product_id])) {
          $productAccountTypeBalance[$productInfoQueryResult->product_id] = $accountTypeBalance;
        }
        $productAccounts[$productInfoQueryResult->product_id][$productInfoQueryResult->account]['debit'] = $productInfoQueryResult->debit;
        $productAccounts[$productInfoQueryResult->product_id][$productInfoQueryResult->account]['credit'] = $productInfoQueryResult->credit;
        $productAccounts[$productInfoQueryResult->product_id][$productInfoQueryResult->account]['balance'] = $productInfoQueryResult->balance;
        $productAccounts[$productInfoQueryResult->product_id][$productInfoQueryResult->account]['type'] = $productInfoQueryResult->type;
        $accountType = $accounts[$productInfoQueryResult->account]['type'];
        if (!isset($productAccountTypeBalance[$productInfoQueryResult->product_id])) {
          $productAccountTypeBalance[$productInfoQueryResult->product_id] = [];
          if (isset($accountTypeBalance[$accountType])) {
            $productAccountTypeBalance[$productInfoQueryResult->product_id][$accountType] = $accountTypeBalance[$accountType];
          }
        }
        if (isset($productAccountTypeBalance[$productInfoQueryResult->product_id][$accountType])) {
          $productAccountTypeBalance[$productInfoQueryResult->product_id][$accountType]['balance'] += $productInfoQueryResult->balance;
        }
      }
      foreach($pids as $pid) {
        self::$productAccounts[$pid] = $productAccounts[$pid];
        self::$productAccountTypes[$pid] = $productAccountTypeBalance[$pid];
      }
    }
  }

  /**
   * Generates a button to view the stock
   *
   * @param $product_id
   * @return string
   */
  public static function genereateStockButtonLink($product_id) {
    \Contao\System::loadLanguageFile(\Isotope\Model\Product::getTable());
    $router = \Contao\System::getContainer()->get('router');
    $url = $router->generate('tl_isotope_stock_booking_product_info', ['id' => $product_id]);
    $icon = "bundles/isotopestock/out_of_stock.png";
    $objProduct = Product::findByPk($product_id);
    $stockPerAccountType = self::getProductStockPerAccountType($product_id);
    if ($objProduct->isostock_preorder) {
      $icon = "bundles/isotopestock/pre_order.png";
    } elseif (isset($stockPerAccountType[AccountModel::STOCK_TYPE]) && $stockPerAccountType[AccountModel::STOCK_TYPE]['balance'] > 0) {
      $icon = "bundles/isotopestock/stock_ok.png";
    }
    $title = $GLOBALS['TL_LANG']['tl_iso_product']['stock'];
    return '<a href="' . $url . '" title="' . StringUtil::specialchars($title) . '">' . Image::getHtml($icon, $title, 'style="width: 16px; height: 16px;"') . '</a> ';
  }

}