<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\Application\Type;

use Nette\Application\UI\Presenter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SignalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        // Detect nette/application v2.4+.
        $prefix = property_exists('Nette\Application\UI\Component', 'onAnchor') && $form->getRoot()->getConfig()->getMethod() === 'POST';
        $view->vars['full_name'] = ($prefix ? '_' : '').Presenter::SIGNAL_KEY;
        $view->vars['value'] = $options['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'signal';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\HiddenType';
    }
}
