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

namespace Krabo\IsotopeStockBundle\Form\Type;

use Krabo\IsotopeStockBundle\Model\AccountModel;
use Krabo\IsotopeStockBundle\Model\BookingModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType {

  /**
   * {@inheritdoc}
   */
  public function configureOptions(OptionsResolver $resolver) {
    \Contao\System::loadLanguageFile(BookingModel::getTable());
    $accounts = AccountModel::findAll(['order' => 'type ASC, title ASC']);
    $resolver->setDefaults([
      'choices' => $accounts,
      'choice_label' => function(?AccountModel $accountModel) {
        return $accountModel ? html_entity_decode($accountModel->title) : '';
      },
      'placeholder' => $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['account_placeholder'],
      'attr' => [
        'class' => 'tl_select',
      ],
      'row_attr' => [
        'class' => 'w50 widget'
      ]
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    return ChoiceType::class;
  }


}