<?php

namespace Arachne\Forms\Application;

use Symfony\Component\Form\FormInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface FormComponentFactory
{
    /**
     * @return FormComponent
     */
    public function create(FormInterface $form);
}
