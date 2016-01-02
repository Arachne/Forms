<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\Validator;

use Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser as BaseValidatorTypeGuesser;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\ValueGuess;
use Symfony\Component\Validator\Constraint;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ValidatorTypeGuesser extends BaseValidatorTypeGuesser
{

	/**
	 * {@inheritdoc}
	 */
	public function guessRequiredForConstraint(Constraint $constraint)
	{
		// Fix guessing of required option for checkboxes with NotNull constraint.
		if (get_class($constraint) === 'Symfony\Component\Validator\Constraints\NotNull') {
			return new ValueGuess(true, Guess::MEDIUM_CONFIDENCE);
		}

		return parent::guessRequiredForConstraint($constraint);
	}

}
