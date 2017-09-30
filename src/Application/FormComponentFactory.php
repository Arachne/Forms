<?php

declare(strict_types=1);

namespace Arachne\Forms\Application;

use Symfony\Component\Form\FormInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface FormComponentFactory
{
    public function create(FormInterface $form): FormComponent;
}
