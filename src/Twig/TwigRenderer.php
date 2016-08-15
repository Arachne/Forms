<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Twig;

use Symfony\Bridge\Twig\Form\TwigRenderer as BaseTwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngineInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 *
 * @todo remove this class after upgrading to Symfony 3. Requires symfony/twig-bridge 3.0.8+ or 3.1.2+ (see https://github.com/symfony/symfony/pull/19065)
 */
class TwigRenderer extends BaseTwigRenderer
{
    public function __construct(TwigRendererEngineInterface $engine, CsrfTokenManagerInterface $csrfTokenManager)
    {
        parent::__construct($engine, $csrfTokenManager);
    }
}
