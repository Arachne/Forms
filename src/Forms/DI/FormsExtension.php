<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\DI;

use Arachne\DIHelpers\CompilerExtension;
use Arachne\Twig\DI\TwigExtension;
use Kdyby\Validator\DI\ValidatorExtension;
use Nette\DI\Statement;
use ReflectionClass;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormsExtension extends CompilerExtension
{

	const TAG_TYPE = 'arachne.forms.type';
	const TAG_TYPE_EXTENSION = 'arachne.forms.type_extension';
	const TAG_TYPE_GUESSER = 'arachne.forms.type_guesser';

	/** @var array */
	public $defaults = [
		'defaultThemes' => [
			'form_div_layout.html.twig',
		],
		'csrfTranslationDomain' => null,
	];

	private $types = [
		'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
		'Symfony\Component\Form\Extension\Core\Type\ButtonType',
		'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
		'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
		'Symfony\Component\Form\Extension\Core\Type\CollectionType',
		'Symfony\Component\Form\Extension\Core\Type\CountryType',
		'Symfony\Component\Form\Extension\Core\Type\CurrencyType',
		'Symfony\Component\Form\Extension\Core\Type\DateType',
		'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
		'Symfony\Component\Form\Extension\Core\Type\EmailType',
		'Symfony\Component\Form\Extension\Core\Type\FileType',
		'Symfony\Component\Form\Extension\Core\Type\FormType',
		'Symfony\Component\Form\Extension\Core\Type\HiddenType',
		'Symfony\Component\Form\Extension\Core\Type\IntegerType',
		'Symfony\Component\Form\Extension\Core\Type\LanguageType',
		'Symfony\Component\Form\Extension\Core\Type\LocaleType',
		'Symfony\Component\Form\Extension\Core\Type\MoneyType',
		'Symfony\Component\Form\Extension\Core\Type\NumberType',
		'Symfony\Component\Form\Extension\Core\Type\PasswordType',
		'Symfony\Component\Form\Extension\Core\Type\PercentType',
		'Symfony\Component\Form\Extension\Core\Type\RadioType',
		'Symfony\Component\Form\Extension\Core\Type\RangeType',
		'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
		'Symfony\Component\Form\Extension\Core\Type\ResetType',
		'Symfony\Component\Form\Extension\Core\Type\SearchType',
		'Symfony\Component\Form\Extension\Core\Type\SubmitType',
		'Symfony\Component\Form\Extension\Core\Type\TextType',
		'Symfony\Component\Form\Extension\Core\Type\TextareaType',
		'Symfony\Component\Form\Extension\Core\Type\TimeType',
		'Symfony\Component\Form\Extension\Core\Type\TimezoneType',
		'Symfony\Component\Form\Extension\Core\Type\UrlType',
	];

	private $typeExtensions = [
		'Symfony\Component\Form\Extension\Core\Type\FormType' => [
			'application' => 'Arachne\Forms\Extension\Application\Type\FormTypeApplicationExtension',
			'validator' => 'Arachne\Forms\Extension\Validator\Type\FormTypeValidatorExtension',
			'csrf' => 'Arachne\Forms\Extension\Csrf\Type\FormTypeCsrfExtension',
		],
		'Symfony\Component\Form\Extension\Core\Type\RepeatedType' => [
			'validator' => 'Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension',
		],
		'Symfony\Component\Form\Extension\Core\Type\SubmitType' => [
			'validator' => 'Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension',
		],
	];

	public function loadConfiguration()
	{
		$this->validateConfig($this->defaults);

		$this->getExtension('Arachne\DIHelpers\DI\ResolversExtension')->add(self::TAG_TYPE, 'Symfony\Component\Form\FormTypeInterface');
		$this->getExtension('Arachne\DIHelpers\DI\IteratorResolversExtension')->add(self::TAG_TYPE_EXTENSION, 'Symfony\Component\Form\FormTypeExtensionInterface');
		$this->getExtension('Arachne\DIHelpers\DI\IteratorsExtension')->add(self::TAG_TYPE_GUESSER, 'Symfony\Component\Form\FormTypeGuesserInterface');

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('resolvedTypeFactory'))
			->setClass('Symfony\Component\Form\ResolvedFormTypeFactoryInterface')
			->setFactory('Symfony\Component\Form\ResolvedFormTypeFactory');

		$builder->addDefinition($this->prefix('extension.di'))
			->setClass('Symfony\Component\Form\FormExtensionInterface')
			->setFactory('Arachne\Forms\Extension\DI\DIFormExtension')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('formRegistry'))
			->setClass('Symfony\Component\Form\FormRegistryInterface')
			->setFactory('Symfony\Component\Form\FormRegistry', [
				'extensions' => [ $this->prefix('@extension.di') ],
			]);

		$builder->addDefinition($this->prefix('formFactory'))
			->setClass('Symfony\Component\Form\FormFactoryInterface')
			->setFactory('Symfony\Component\Form\FormFactory');

		$builder->addDefinition($this->prefix('typeGuesser'))
			->setClass('Symfony\Component\Form\FormTypeGuesserInterface')
			->setFactory('Arachne\Forms\Extension\DI\FormTypeGuesserChain');

		$builder->addDefinition($this->prefix('choiceList.defaultChoiceListFactory'))
			->setClass('Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface')
			->setFactory('Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('choiceList.propertyAccessDecorator'))
			->setClass('Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface')
			->setFactory('Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator', [
				'decoratedFactory' => $this->prefix('@choiceList.defaultChoiceListFactory'),
			])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('choiceList.cachingFactoryDecorator'))
			->setClass('Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface')
			->setFactory('Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator', [
				'decoratedFactory' => $this->prefix('@choiceList.propertyAccessDecorator'),
			]);

		$builder->addDefinition($this->prefix('validator.typeGuesser'))
			->setClass('Symfony\Component\Form\FormTypeGuesserInterface')
			->setFactory('Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser', [
				'metadataFactory' => new Statement('?->getMetadataFactory()', [ '@Symfony\Component\Validator\Validator\ValidatorInterface' ]),
			])
			->addTag(self::TAG_TYPE_GUESSER)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('application.componentFactory'))
			->setImplement('Arachne\Forms\Application\FormComponentFactory');

		$builder->addDefinition($this->prefix('validationLoader'))
			->setFactory('Symfony\Component\Validator\Mapping\Loader\XmlFileLoader', [
				'file' => dirname((new ReflectionClass('Symfony\Component\Form\FormInterface'))->getFileName()) . '/Resources/config/validation.xml',
			])
			->setAutowired(false)
			->addTag(ValidatorExtension::TAG_LOADER);

		foreach ($this->types as $class) {
			$typeName = $this->createShortName($class);
			$builder->addDefinition($this->prefix('type.' . $typeName))
				->setClass($class)
				->addTag(self::TAG_TYPE, [
					$typeName,
					$class,
				])
				->setAutowired(false);
		}

		foreach ($this->typeExtensions as $type => $classes) {
			$typeName = $this->createShortName($type);
			foreach ($classes as $name => $class) {
				$builder->addDefinition($this->prefix('typeExtension.' . $typeName . '.' . $name))
					->setClass($class)
					->addTag(self::TAG_TYPE_EXTENSION, $type)
					->setAutowired(false);
			}
		}

		$builder->getDefinition($this->prefix('typeExtension.form.csrf'))
			->setArguments([
				'translationDomain' => $this->config['csrfTranslationDomain'],
			]);

		$builder->addDefinition($this->prefix('twig.extension.translation'))
			->setClass('Symfony\Bridge\Twig\Extension\TranslationExtension')
			->addTag(TwigExtension::TAG_EXTENSION);

		$builder->addDefinition($this->prefix('twig.extension.form'))
			->setClass('Symfony\Bridge\Twig\Extension\FormExtension')
			->addTag(TwigExtension::TAG_EXTENSION);

		$builder->addDefinition($this->prefix('twig.renderer'))
			->setClass('Symfony\Bridge\Twig\Form\TwigRendererInterface')
			->setFactory('Arachne\Forms\Twig\TwigRenderer');

		$builder->addDefinition($this->prefix('twig.engine'))
			->setClass('Symfony\Bridge\Twig\Form\TwigRendererEngineInterface')
			->setFactory('Symfony\Bridge\Twig\Form\TwigRendererEngine', [
				'defaultThemes' => $this->config['defaultThemes'],
			]);
	}

	public function beforeCompile()
	{
		$this->getExtension('Arachne\Twig\DI\TwigExtension')
			->addPath(dirname((new ReflectionClass('Symfony\Bridge\Twig\AppVariable'))->getFileName()) . '/Resources/views/Form');

		$builder = $this->getContainerBuilder();

		$builder->getDefinition($this->prefix('extension.di'))
			->setArguments([
				'typeResolver' => '@' . $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension')->get(self::TAG_TYPE),
				'typeExtensionResolver' => '@' . $this->getExtension('Arachne\DIHelpers\DI\IteratorResolversExtension')->get(self::TAG_TYPE_EXTENSION),
			]);

		$builder->getDefinition($this->prefix('typeGuesser'))
			->setArguments([
				'guessers' => '@' . $this->getExtension('Arachne\DIHelpers\DI\IteratorsExtension')->get(self::TAG_TYPE_GUESSER),
			]);
	}

	private function createShortName($class)
	{
		return strtolower(substr($class, strrpos($class, '\\') + 1, -4));
	}

}
