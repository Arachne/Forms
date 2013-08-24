<?php

namespace Arachne\PropertyAccess\DI;

use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class PropertyAccessExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('propertyAccessor'))
			->setClass('Symfony\Component\PropertyAccess\PropertyAccessorInterface')
			->setFactory('Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor');
	}

}
