<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Csrf\TokenGenerator;

use Nette\Utils\Random;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class TokenGenerator implements TokenGeneratorInterface
{

	/**
	 * {@inheritdoc}
	 */
	public function generateToken()
	{
		return Random::generate(43, '0-9a-zA-Z-_'); // Same length and characters as what symfony's default token generator returns.
	}

}
