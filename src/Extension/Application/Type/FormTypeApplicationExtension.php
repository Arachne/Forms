<?php

declare(strict_types=1);

namespace Arachne\Forms\Extension\Application\Type;

use Arachne\Forms\Extension\Application\ApplicationRequestHandler;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormTypeApplicationExtension extends AbstractTypeExtension
{
    /**
     * @var ApplicationRequestHandler
     */
    private $requestHandler;

    public function __construct()
    {
        $this->requestHandler = new ApplicationRequestHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setRequestHandler($this->requestHandler);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield FormType::class;
    }

    /**
     * @deprecated to be removed when Symfony 4.1 and older are no longer supported
     */
    public function getExtendedType(): string
    {
        return FormType::class;
    }
}
