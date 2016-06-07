<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\DI;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\Forms\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DIFormExtension implements FormExtensionInterface
{
    /**
     * @var ResolverInterface
     */
    private $typeResolver;

    /**
     * @var ResolverInterface
     */
    private $typeExtensionResolver;

    /**
     * @var FormTypeGuesserInterface
     */
    private $guesser;

    /**
     * @var bool
     */
    private $sf28;

    public function __construct(ResolverInterface $typeResolver, ResolverInterface $typeExtensionResolver, FormTypeGuesserInterface $guesser)
    {
        $this->typeResolver = $typeResolver;
        $this->typeExtensionResolver = $typeExtensionResolver;
        $this->guesser = $guesser;
        $this->sf28 = method_exists('Symfony\Component\Form\AbstractType', 'getName');
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        $type = $this->typeResolver->resolve($name);

        if (!$type) {
            throw new InvalidArgumentException(sprintf('The field type "%s" does not exist.', $name));
        }

        if ($this->sf28 && $name !== get_class($type) && $type->getName() !== $name) {
            throw new InvalidArgumentException(sprintf('The type name does not match the actual name. Expected "%s", given "%s"', $name, $type->getName()));
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        return (bool) $this->typeResolver->resolve($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        $iterator = $this->typeExtensionResolver->resolve($name);

        if (!$iterator) {
            return [];
        }

        $extensions = [];
        foreach ($iterator as $extension) {
            if ($extension->getExtendedType() !== $name) {
                throw new InvalidArgumentException(sprintf('The extended type does not match the actual extended type. Expected "%s", given "%s".', $name, $extension->getExtendedType()));
            }
            $extensions[] = $extension;
        }

        return $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTypeExtensions($name)
    {
        return (bool) $this->typeExtensionResolver->resolve($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        return $this->guesser;
    }
}
