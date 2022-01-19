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

\Contao\System::loadLanguageFile(\Isotope\Model\Product::getTable());
\Contao\Controller::loadDataContainer(\Isotope\Model\Product::getTable());
\Contao\System::loadLanguageFile(\Isotope\Model\ProductCollection::getTable());
\Contao\Controller::loadDataContainer(\Isotope\Model\ProductCollection::getTable());

$GLOBALS['TL_DCA']['tl_isotope_stock_booking'] = array
(
  // Config
  'config' => array
  (
    'dataContainer'             => 'Table',
    'ctable'                    => array('tl_isotope_stock_booking_line'),
    'switchToEdit'              => true,
    'sql'                       => array
    (
      'keys' => array
      (
        'id' => 'primary'
      )
    )
  ),

  // List
  'list' => array
  (
    'sorting' => array
    (
      'mode'                    => 1,
      'fields'                  => array('date','tstamp'),
      'flag'                    => 8,
      'panelLayout'             => 'sort,filter,search,limit'
    ),
    'label' => array
    (
      'fields'                  => array('description', 'type'),
      'label_callback'          => array('tl_isotope_stock_booking', 'generateLabel')
    ),
    'global_operations' => array
    (
      'mass_booking' => array
      (
        'label'               =>  $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['new_mass_booking'],
        'route'               => 'tl_isotope_stock_booking_mass_booking',
        'class'               => 'mass_booking',
        'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"',
        'icon'                => 'tablewizard.svg',
      ),
      'update_balance_status' => array
      (
        'label'               =>  $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['update_balance_status'],
        'route'               => 'tl_isotope_stock_booking_update_balance_status',
        'class'               => 'update_balance_status',
        'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"',
        'icon'                => 'changelog.svg',
      ),
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
        'href'                => 'table=tl_isotope_stock_booking_line',
        'icon'                => 'edit.svg',
      ),
      'editheader' => array
      (
        'href'                => 'table=tl_isotope_stock_booking&amp;act=edit',
        'icon'                => 'header.svg',
      ),
      'delete' => array
      (
        'href'                => 'act=delete',
        'icon'                => 'delete.svg',
        'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
      ),
    )
  ),

  // Palettes
  'palettes' => array
  (
    '__selector__'                => ['type'],
    'default'                     => 'description;date,period_id;type;{product_legend},product_id'
  ),

  // Subpalettes
  'subpalettes' => array
  (
    'type_1' => ';{order_legend},order_id',
    'type_2' => ';{order_legend},order_id',
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
    'description' => array
    (
      'search'                  => true,
      'inputType'               => 'text',
      'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
      'sql'                     => "varchar(255) NOT NULL default ''"
    ),
    'date' => array
    (
      'filter'                  => true,
      'inputType'               => 'text',
      'flag'                    => 8,
      'default'                 => time(),
      'eval'                    => array('mandatory'=>true, 'rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
      'sql'                     => "varchar(10) NOT NULL default ''"
    ),
    'period_id' => array
    (
      'filter'                  => true,
      'flag'                    => 2,
      'inputType'               => 'select',
      'eval'                    => array('doNotCopy'=>true, 'tl_class'=>'w50 wizard'),
      'options_callback'        => ['tl_isotope_stock_booking', 'periodOptions'],
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'default'                 => '0',
    ),
    'product_id'     => array
    (
      'inputType'               => 'tableLookup',
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'eval' => array
      (
        'mandatory'                 => true,
        'doNotSaveEmpty'            => true,
        'tl_class'                  => 'clr',
        'foreignTable'              => 'tl_iso_product',
        'fieldType'                 => 'radio',
        'listFields'                => array(\Isotope\Model\ProductType::getTable().'.name', 'name', 'sku'),
        'joins'                     => array
        (
          \Isotope\Model\ProductType::getTable() => array
          (
            'type' => 'LEFT JOIN',
            'jkey' => 'id',
            'fkey' => 'type',
          ),
        ),
        'searchFields'              => array('name', 'alias', 'sku', 'description'),
        'customLabels'              => array
        (
          $GLOBALS['TL_DCA'][\Isotope\Model\Product::getTable()]['fields']['type']['label'][0],
          $GLOBALS['TL_DCA'][\Isotope\Model\Product::getTable()]['fields']['name']['label'][0],
          $GLOBALS['TL_DCA'][\Isotope\Model\Product::getTable()]['fields']['sku']['label'][0],
        ),
        'sqlWhere'                  => 'pid=0',
        'searchLabel'               => 'Search products',
      ),
    ),
    'type' => array
    (
      'filter'                  => true,
      'inputType'               => 'radio',
      'eval'                    => array('doNotCopy'=>true, 'submitOnChange' => true),
      'reference'               => $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['type_options'],
      'options'                 => array('1', '2', '3', '4', '0'),
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'default'                 => '0',
    ),
    'is_in_balance' => array
    (
      'filter'                  => true,
      'inputType'               => 'checkbox',
      'eval'                    => array('doNotCopy'=>true),
      'sql'                     => "char(1) NOT NULL default ''",
      'default'                 => '0',
    ),
    'order_id'     => array
    (
      'inputType'               => 'tableLookup',
      'sql'                     => "int(10) unsigned NOT NULL default 0",
      'eval' => array
      (
        'mandatory'                 => true,
        'doNotSaveEmpty'            => true,
        'tl_class'                  => 'clr',
        'foreignTable'              => 'tl_iso_product_collection',
        'fieldType'                 => 'radio',
        'listFields'                => array(\Isotope\Model\ProductCollection::getTable().'.document_number'),
        'joins'                     => array(),
        'searchFields'              => array('document_number'),
        'customLabels'              => array
        (
          $GLOBALS['TL_DCA'][\Isotope\Model\ProductCollection::getTable()]['fields']['document_number']['label'][0],
        ),
        'sqlWhere'                  => 'type=\'order\' AND locked>0',
        'searchLabel'               => 'Search Order',
      ),
    ),
  )
);

class tl_isotope_stock_booking {

  public function periodOptions() {
    $options = array();
    $periods = \Database::getInstance()->prepare('SELECT * FROM `tl_isotope_stock_period` ORDER BY `active` DESC, `start` ASC, `title` ASC')->execute();
    while ($periods->next()) {
      $suffix = '';
      if (!$periods->active) {
        $suffix = ' (' . $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['period_inactive'] . ')';
      }
      $options[$periods->id] = $periods->title . $suffix;
    }
    return $options;
  }

  /**
   * Generates a label for the record listening.
   *
   * Adds an icon to indicate whether a booking is in balance or not.
   *
   * @param $arrData
   * @param $strLabel
   * @param \Contao\DataContainer $dc
   * @param $arrColumns
   *
   * @return string
   */
  public function generateLabel($arrData, $strLabel, \Contao\DataContainer $dc, $arrColumns) {
    $balanceLabel = $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['in_balance'];
    $balanceIcon = 'ok.gif';
    if (empty($arrData['is_in_balance'])) {
      $balanceLabel = $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['not_in_balance'];
      $balanceIcon = 'error.gif';
    }
    $strLabel = \Image::getHtml($balanceIcon, $balanceLabel, 'title="'.$balanceLabel.'"') . '&nbsp;' . $strLabel;
    $type = $arrData['type'];
    $strLabel .= ' - '.$GLOBALS['TL_LANG']['tl_isotope_stock_booking']['type_options'][$type];
    return $strLabel;
  }

}