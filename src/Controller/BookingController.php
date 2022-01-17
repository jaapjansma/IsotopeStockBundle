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

namespace Krabo\IsotopeStockBundle\Controller;

use Krabo\IsotopeStockBundle\Helper\BookingHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{

  /**
   * @Route("/contao/tl_isotope_stock_booking/update_balance_status",
   *     name="tl_isotope_stock_booking_update_balance_status",
   *     defaults={"_scope": "backend"}
   * )
   */
  public function updateBalanceStatus(): RedirectResponse
  {
    BookingHelper::updateBalanceStatusForAllBookingsInActivePeriod();
    $url = $this->generateUrl('contao_backend', ['do' => 'tl_isotope_stock_booking']);
    return new RedirectResponse($url);
  }
}