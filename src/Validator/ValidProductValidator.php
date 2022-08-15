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

namespace Krabo\IsotopeStockBundle\Validator;

use Isotope\Model\Product;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidProductValidator extends ConstraintValidator {

  /**
   * Checks if the passed value is valid.
   *
   * @param mixed $value The value that should be validated
   */
  public function validate($value, Constraint $constraint) {
    if (empty($value)) {
      $this->context->addViolation(sprintf($GLOBALS['TL_LANG']['IsotopeStockProductNotFound'], $value));
    }
    $product = Product::findOneBy('sku', $value);
    if (!$product) {
      $this->context->addViolation(sprintf($GLOBALS['TL_LANG']['IsotopeStockProductNotFound'], $value));
    }
  }


}