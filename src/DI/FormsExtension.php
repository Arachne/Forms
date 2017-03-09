<?php

namespace Arachne\Forms\DI;

use Arachne\Csrf\DI\CsrfExtension;
use Arachne\Forms\Application\FormComponentFactory;
use Arachne\Forms\Extension\Application\Type\FormTypeApplicationExtension;
use Arachne\Forms\Extension\DI\DIFormExtension;
use Arachne\ServiceCollections\DI\ServiceCollectionsExtension;
use Arachne\Twig\DI\TwigExtension;
use Kdyby\Validator\DI\ValidatorExtension;
use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;
use ReflectionClass;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Form\TwigRendererEngineInterface;
use Symfony\Bridge\Twig\Form\TwigRendererInterface;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension;
use Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\FormRendererEngineInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\ResolvedFormTypeFactoryInterface;
use Symfony\Component\Validator\Mapping\Loader\XmlFileLoader;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormsExtension extends CompilerExtension
{
    /**
     * Form types with this tag can be found by the FormRegistry service.
     */
    const TAG_TYPE = 'arachne.forms.type';

    /**
     * Form type extensions with this tag can be found by the FormRegistry service.
     */
    const TAG_TYPE_EXTENSION = 'arachne.forms.type_extension';

    /**
     * Form type guessers with this tag are added to the FormTypeGuesserChain service.
     */
    const TAG_TYPE_GUESSER = 'arachne.forms.type_guesser';

    /**
     * @var array
     */
    public $defaults = [
        'defaultThemes' => [
            'form_div_layout.html.twig',
        ],
        'csrfTranslationDomain' => null,
    ];

    private $types = [
        ChoiceType::class,
        FormType::class,
    ];

    public function loadConfiguration()
    {
        $this->validateConfig($this->defaults);

        /* @var $serviceCollectionsExtension ServiceCollectionsExtension */
        $serviceCollectionsExtension = $this->getExtension(ServiceCollectionsExtension::class);

        $typeResolver = $serviceCollectionsExtension->getCollection(
            ServiceCollectionsExtension::TYPE_RESOLVER,
            self::TAG_TYPE,
            FormTypeInterface::class
        );

        $extensionIteratorResolver = $serviceCollectionsExtension->getCollection(
            ServiceCollectionsExtension::TYPE_ITERATOR_RESOLVER,
            self::TAG_TYPE_EXTENSION,
            FormTypeExtensionInterface::class
        );

        $guesserIterator = $serviceCollectionsExtension->getCollection(
            ServiceCollectionsExtension::TYPE_ITERATOR,
            self::TAG_TYPE_GUESSER,
            FormTypeGuesserInterface::class
        );

        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('resolvedTypeFactory'))
            ->setClass(ResolvedFormTypeFactoryInterface::class)
            ->setFactory(ResolvedFormTypeFactory::class);

        $builder->addDefinition($this->prefix('extension.di'))
            ->setClass(FormExtensionInterface::class)
            ->setFactory(DIFormExtension::class)
            ->setArguments(
                [
                    'typeResolver' => '@'.$typeResolver,
                    'typeExtensionResolver' => '@'.$extensionIteratorResolver,
                ]
            )
            ->setAutowired(false);

        $builder->addDefinition($this->prefix('formRegistry'))
            ->setClass(FormRegistryInterface::class)
            ->setFactory(
                FormRegistry::class,
                [
                    'extensions' => [
                        $this->prefix('@extension.di'),
                    ],
                ]
            );

        $builder->addDefinition($this->prefix('formFactory'))
            ->setClass(FormFactoryInterface::class)
            ->setFactory(FormFactory::class);

        $builder->addDefinition($this->prefix('typeGuesser'))
            ->setClass(FormTypeGuesserInterface::class)
            ->setFactory(FormTypeGuesserChain::class)
            ->setArguments(
                [
                    'guessers' => '@'.$guesserIterator,
                ]
            );

        $builder->addDefinition($this->prefix('choiceList.defaultChoiceListFactory'))
            ->setClass(ChoiceListFactoryInterface::class)
            ->setFactory(DefaultChoiceListFactory::class)
            ->setAutowired(false);

        $builder->addDefinition($this->prefix('choiceList.propertyAccessDecorator'))
            ->setClass(ChoiceListFactoryInterface::class)
            ->setFactory(
                PropertyAccessDecorator::class,
                [
                    'decoratedFactory' => $this->prefix('@choiceList.defaultChoiceListFactory'),
                ]
            )
            ->setAutowired(false);

        $builder->addDefinition($this->prefix('choiceList.cachingFactoryDecorator'))
            ->setClass(ChoiceListFactoryInterface::class)
            ->setFactory(
                CachingFactoryDecorator::class,
                [
                    'decoratedFactory' => $this->prefix('@choiceList.propertyAccessDecorator'),
                ]
            );

        foreach ($this->types as $class) {
            $typeName = strtolower(substr($class, strrpos($class, '\\') + 1, -4));
            $builder->addDefinition($this->prefix('type.'.$typeName))
                ->setClass($class)
                ->addTag(self::TAG_TYPE, [$class])
                ->setAutowired(false);
        }

        $builder->addDefinition($this->prefix('application.typeExtension.form'))
            ->setClass(FormTypeApplicationExtension::class)
            ->addTag(self::TAG_TYPE_EXTENSION, FormType::class)
            ->setAutowired(false);

        if ($this->getExtension(CsrfExtension::class, false)) {
            $builder->addDefinition($this->prefix('csrf.typeExtension.form'))
                ->setClass(FormTypeCsrfExtension::class)
                ->setArguments(
                    [
                        'translationDomain' => $this->config['csrfTranslationDomain'],
                    ]
                )
                ->addTag(self::TAG_TYPE_EXTENSION, FormType::class)
                ->setAutowired(false);
        }

        if ($this->getExtension(ValidatorExtension::class, false)) {
            $builder->addDefinition($this->prefix('validator.typeExtension.form'))
                ->setClass(FormTypeValidatorExtension::class)
                ->addTag(self::TAG_TYPE_EXTENSION, FormType::class)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('validator.typeExtension.repeated'))
                ->setClass(RepeatedTypeValidatorExtension::class)
                ->addTag(self::TAG_TYPE_EXTENSION, RepeatedType::class)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('validator.typeExtension.submit'))
                ->setClass(SubmitTypeValidatorExtension::class)
                ->addTag(self::TAG_TYPE_EXTENSION, SubmitType::class)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('validator.typeGuesser'))
                ->setClass(FormTypeGuesserInterface::class)
                ->setFactory(ValidatorTypeGuesser::class)
                ->addTag(self::TAG_TYPE_GUESSER)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('validationLoader'))
                ->setFactory(
                    XmlFileLoader::class,
                    [
                        'file' => dirname((new ReflectionClass(FormInterface::class))->getFileName()).'/Resources/config/validation.xml',
                    ]
                )
                ->setAutowired(false)
                ->addTag(ValidatorExtension::TAG_LOADER);
        }

        /* @var $twigExtension TwigExtension */
        $twigExtension = $this->getExtension(TwigExtension::class, false);
        if ($twigExtension) {
            $twigExtension->addPaths(
                [
                    dirname((new ReflectionClass(AppVariable::class))->getFileName()).'/Resources/views/Form',
                ]
            );

            $builder->addDefinition($this->prefix('twig.extension.translation'))
                ->setClass(TranslationExtension::class)
                ->addTag(TwigExtension::TAG_EXTENSION);

            $builder->addDefinition($this->prefix('twig.extension.form'))
                ->setClass(FormExtension::class)
                ->addTag(TwigExtension::TAG_EXTENSION);

            $builder->addDefinition($this->prefix('twig.renderer'))
                ->setClass(interface_exists(TwigRendererInterface::class) ? TwigRendererInterface::class : FormRendererInterface::class)
                ->setFactory(TwigRenderer::class)
                ->addTag(TwigExtension::TAG_RUNTIME, TwigRenderer::class);

            $builder->addDefinition($this->prefix('twig.engine'))
                ->setClass(interface_exists(TwigRendererEngineInterface::class) ? TwigRendererEngineInterface::class : FormRendererEngineInterface::class)
                ->setFactory(
                    TwigRendererEngine::class,
                    [
                        'defaultThemes' => $this->config['defaultThemes'],
                    ]
                );

            $builder->addDefinition($this->prefix('application.componentFactory'))
                ->setImplement(FormComponentFactory::class);
        }
    }

    /**
     * @param string $class
     * @param bool   $need
     *
     * @return CompilerExtension|null
     */
    private function getExtension($class, $need = true)
    {
        $extensions = $this->compiler->getExtensions($class);

        if (!$extensions) {
            if (!$need) {
                return null;
            }

            throw new AssertionException(
                sprintf('Extension "%s" requires "%s" to be installed.', get_class($this), $class)
            );
        }

        return reset($extensions);
    }
}
