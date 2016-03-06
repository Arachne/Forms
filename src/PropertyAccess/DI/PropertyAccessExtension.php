<?php

namespace Arachne\PropertyAccess\DI;

use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class PropertyAccessExtension extends CompilerExtension
{

    /** @var array */
    public $defaults = [
        'magicCall' => false,
        'throwExceptionOnInvalidIndex' => false,
    ];

    public function loadConfiguration()
    {
        $this->validateConfig($this->defaults);

        $builder = $this->getContainerBuilder();

        $definition = $builder->addDefinition($this->prefix('propertyAccessorBuilder'));
        $definition->setClass('Symfony\Component\PropertyAccess\PropertyAccessorBuilder');

        if ($this->config['magicCall']) {
            $definition->addSetup('enableMagicCall');
        } else {
            $definition->addSetup('disableMagicCall');
        }

        if ($this->config['throwExceptionOnInvalidIndex']) {
            $definition->addSetup('enableExceptionOnInvalidIndex');
        } else {
            $definition->addSetup('disableExceptionOnInvalidIndex');
        }

        $builder->addDefinition($this->prefix('propertyAccessor'))
            ->setClass('Symfony\Component\PropertyAccess\PropertyAccessorInterface')
            ->setFactory('@Symfony\Component\PropertyAccess\PropertyAccessorBuilder::getPropertyAccessor');
    }
}
