<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Application;

use Nette\Application\Request;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\ISignalReceiver;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IContainer;
use Symfony\Bridge\Twig\Form\TwigRendererInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Twig_Environment;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormComponent extends Component implements ISignalReceiver
{

    /**
     * Array of function(mixed $data, FormComponent $form); Occurs when the form is submitted and successfully validated.
     * @var callable[]
     */
    public $onSuccess;

    /**
     * Array of function(mixed $data, FormComponent $form); Occurs when the form is submitted and is not valid.
     * @var callable[]
     */
    public $onError;

    /**
     * Array of function(mixed $data, FormComponent $form); Occurs when the form is submitted.
     * @var callable[]
     */
    public $onSubmit;

    /**
     * Array of function(FormView $view, FormComponent $form); Occurs when the form is rendered.
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
     * @param TwigRendererInterface $renderer
     * @param Twig_Environment $twig
     * @param FormInterface $form
     */
    public function __construct(TwigRendererInterface $renderer, Twig_Environment $twig, FormInterface $form)
    {
        $this->renderer = $renderer;
        $renderer->setEnvironment($twig); // Cannot be in DIC setup because of cyclic reference.
        $this->form = $form;
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
                'data' => $this->lookupPath('Nette\Application\UI\Presenter') . self::NAME_SEPARATOR . 'submit',
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
     * @param bool $need
     * @return Presenter|null
     */
    public function getPresenter($need = true)
    {
        return $this->lookup('Nette\Application\UI\Presenter', $need);
    }

    /**
     * @param string $signal
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
        } else {
            throw new BadSignalException("Missing handler for signal '$signal' in " . get_class($this) . ".");
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
        $this->walkErrors($form->getErrors(true, false), $view, function ($view) use (& $errors) {
            $errors[$view->vars['id']] = $this->renderer->searchAndRenderBlock($view, 'errors_content');
        });
        $this->getPresenter()->sendJson((object) [ 'errors' => $errors ]);
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
}
