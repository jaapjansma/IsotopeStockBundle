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

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\Database;
use Contao\System;
use Krabo\IsotopeStockBundle\Helper\ProductHelper;
use Krabo\IsotopeStockBundle\Model\AccountModel;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OverviewController extends AbstractController
{

  /**
   * @var ContaoCsrfTokenManager
   */
  private $tokenManager;

  /**
   * @var string
   */
  private $csrfTokenName;

  public function __construct(ContaoCsrfTokenManager $tokenManager)
  {
    $this->tokenManager = $tokenManager;
    $this->csrfTokenName = System::getContainer()->getParameter('contao.csrf_token_name');
  }

  /**
   * @Route("/contao/tl_isotope_stock_booking/overview_all",
   *     name="tl_isotope_stock_booking_overview_all",
   *     defaults={"_scope": "backend", "_token_check": true}
   * )
   */
  public function overviewAll(Request $request): Response {
    return $this->overview($request, false);
  }

  /**
   * @Route("/contao/tl_isotope_stock_booking/overview/{onlyActive}",
   *     name="tl_isotope_stock_booking_overview",
   *     defaults={"_scope": "backend", "_token_check": true}
   * )
   */
  public function overview(Request $request, bool $onlyActive=true): Response
  {
    \Contao\System::loadLanguageFile('default');
    \Contao\System::loadLanguageFile('tl_iso_product');
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', $GLOBALS['TL_LANG']['IstotopeStockProductInfo']['Product']);
    $sheet->getColumnDimension('A')->setAutoSize(TRUE);
    $sheet->getStyle('A1')->getFont()->setBold(TRUE);
    $sheet->getStyle('A1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    $sheet->setCellValue('B1', $GLOBALS['TL_LANG']['IstotopeStockProductInfo']['SKU']);
    $sheet->getColumnDimension('B')->setAutoSize(TRUE);
    $sheet->getStyle('B1')->getFont()->setBold(TRUE);
    $sheet->getStyle('B1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    $sheet->setCellValue('C1', $GLOBALS['TL_LANG']['IstotopeStockProductInfo']['Stock']);
    $sheet->getColumnDimension('C')->setAutoSize(TRUE);
    $sheet->getStyle('C1')->getFont()->setBold(TRUE);
    $sheet->getStyle('C1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    $sheet->setCellValue('D1', $GLOBALS['TL_LANG']['IstotopeStockProductInfo']['PreOrder']);
    $sheet->getColumnDimension('D')->setAutoSize(TRUE);
    $sheet->getStyle('D1')->getFont()->setBold(TRUE);
    $sheet->getStyle('D1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    $sheet->setCellValue('E1', $GLOBALS['TL_LANG']['tl_iso_product']['isostock_minimun_stock'][0]);
    $sheet->getColumnDimension('E')->setAutoSize(TRUE);
    $sheet->getStyle('E1')->getFont()->setBold(TRUE);
    $sheet->getStyle('E1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

    $accounts = AccountModel::findAll(['order' => 'title ASC']);
    $column = 'F';
    foreach($accounts as $account) {
      $sheet->setCellValue($column.'1', html_entity_decode($account->title));
      $sheet->getColumnDimension($column)->setAutoSize(TRUE);
      $sheet->getStyle($column.'1')->getFont()->setBold(TRUE);
      $sheet->getStyle($column.'1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
      $column ++;
    }
    $sheet->freezePane('A2');

    $sql = "SELECT id, name, sku, isostock_minimun_stock FROM tl_iso_product WHERE `pid` = '0'";
    if ($onlyActive) {
      $sql .= " AND `published` = '1'";
    }
    $sql .= " ORDER BY sku ASC, name ASC";
    $db = Database::getInstance();
    $products = $db->prepare($sql)->execute();
    $i = 2;
    while($product = $products->fetchAssoc()) {
      if (empty($product['name']) && empty($product['sku'])) {
        continue;
      }

      $sheet->setCellValue('A'.$i, html_entity_decode($product['name'], ENT_QUOTES));
      $sheet->getStyle('A'.$i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
      $sheet->setCellValue('B'.$i, $product['sku']);
      $sheet->getStyle('B'.$i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
      $stock = ProductHelper::getProductStockPerAccountType($product['id']);
      $sheet->setCellValue('C'.$i, $stock[AccountModel::STOCK_TYPE]['balance']);
      $sheet->getStyle('C'.$i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
      $sheet->setCellValue('D'.$i, $stock[AccountModel::PRE_ORDER_TYPE]['balance']);
      $sheet->getStyle('D'.$i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
      $sheet->setCellValue('E'.$i, $product['isostock_minimun_stock']);
      $sheet->getStyle('E'.$i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
      if ($stock[AccountModel::STOCK_TYPE]['balance'] < $product['isostock_minimun_stock'] && $product['isostock_minimun_stock'] > 0) {
        $sheet->getStyle('A'.$i)->getFont()->setColor(new Color(Color::COLOR_RED));
        $sheet->getStyle('B'.$i)->getFont()->setColor(new Color(Color::COLOR_RED));
        $sheet->getStyle('C'.$i)->getFont()->setColor(new Color(Color::COLOR_RED));
        $sheet->getStyle('D'.$i)->getFont()->setColor(new Color(Color::COLOR_RED));
        $sheet->getStyle('E'.$i)->getFont()->setColor(new Color(Color::COLOR_RED));
      }

      $column = 'F';
      $stockPerAccount = ProductHelper::getProductStockPerAccount($product['id']);
      foreach($accounts as $account) {
        if (isset($stockPerAccount[$account->id]) && isset ($stockPerAccount[$account->id]['balance'])) {
          $sheet->setCellValue($column . $i, $stockPerAccount[$account->id]['balance']);
          $sheet->getStyle($column.$i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        }
        $column ++;
      }

      $i++;
    }

    $writer = new Xlsx($spreadsheet);
    $response =  new StreamedResponse(
      function () use ($writer) {
        $writer->save('php://output');
      }
    );
    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
    $response->headers->set('Content-Disposition', 'attachment;filename="Voorraad'.date('ymd').'.xlsx"');
    $response->headers->set('Cache-Control','max-age=0');
    return $response;
  }

}