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

use Contao\Image;
use Contao\StringUtil;
use Isotope\Model\Product;
use Krabo\IsotopeStockBundle\Event\Events;
use Krabo\IsotopeStockBundle\Event\ManualBookingEvent;
use \Krabo\IsotopeStockBundle\Helper\BookingHelper;
use Krabo\IsotopeStockBundle\Helper\ProductHelper;
use Krabo\IsotopeStockBundle\Model\AccountModel;
use Krabo\IsotopeStockBundle\Model\BookingModel;

\Contao\System::loadLanguageFile('tl_isotope_stock_booking_line');

$GLOBALS['TL_DCA']['tl_isotope_stock_booking_line'] = array
(
  // Config
  'config' => array
  (
    'dataContainer'           => 'Table',
    'ptable'                  => 'tl_isotope_stock_booking',
    'sql'                     => array
    (
      'keys' => array
      (
        'id' => 'primary',
        'pid' => 'index'
      )
    ),
    'onsubmit_callback'   => array(
      array('tl_isotope_stock_booking_line', 'onSubmit'),
    ),
  ),

  // List
  'list' => array
  (
    'sorting' => array
    (
      'mode'                    => 4,
      'headerFields'            => array('description', 'date', 'period_id', 'product_id'),
      'fields'                  => array('account'),
      'panelLayout'             => 'sort,filter,search,limit',
      'child_record_callback'   => array('tl_isotope_stock_booking_line', 'listBookingLines'),
      'header_callback'   => array('tl_isotope_stock_booking_line', 'headerFields'),
    ),
    /*'label' => array
    (
      'showColumns'             => true,
      'fields'                  => array('account', 'debit', 'credit'),
    ),*/
    'global_operations' => array
    (
      'all' => array
      (
        'href'                => 'act=select',
        'class'               => 'header_edit_all',
        'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
      )
    ),
    'operations' => array
    (
      'edit' => array
      (
        'href'                => 'act=edit',
        'icon'                => 'edit.svg',
      ),
      'delete' => array
      (
        'href'                => 'act=delete',
        'icon'                => 'delete.svg',
        'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_isotope_stock_booking_line']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
      ),
    )
  ),

  // Palettes
  'palettes' => array
  (
    'default'                     => 'account;debit,credit'
  ),

  // Subpalettes
  'subpalettes' => array
  (
  ),

  // Fields
  'fields' => array
  (
    'id' => array
    (
      'sql'                     => "int(10) unsigned NOT NULL auto_increment"
    ),
    'tstamp' => array
    (
      'sql'                     => "int(10) unsigned NOT NULL default 0"
    ),
    'pid' => array
    (
      'foreignKey'              => 'tl_isotope_stock_booking.description',
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
    ),
    'account' => array
    (
      'filter'                  => true,
      'inputType'               => 'select',
      'eval'                    => array('doNotCopy'=>true),
      'foreignKey'              => 'tl_isotope_stock_account.title',
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'default'                 => '0',
    ),
    'debit' => array
    (
      'inputType'               => 'text',
      'eval'                    => array('doNotCopy'=>true, 'rgxp' => 'natural'),
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'flag'                    => 11,
      'default'                 => '0',
    ),
    'credit' => array
    (
      'inputType'               => 'text',
      'eval'                    => array('doNotCopy'=>true, 'rgxp' => 'natural'),
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'flag'                    => 11,
      'default'                 => '0',
    ),
  )
);

class tl_isotope_stock_booking_line {

  public function listBookingLines($arrRow) {
    $return = '';
    if ($arrRow['debit']) {
      $return .= ' ' . $GLOBALS['TL_LANG']['tl_isotope_stock_booking_line']['debit'][0].': '.$arrRow['debit'];
    }
    if ($arrRow['credit']) {
      $return .= ' ' . $GLOBALS['TL_LANG']['tl_isotope_stock_booking_line']['credit'][0].': '.$arrRow['credit'];
    }
    return trim($return);
  }

  public function headerFields($arrRow, \Contao\DataContainer $dc) {
    \Contao\System::loadLanguageFile(\Isotope\Model\Product::getTable());
    $productIdField = $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['product_id'][0];
    $booking = BookingModel::findByPk($dc->id);
    $objProduct = Product::findByPk($booking->product_id);
    $stockButton = ProductHelper::genereateStockButtonLink($objProduct->id);
    $editUrl = \Contao\DataContainer::addToUrl('do=iso_products&table=tl_iso_product&act=edit&id='.$objProduct->id);
    $editIcon = Image::getHtml('edit.gif', $GLOBALS['TL_LANG']['tl_iso_product']['edit'][0]);
    $editButton = '<a href="'.$editUrl.'">' . $editIcon . '</a>';
    $arrRow[$productIdField] .= '&nbsp;' . $editButton . '&nbsp;' . $stockButton;

    $balanceLabel = $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['in_balance'];
    $balanceIcon = 'ok.gif';
    if (!BookingHelper::isBookingInBalance($dc->id)) {
      $balanceLabel = $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['not_in_balance'];
      $balanceIcon = 'error.gif';
    }
    $arrRow[$GLOBALS['TL_LANG']['tl_isotope_stock_booking']['is_in_balance'][0]] = \Image::getHtml($balanceIcon, $balanceLabel, 'title="'.$balanceLabel.'"');

    return $arrRow;
  }

  /**
   * Updates the balance status for the booking.
   *
   * @param \Contao\DataContainer $dc
   *
   * @return void
   */
  public function onSubmit(\Contao\DataContainer $dc) {
    BookingHelper::updateBalanceStatusForBooking($dc->activeRecord->pid);
    $event = new ManualBookingEvent(BookingModel::findByPk($dc->activeRecord->pid));
    System::getContainer()
      ->get('event_dispatcher')
      ->dispatch($event, Events::MANUAL_BOOKING_EVENT);
  }

}