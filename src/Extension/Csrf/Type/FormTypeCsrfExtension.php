<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\Csrf\Type;

use Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension as BaseFormTypeCsrfExtension;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormTypeCsrfExtension extends BaseFormTypeCsrfExtension
{
    public function __construct(CsrfTokenManagerInterface $defaultTokenManager, $defaultEnabled = true, $defaultFieldName = '_token', TranslatorInterface $translator = null, $translationDomain = null)
    {
        parent::__construct($defaultTokenManager, $defaultEnabled, $defaultFieldName, $translator, $translationDomain);
    }
}
