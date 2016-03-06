<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\DI;

use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Traversable;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormTypeGuesserChain implements FormTypeGuesserInterface
{

    /** @var Traversable */
    private $guessers;

    public function __construct(Traversable $guessers)
    {
        $this->guessers = $guessers;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        return $this->guess(function ($guesser) use ($class, $property) {
            return $guesser->guessType($class, $property);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function guessRequired($class, $property)
    {
        return $this->guess(function ($guesser) use ($class, $property) {
            return $guesser->guessRequired($class, $property);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
        return $this->guess(function ($guesser) use ($class, $property) {
            return $guesser->guessMaxLength($class, $property);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {
        return $this->guess(function ($guesser) use ($class, $property) {
            return $guesser->guessPattern($class, $property);
        });
    }

    private function guess(callable $closure)
    {
        $guesses = [];

        foreach ($this->guessers as $guesser) {
            if ($guess = $closure($guesser)) {
                $guesses[] = $guess;
            }
        }

        return Guess::getBestGuess($guesses);
    }
}
