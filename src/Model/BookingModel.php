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

namespace Krabo\IsotopeStockBundle\Model;

use Contao\Model;
use Contao\System;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Krabo\IsotopeStockBundle\Event\BookingEvent;
use Krabo\IsotopeStockBundle\Event\ClearBookingEvent;
use Krabo\IsotopeStockBundle\Event\Events;
use Krabo\IsotopeStockBundle\Helper\BookingHelper;

class BookingModel extends Model {

  protected static $strTable = 'tl_isotope_stock_booking';

  const OTHER_TYPE = 0;
  const SALES_TYPE = 1;
  const DELIVERY_TYPE = 2;

  /**
   * @param \Isotope\Model\ProductCollection\Order $objOrder
   * @param $product_id
   * @param int $type
   *
   * @return bool
   */
  public static function doesBookingExistsForOrderAndProduct(Order $objOrder, $product_id, int $bookingType) {
    return (bool) \Database::getInstance()
      ->prepare("SELECT * FROM `tl_isotope_stock_booking` WHERE `order_id` = ? AND `product_id` = ? AND `type` = ?")
      ->execute($objOrder->id, $product_id, $bookingType)
      ->count();
  }

  /**
   * Modify the current row before it is stored in the database
   *
   * @param array $arrSet The data array
   *
   * @return array The modified data array
   */
  protected function preSave(array $arrSet)
  {
    $arrSet['tstamp'] = time();
    return $arrSet;
  }

  /**
   * @param \Isotope\Model\ProductCollection\Order $objOrder
   * @param int $type
   *
   * @return bool
   */
  public static function deleteBookingOrderAndProduct(Order $objOrder, int $bookingType) {
    $bookings = BookingModel::findAll([
      'column' => ['order_id = ?', 'type = ?'],
      'value' => [$objOrder->id, $bookingType],
    ]);
    while($bookings->next()) {
      $bookings->delete();
    }

    $event = new ClearBookingEvent(null, $bookingType, $objOrder->id);
    System::getContainer()
      ->get('event_dispatcher')
      ->dispatch($event, Events::CLEAR_BOOKING_EVENT);
  }

  /**
   * Creates a new booking for a Product Collection Item
   *
   * @param \Isotope\Model\ProductCollection\Order $order
   * @param \Isotope\Model\ProductCollectionItem $item
   * @param int $debit_account_id
   * @param int $crebit_account_id
   * @param int $bookingType
   *
   * @return void
   */
  public static function createBookingFromOrderAndProduct(Order $order, ProductCollectionItem $item, int $debit_account_id, int $credit_account_id, int $bookingType) {
    if (!BookingModel::doesBookingExistsForOrderAndProduct($order,$item->getProduct()->getId(), $bookingType)) {
      $period = PeriodModel::getFirstActivePeriod();
      $booking = new BookingModel();
      $booking->description = $order->getDocumentNumber();
      $booking->date = time();
      $booking->period_id = $period->id;
      $booking->product_id = $item->getProduct()->getId();
      $booking->type = $bookingType;
      $booking->order_id = $order->id;
      $booking->save();
      $debitBookingLine = new BookingLineModel();
      $debitBookingLine->debit = $item->quantity;
      $debitBookingLine->account = $debit_account_id;
      $debitBookingLine->pid = $booking->id;
      $debitBookingLine->save();
      $creditBookingLine = new BookingLineModel();
      $creditBookingLine->credit = $item->quantity;
      $creditBookingLine->account = $credit_account_id;
      $creditBookingLine->pid = $booking->id;
      $creditBookingLine->save();
      BookingHelper::updateBalanceStatusForBooking($booking->id);

      $event = new BookingEvent($booking);
      System::getContainer()
        ->get('event_dispatcher')
        ->dispatch($event, Events::BOOKING_EVENT);
    }
  }

}