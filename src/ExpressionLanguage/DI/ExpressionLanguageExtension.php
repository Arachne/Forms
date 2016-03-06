<?php

namespace Arachne\ExpressionLanguage\DI;

use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ExpressionLanguageExtension extends CompilerExtension
{

    const TAG_FUNCTION_PROVIDER = 'arachne.expressionLanguage.functionProvider';

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('expressionLanguage'))
            ->setClass('Symfony\Component\ExpressionLanguage\ExpressionLanguage');
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $builder->getDefinition($this->prefix('expressionLanguage'))
            ->setArguments([
                'providers' => array_map(function ($service) {
                    return '@' . $service;
                }, array_keys($builder->findByTag(self::TAG_FUNCTION_PROVIDER))),
            ]);
    }
}
