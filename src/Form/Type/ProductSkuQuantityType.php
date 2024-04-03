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

use Krabo\IsotopeStockBundle\Validator\ValidProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductSkuQuantityType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $sku = $builder->add('sku', TextType::class, [
      'label' => $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['sku'],
      'attr' => [
        'class' => 'tl_text',
      ],
      'row_attr' => [
        'class' => 'widget w50'
      ],
      'constraints' => [
        new ValidProduct(),
      ],
    ]);
    $builder->add('quantity', NumberType::class, [
      'label' => $GLOBALS['TL_LANG']['tl_isotope_stock_booking']['product_quantity'],
      'attr' => [
        'class' => 'tl_text',
      ],
      'row_attr' => [
        'class' => 'widget w50'
      ],
      'constraints' => [
        new NotBlank(),
        new GreaterThanOrEqual(['value' => 1]),
      ]
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([]);
  }


}