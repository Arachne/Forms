<?php

namespace Arachne\Forms\Extension\DI;

use Arachne\Forms\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DIFormExtension implements FormExtensionInterface
{
    /**
     * @var callable
     */
    private $typeResolver;

    /**
     * @var callable
     */
    private $typeExtensionResolver;

    /**
     * @var FormTypeGuesserInterface
     */
    private $guesser;

    public function __construct(callable $typeResolver, callable $typeExtensionResolver, FormTypeGuesserInterface $guesser)
    {
        $this->typeResolver = $typeResolver;
        $this->typeExtensionResolver = $typeExtensionResolver;
        $this->guesser = $guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name): FormTypeInterface
    {
        $type = call_user_func($this->typeResolver, $name);

        if (!$type) {
            throw new InvalidArgumentException(sprintf('The field type "%s" does not exist.', $name));
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name): bool
    {
        return (bool) call_user_func($this->typeResolver, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name): array
    {
        $iterator = call_user_func($this->typeExtensionResolver, $name);

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
    public function hasTypeExtensions($name): bool
    {
        return (bool) call_user_func($this->typeExtensionResolver, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser(): ?FormTypeGuesserInterface
    {
        return $this->guesser;
    }
}
