<?php
/**
 * Copyright (C) 2024  Jaap Jansma (jaap.jansma@civicoop.org)
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

namespace Krabo\IsotopeStockBundle\Cron;

use Contao\CoreBundle\ServiceAnnotation\CronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\System;
use Isotope\Model\Product;
use Krabo\IsotopeStockBundle\Event\Events;
use Krabo\IsotopeStockBundle\Event\ManualBookingEvent;
use Krabo\IsotopeStockBundle\Helper\BookingHelper;
use Krabo\IsotopeStockBundle\Model\BookingModel;

/**
 * @CronJob("minutely")
 */
class CheckBookingEvents
{

    /**
     * @param \Contao\CoreBundle\Framework\ContaoFramework $contaoFramework
     */
    public function __construct(ContaoFramework $contaoFramework)
    {
        $contaoFramework->initialize();
    }

    public function __invoke(): void
    {
        /** @var Database $db */
        $db = System::importStatic('Database');
        $db->execute("DELETE FROM `tl_isotope_stock_booking_event` WHERE booking_id NOT IN (SELECT id FROM `tl_isotope_stock_booking`); ");
        $objResult = $db->execute("SELECT * FROM `tl_isotope_stock_booking_event` LIMIT 0, 1");
        $ids = [];
        while($objResult->next()) {
            $ids[] = $objResult->id;
            $booking = BookingModel::findByPk($objResult->booking_id);
            if ($booking) {
              BookingHelper::updateBalanceStatusForBooking($booking->id);
              $event = new ManualBookingEvent($booking);
              System::getContainer()
                ->get('event_dispatcher')
                ->dispatch($event, Events::MANUAL_BOOKING_EVENT);
            }
        }
        if (count($ids)) {
            $sql = "DELETE FROM `tl_isotope_stock_booking_event` WHERE `id` IN (" . implode(", ", $ids) . ")";
            $db->execute($sql);
            BookingHelper::updateBalanceStatusForModifiedBookings();
        }
    }
}