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

use Isotope\Model\Product;
use Krabo\IsotopeStockBundle\Helper\ProductHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as TwigEnvironment;

class ProductController extends AbstractController
{

  /**
   * @var \Twig\Environment
   */
  private $twig;

  public function __construct(TwigEnvironment $twig)
  {
    $this->twig = $twig;
  }

  /**
   * @Route("/contao/tl_isotope_stock_booking/product/{id}",
   *     name="tl_isotope_stock_booking_product_info",
   *     defaults={"_scope": "backend"},
   *     requirements={"id"="\d+"}
   * )
   */
  public function viewProductStock(int $id): Response
  {
    $objProduct = Product::findByPk($id);

    $label = html_entity_decode($objProduct->name) . ' (' . $objProduct->sku.')';
    $productImage = \Isotope\Backend\Product\Label::generateImage($objProduct);
    $closeUrl = \Contao\System::getReferer();

    return new Response($this->twig->render(
      '@IsotopeStock/tl_isotope_stock_booking_product_info.html.twig',
      [
        'stockPerAccount' => ProductHelper::getProductStockPerAccount($id),
        'stockPerAccountType' => ProductHelper::getProductStockPerAccountType($id),
        'title' => $label,
        'image' => $productImage,
        'closeUrl' => $closeUrl,
      ]
    ));
  }
}