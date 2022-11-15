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

namespace Krabo\IsotopeStockBundle\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\CronJob;
use Krabo\IsotopePackagingSlipBundle\Helper\PackagingSlipCheckAvailability;

/**
 * @CronJob("hourly")
 */
class UpdateTstamp {

  /**
   * @param \Contao\CoreBundle\Framework\ContaoFramework $contaoFramework
   */
  public function __construct(ContaoFramework $contaoFramework) {
    $contaoFramework->initialize();
  }

  public function __invoke(): void
  {
    $db = \Contao\Database::getInstance();
    $db->prepare("UPDATE `tl_isotope_stock_booking` SET `tstamp` = `date` WHERE `tstamp` = 0")->execute();
  }

}