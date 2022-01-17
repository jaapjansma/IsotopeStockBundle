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

use Contao\DataContainer;
use Contao\Image;
use Contao\Message;
use Contao\StringUtil;
use Krabo\IsotopeStockBundle\Helper\ProductHelper;
use Krabo\IsotopeStockBundle\Model\AccountModel;
use Symfony\Component\Routing\RouterInterface;

class ProductListener {

  /**
   * @var \Symfony\Component\Routing\RouterInterface
   */
  private $router;

  public function __construct(RouterInterface $router)
  {
    $this->router = $router;
  }

  /**
   * Add a Save & View stock button to the Edit Product page.
   *
   * @param $arrButtons
   * @param \Contao\DataContainer $dc
   *
   * @return mixed
   */
  public function editButtonCallback($arrButtons, DataContainer $dc) {
    $arrButtons['saveAndViewStock'] = '<button type="submit" name="saveAndViewStock" id="saveAndViewStock" class="tl_submit">' . $GLOBALS['TL_LANG']['MSC']['saveAndViewStock'] . '</button>';
    return $arrButtons;
  }

  /**
   * Redirect to View Stock page.
   *
   * @param \Contao\DataContainer $dc
   * @return void
   */
  public function onSubmitCallback(DataContainer $dc) {
    // Redirect
    if (isset($_POST['saveAndViewStock']))
    {
      Message::reset();
      $url = $this->router->generate('tl_isotope_stock_booking_product_info', ['id' => $dc->id]);
      $dc->redirect($url);
    }
  }

  /**
   * @param array $arrData
   * @param string|NULL $href
   * @param string $label
   * @param string $title
   * @param string $icon
   * @param string $attributes
   * @param string $table
   * @param array|NULL $rootIds
   * @param array|NULL $childIds
   * @param bool $isCircular
   * @param string|NULL $previous
   * @param string|NULL $next
   * @param \Contao\DataContainer $dc
   *
   * @return string
   */
  public function stockButtonCallback(array $arrData, string $href=null, string $label,  string $title, string $icon, string $attributes, string $table, array $rootIds=null, array $childIds=null, bool $isCircular, string $previous=null,  string $next=null, DataContainer $dc) {
    return ProductHelper::genereateStockButtonLink($arrData['id']);
  }

}