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

namespace Krabo\IsotopeStockBundle\EventListener;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\Config;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection\Order;
use Krabo\IsotopeStockBundle\Helper\BookingHelper;
use Krabo\IsotopeStockBundle\Helper\ProductHelper;
use Krabo\IsotopeStockBundle\Model\AccountModel;
use Krabo\IsotopeStockBundle\Model\BookingLineModel;
use Krabo\IsotopeStockBundle\Model\BookingModel;
use Krabo\IsotopeStockBundle\Model\PeriodModel;

class ProductCollectionListener {

  /**
   * Check quantity and stock and if not enough quanity show a message.
   *
   * @param \Isotope\Interfaces\IsotopeProduct $objProduct
   * @param $intQuantity
   * @param \Isotope\Model\ProductCollection $collection
   * @param $arrConfig
   *
   * @return mixed
   */
  public function addProductToCollection(IsotopeProduct $objProduct, $intQuantity, \Isotope\Model\ProductCollection $collection, $arrConfig) {
    $stockAccountType = ProductHelper::getProductStockPerAccountType($objProduct->getId());
    if ($objProduct->isostock_preorder) {
      if (isset($stockAccountType[AccountModel::PRE_ORDER_TYPE]) && isset($stockAccountType[AccountModel::PRE_ORDER_TYPE]['balance']) && $stockAccountType[AccountModel::PRE_ORDER_TYPE]['balance'] >= $intQuantity) {
        return $intQuantity;
      }
      Message::addInfo(sprintf($GLOBALS['TL_LANG']['IsotopeStockProductInfoNotAvailableForPreOrder'], $objProduct->getName()));
      return 0;
    } elseif (isset($stockAccountType[AccountModel::STOCK_TYPE]) && isset($stockAccountType[AccountModel::STOCK_TYPE]['balance']) && $stockAccountType[AccountModel::STOCK_TYPE]['balance'] >= $intQuantity) {
      return $intQuantity;
    }
    Message::addInfo(sprintf($GLOBALS['TL_LANG']['IsotopeStockProductInfoNotInStock'], $objProduct->getName()));
    return $intQuantity;
  }

  /**
   * Add a booking as soon as an order is paid
   *
   * @param \Isotope\Model\ProductCollection\Order $order
   * @param $intOldStatus
   * @param \Isotope\Model\OrderStatus $objNewStatus
   *
   * @return void
   */
  public function postOrderStatusUpdate(Order $order, $intOldStatus, OrderStatus $objNewStatus) {
    if ($order->isLocked() && $order->isCheckoutComplete()) {
      $config = Config::findByPk($order->config_id);
      foreach($order->getItems() as $item) {
        if ($item->getProduct()->isostock_preorder) {
          BookingModel::createBookingFromOrderAndProduct($order, $item, $config->isotopestock_order_debit_account, $config->isotopestock_preorder_credit_account, BookingModel::SALES_TYPE);
        } else {
          BookingModel::createBookingFromOrderAndProduct($order, $item, $config->isotopestock_order_debit_account, $config->isotopestock_order_credit_account, BookingModel::SALES_TYPE);
        }
      }
    }
    if ($objNewStatus->isotopestock_process_delivery_booking && $order->isLocked() && $order->isCheckoutComplete()) {
      $config = Config::findByPk($order->config_id);
      foreach($order->getItems() as $item) {
        BookingModel::createBookingFromOrderAndProduct($order, $item, $config->isotopestock_order_credit_account, $config->isotopestock_store_account, BookingModel::DELIVERY_TYPE);
      }
    }
  }

}