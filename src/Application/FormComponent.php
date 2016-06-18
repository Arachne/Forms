<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Application;

use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\ISignalReceiver;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings;
use Symfony\Bridge\Twig\Form\TwigRendererInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig_Environment;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormComponent extends Component implements ISignalReceiver
{
    /**
     * Array of function(mixed $data, FormComponent $form); Occurs when the form is submitted and successfully validated.
     *
     * @var callable[]
     */
    public $onSuccess;

    /**
     * Array of function(mixed $data, FormComponent $form); Occurs when the form is submitted and is not valid.
     *
     * @var callable[]
     */
    public $onError;

    /**
     * Array of function(mixed $data, FormComponent $form); Occurs when the form is submitted.
     *
     * @var callable[]
     */
    public $onSubmit;

    /**
     * Array of function(FormView $view, FormComponent $form); Occurs when the form is rendered.
     *
     * @var callable[]
     */
    public $onCreateView;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var FormView
     */
    protected $view;

    /**
     * @var TwigRendererInterface
     */
    protected $renderer;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function __construct(TwigRendererInterface $renderer, Twig_Environment $twig, FormInterface $form, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->renderer = $renderer;
        $renderer->setEnvironment($twig); // Cannot be in DIC setup because of cyclic reference.
        $this->form = $form;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return TwigRendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return FormView
     */
    public function getView()
    {
        if (!$this->view) {
            $this->view = $this->form->createView();
            $this->onCreateView($this->view, $this);
        }

        return $this->view;
    }

    protected function validateParent(IContainer $parent)
    {
        parent::validateParent($parent);
        $this->monitor('Nette\Application\UI\Presenter');
    }

    protected function attached($presenter)
    {
        if ($presenter instanceof Presenter) {
            $this->form->add('_signal', 'Arachne\Forms\Extension\Application\Type\SignalType', [
                'mapped' => false,
                'data' => $this->lookupPath('Nette\Application\UI\Presenter').self::NAME_SEPARATOR.'submit',
            ]);
        }

        parent::attached($presenter);
    }

    public function render(array $variables = [])
    {
        echo $this->renderer->renderBlock($this->getView(), 'form', $variables);
    }

    /**
     * Returns the presenter where this component belongs to.
     *
     * @param bool $need
     *
     * @return Presenter|null
     */
    public function getPresenter($need = true)
    {
        return $this->lookup('Nette\Application\UI\Presenter', $need);
    }

    /**
     * @param string $signal
     *
     * @throws BadSignalException
     */
    public function signalReceived($signal)
    {
        $form = $this->getForm();
        $request = $this->getPresenter()->getRequest();

        if ($signal === 'submit') {
            $this->processSubmit($form, $request);
        } elseif ($signal === 'validate') {
            $this->processValidate($form, $request);
        } elseif ($signal === 'render') {
            $this->processRender($form, $request);
        } else {
            throw new BadSignalException("Missing handler for signal '$signal' in ".get_class($this).'.');
        }
    }

    protected function processSubmit(FormInterface $form, Request $request)
    {
        if ($request->hasFlag(Request::RESTORED)) {
            return;
        }

        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return;
        }

        $data = $form->getData();
        if ($form->isValid()) {
            $this->onSuccess($data, $this);
        } else {
            $this->onError($data, $this);
        }
        $this->onSubmit($data, $this);
    }

    protected function processValidate(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$this->getPresenter()->isAjax()) {
            return;
        }

        $view = $this->getView();
        $errors = [];
        $this->walkErrors($form->getErrors(true, false), $view, function (FormView $view) use (&$errors) {
            $errors[$view->vars['id']] = $this->renderer->searchAndRenderBlock($view, 'errors_content');
        });

        $this->getPresenter()->sendJson((object) ['errors' => $errors]);
    }

    private function walkErrors(FormErrorIterator $iterator, FormView $view, callable $callback)
    {
        $new = true;
        foreach ($iterator as $error) {
            if ($error instanceof FormErrorIterator) {
                $this->walkErrors($error, $view[$error->getForm()->getName()], $callback);
            } elseif ($new) {
                $callback($view);
                $new = false;
            }
        }
    }

    protected function processRender(FormInterface $form, Request $request)
    {
        $fields = $request->getPost($this->lookupPath('Nette\Application\UI\Presenter', true).'-fields');
        $form->handleRequest($request);
        if (!$fields || !$form->isSubmitted() || !$this->getPresenter()->isAjax()) {
            return;
        }

        $view = $this->getView();
        $widgets = [];
        foreach ($fields as $field) {
            // Validate the field identifier for security reasons. A dot in the identifier would be particularly dangerous.
            if (!Strings::match($field, '~^(?:\[\w++\])++$~')) {
                throw new BadRequestException("Field identifier '$field' containes unallowed characters.");
            }

            // Skip duplicates. The renderer would return an empty string on second try.
            if (isset($widgets[$field])) {
                continue;
            }

            // Wrap an exception from PropertyAccessor in a BadRequestException.
            try {
                $fieldView = $this->propertyAccessor->getValue($view, $field);
            } catch (ExceptionInterface $e) {
                throw new BadRequestException("FormView not found for field identifier '$field'.", 0, $e);
            }

            // Render the field widget.
            $widgets[$field] = $this->renderer->searchAndRenderBlock($fieldView, 'widget');
        }

        $this->getPresenter()->sendJson((object) ['widgets' => $widgets]);
    }
}
