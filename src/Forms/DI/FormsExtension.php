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
use Arachne\DIHelpers\DI\IteratorResolversExtension;
use Arachne\DIHelpers\DI\IteratorsExtension;
use Arachne\DIHelpers\DI\ResolversExtension;
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

	/**
	 * @var array
	 */
	public $defaults = [
		'defaultThemes' => [
			'form_div_layout.html.twig',
			//'form_table_layout.html.twig',
			//'bootstrap_3_layout.html.twig',
			//'bootstrap_3_horizontal_layout.html.twig',
		],
		'csrfTranslationDomain' => null,
	];

	private $types = [
		'birthday' => 'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
		'button' => 'Symfony\Component\Form\Extension\Core\Type\ButtonType',
		'checkbox' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
		'choice' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
		'collection' => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
		'country' => 'Symfony\Component\Form\Extension\Core\Type\CountryType',
		'currency' => 'Symfony\Component\Form\Extension\Core\Type\CurrencyType',
		'date' => 'Symfony\Component\Form\Extension\Core\Type\DateType',
		'datetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
		'email' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
		'file' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
		'form' => 'Symfony\Component\Form\Extension\Core\Type\FormType',
		'hidden' => 'Symfony\Component\Form\Extension\Core\Type\HiddenType',
		'integer' => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
		'language' => 'Symfony\Component\Form\Extension\Core\Type\LanguageType',
		'locale' => 'Symfony\Component\Form\Extension\Core\Type\LocaleType',
		'money' => 'Symfony\Component\Form\Extension\Core\Type\MoneyType',
		'number' => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
		'password' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
		'percent' => 'Symfony\Component\Form\Extension\Core\Type\PercentType',
		'radio' => 'Symfony\Component\Form\Extension\Core\Type\RadioType',
		// 'range' => 'Symfony\Component\Form\Extension\Core\Type\Range', // TODO: Symfony 2.8.
		'repeated' => 'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
		'reset' => 'Symfony\Component\Form\Extension\Core\Type\ResetType',
		'search' => 'Symfony\Component\Form\Extension\Core\Type\SearchType',
		'submit' => 'Symfony\Component\Form\Extension\Core\Type\SubmitType',
		'text' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
		'textarea' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
		'time' => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
		'timezone' => 'Symfony\Component\Form\Extension\Core\Type\TimezoneType',
		'url' => 'Symfony\Component\Form\Extension\Core\Type\UrlType',
	];

	private $typeExtensions = [
		'form' => [
			'application' => 'Arachne\Forms\Extension\Application\Type\FormTypeApplicationExtension',
			'validator' => 'Arachne\Forms\Extension\Validator\Type\FormTypeValidatorExtension',
			'csrf' => 'Arachne\Forms\Extension\Csrf\Type\FormTypeCsrfExtension',
		],
		'repeated' => [
			'validator' => 'Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension',
		],
		'submit' => [
			'validator' => 'Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension',
		],
	];

	public function loadConfiguration()
	{
		$this->validateConfig($this->defaults);

		$this->getExtension(ResolversExtension::class)->add(self::TAG_TYPE, 'Symfony\Component\Form\FormTypeInterface');
		$this->getExtension(IteratorResolversExtension::class)->add(self::TAG_TYPE_EXTENSION, 'Symfony\Component\Form\FormTypeExtensionInterface');
		$this->getExtension(IteratorsExtension::class)->add(self::TAG_TYPE_GUESSER, 'Symfony\Component\Form\FormTypeGuesserInterface');

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

		foreach ($this->types as $type => $class) {
			$builder->addDefinition($this->prefix('type.' . $type))
				->setClass($class)
				->addTag(self::TAG_TYPE, $type)
				->setAutowired(false);
		}

		foreach ($this->typeExtensions as $type => $classes) {
			foreach ($classes as $name => $class) {
				$builder->addDefinition($this->prefix('typeExtension.' . $type . '.' . $name))
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
				'typeResolver' => '@' . $this->getExtension(ResolversExtension::class)->get(self::TAG_TYPE),
				'typeExtensionResolver' => '@' . $this->getExtension(IteratorResolversExtension::class)->get(self::TAG_TYPE_EXTENSION),
			]);

		$builder->getDefinition($this->prefix('typeGuesser'))
			->setArguments([
				'guessers' => '@' . $this->getExtension(IteratorsExtension::class)->get(self::TAG_TYPE_GUESSER)
			]);
	}

}
