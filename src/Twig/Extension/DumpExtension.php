<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Twig\Extension;

use Arachne\Twig\TokenParser\DumpTokenParser;
use Tracy\Debugger;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_Template;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DumpExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('dump', [ $this, 'dump' ], [ 'is_safe' => array('html'), 'needs_context' => true, 'needs_environment' => true ]),
        ];
    }

    public function getTokenParsers()
    {
        return [ new DumpTokenParser() ];
    }

    public function getName()
    {
        return 'dump';
    }

    public function dump(Twig_Environment $env, $context)
    {
        if (!$env->isDebug()) {
            return;
        }

        if (2 === func_num_args()) {
            $vars = [];
            foreach ($context as $key => $value) {
                if (!$value instanceof Twig_Template) {
                    $vars[$key] = $value;
                }
            }
        } else {
            $vars = func_get_args();
            unset($vars[0], $vars[1]);
        }

        Debugger::barDump($vars);
    }
}
