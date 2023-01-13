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

class BookingHelper {

  protected static $modifiedBookingIds = [];

  /**
   * Returns whether a booking is balance or not.
   *
   * @param $booking_id
   * @return bool
   */
  public static function isBookingInBalance(int $booking_id): bool {
    $query = "
    SELECT `is_in_balance`
    FROM `tl_isotope_stock_booking` 
    WHERE `id` = ?";

    try {
      return (bool) \Database::getInstance()
        ->prepare($query)
        ->execute($booking_id)
        ->first()->is_in_balance;
    } catch (\Exception $previousException) {
      throw new \Exception('Could not check whether booking is in balance because booking, with ID: '.$booking_id . ', is not found.', 0, $previousException);
    }
  }

  /**
   * Update the is in balance status for all bookings in an active period.
   *
   * @return void
   */
  public static function updateBalanceStatusForAllBookingsInActivePeriod() {
    $db = \Database::getInstance();

    $queryOutBalance = "
      UPDATE `tl_isotope_stock_booking`
      INNER JOIN `tl_isotope_stock_period` ON `tl_isotope_stock_booking`.`period_id` = `tl_isotope_stock_period`.`id` AND `tl_isotope_stock_period`.`active` = '1'
      SET `is_in_balance` = '0' 
    ";
    $db->prepare($queryOutBalance)->execute();

    $queryInBalance = "
      UPDATE `tl_isotope_stock_booking`
      INNER JOIN `tl_isotope_stock_period` ON `tl_isotope_stock_booking`.`period_id` = `tl_isotope_stock_period`.`id` AND `tl_isotope_stock_period`.`active` = '1'
      SET `is_in_balance` = '1' 
      WHERE `tl_isotope_stock_booking`.`id` IN ( 
        SELECT     
        `tl_isotope_stock_booking_line`.`pid` 
        FROM `tl_isotope_stock_booking_line`
        GROUP BY `pid`
        HAVING (SUM(`debit`) - SUM(`credit`)) = 0
    )";
    $db->prepare($queryInBalance)->execute();
  }

  /**
   * Update the is in balance status for a specific booking.
   *
   * @param int $booking_id
   * @return void
   */
  public static function updateBalanceStatusForBooking(int $booking_id) {
    self::$modifiedBookingIds[] = $booking_id;
  }

  public static function updateBalanceStatusForModifiedBookings() {
    if (count(self::$modifiedBookingIds)) {
      $db = \Contao\Database::getInstance();
      $queryOutBalance = "UPDATE `tl_isotope_stock_booking` SET `is_in_balance` = '0' WHERE `id` IN (".implode(",", self::$modifiedBookingIds).")";
      $db->prepare($queryOutBalance)->execute();

      $inBalanceResult = $db->prepare("
      SELECT 
          `tl_isotope_stock_booking`.`id` as `id`,
          (SUM(`debit`) - SUM(`credit`)) as balance
      FROM `tl_isotope_stock_booking`
      INNER JOIN `tl_isotope_stock_booking_line` ON `tl_isotope_stock_booking_line`.`pid` = `tl_isotope_stock_booking`.`id`
      WHERE `tl_isotope_stock_booking`.`id` IN (".implode(",", self::$modifiedBookingIds).")
      GROUP BY `tl_isotope_stock_booking`.`id`
      HAVING (SUM(`debit`) - SUM(`credit`)) = 0;
      ")->execute();

      $inBalanceBookingIds = [];
      while($inBalanceResult->next()) {
        $inBalanceBookingIds[] = $inBalanceResult->id;
      }

      $db->prepare("UPDATE `tl_isotope_stock_booking` SET `is_in_balance` = '1' WHERE `id` IN (".implode(",", $inBalanceBookingIds).") ")->execute();
    }
    self::$modifiedBookingIds = [];
  }

}