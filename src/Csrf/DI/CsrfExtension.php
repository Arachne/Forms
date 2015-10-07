<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Csrf\DI;

use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class CsrfExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('tokenManager'))
			->setClass('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface')
			->setFactory('Symfony\Component\Security\Csrf\CsrfTokenManager');

		$builder->addDefinition($this->prefix('tokenGenerator'))
			->setClass('Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface')
			->setFactory('Arachne\Csrf\TokenGenerator\TokenGenerator');

		$builder->addDefinition($this->prefix('tokenStorage'))
			->setClass('Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface')
			->setFactory('Arachne\Csrf\TokenStorage\SessionTokenStorage');
	}

}
