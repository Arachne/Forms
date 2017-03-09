<?php

namespace Arachne\Forms\Application;

use Arachne\Forms\Extension\Application\Type\SignalType;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\ISignalReceiver;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
     * @var FormRendererInterface
     */
    protected $renderer;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function __construct(FormRendererInterface $renderer, FormInterface $form, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->renderer = $renderer;
        $this->form = $form;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return FormRendererInterface
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
        $this->monitor(Presenter::class);
    }

    protected function attached($presenter)
    {
        if ($presenter instanceof Presenter) {
            $this->form->add(
                '_signal',
                SignalType::class,
                [
                    'mapped' => false,
                    'data' => $this->lookupPath(Presenter::class).self::NAME_SEPARATOR.'submit',
                ]
            );
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
        return $this->lookup(Presenter::class, $need);
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

        switch ($signal) {
            case 'submit':
                $this->processSubmit($form, $request);
                break;
            case 'validate':
                $this->processValidate($form, $request);
                break;
            case 'render':
                $this->processRender($form, $request);
                break;
            default:
                throw new BadSignalException(
                    sprintf('Missing handler for signal "%s" in %s.', $signal, get_class($this))
                );
        }
    }

    /**
     * Submits the form.
     */
    protected function processSubmit(FormInterface $form, Request $request)
    {
        // Restored request should only render the form, not immediately submit it.
        if ($request->hasFlag(Request::RESTORED)) {
            return;
        }

        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new BadRequestException('The form was not submitted.');
        }

        $data = $form->getData();
        if ($form->isValid()) {
            $this->onSuccess($data, $this);
        } else {
            $this->onError($data, $this);
        }
        $this->onSubmit($data, $this);
    }

    /**
     * Provides ajax validation.
     */
    protected function processValidate(FormInterface $form, Request $request)
    {
        $presenter = $this->getPresenter();
        if (!$presenter->isAjax()) {
            throw new BadRequestException('The validate signal is only allowed in ajax mode.');
        }

        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new BadRequestException('The form was not submitted.');
        }

        $view = $this->getView();
        $errors = [];
        $this->walkErrors(
            $form->getErrors(true, false),
            $view,
            function (FormView $view) use (&$errors) {
                $errors[$view->vars['id']] = $this->renderer->searchAndRenderBlock($view, 'errors_content');
            }
        );

        $presenter->sendJson((object) ['errors' => $errors]);
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

    /**
     * Renders only specified fields. Useful for dynamic ajax forms.
     */
    protected function processRender(FormInterface $form, Request $request)
    {
        $presenter = $this->getPresenter();
        if (!$presenter->isAjax()) {
            throw new BadRequestException('The render signal is only allowed in ajax mode.');
        }

        $fields = $request->getPost($this->lookupPath(Presenter::class, true).self::NAME_SEPARATOR.'fields');
        if (!$fields) {
            throw new BadRequestException('No fields specified for rendering.');
        }

        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new BadRequestException('The form was not submitted.');
        }

        $view = $this->getView();
        $widgets = [];
        foreach ($fields as $field) {
            // Validate the field identifier for security reasons. A dot in the identifier would be particularly dangerous.
            if (!Strings::match($field, '~^(?:\[\w++\])++$~')) {
                throw new BadRequestException(
                    sprintf('Field identifier "%s" contains unallowed characters.', $field)
                );
            }

            // Skip duplicates. The renderer would return an empty string on second try.
            if (isset($widgets[$field])) {
                continue;
            }

            // Wrap an exception from PropertyAccessor in a BadRequestException.
            try {
                $fieldView = $this->propertyAccessor->getValue($view, $field);
            } catch (ExceptionInterface $e) {
                throw new BadRequestException(
                    sprintf('FormView not found for field identifier "%s".', $field),
                    0,
                    $e
                );
            }

            // Render the field widget.
            $widgets[$field] = $this->renderer->searchAndRenderBlock($fieldView, 'widget');
        }

        $presenter->sendJson((object) ['widgets' => $widgets]);
    }
}
