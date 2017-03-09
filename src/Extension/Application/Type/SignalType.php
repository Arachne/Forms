<?php

namespace Arachne\Forms\Extension\Application\Type;

use Nette\Application\UI\Presenter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

        $prefix = $form->getRoot()->getConfig()->getMethod() === 'POST';
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
        return HiddenType::class;
    }
}
