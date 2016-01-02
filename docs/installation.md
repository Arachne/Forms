Installation
====

The best way to install Arachne/Forms is using [Composer](http://getcomposer.org/). The commands below will install all the libraries needed to use Arachne/Forms. They are intentionally not direct dependencies so that advanced users may skip or replace some of them.

```sh
composer require arachne/forms
composer require kdyby/translation
composer require kdyby/validator
composer require symfony/security-csrf
composer require symfony/twig-bridge
```

It is recommended to add all of the following extensions into your config.neon. Again advanced users might want to skip or replace some of them.

```yml
extensions:
	arachne.dihelpers.iteratorresolvers: Arachne\DIHelpers\DI\IteratorResolversExtension
	arachne.dihelpers.iterators: Arachne\DIHelpers\DI\IteratorsExtension
	arachne.dihelpers.resolvers: Arachne\DIHelpers\DI\ResolversExtension
	arachne.csrf: Arachne\Csrf\DI\CsrfExtension
	arachne.forms: Arachne\Forms\DI\FormsExtension
	arachne.propertyaccess: Arachne\PropertyAccess\DI\PropertyAccessExtension
	arachne.twig: Arachne\Twig\DI\TwigExtension( %debugMode% )
	kdyby.annotations: Kdyby\Annotations\DI\AnnotationsExtension
	kdyby.translation: Kdyby\Translation\DI\TranslationExtension
	kdyby.validator: Kdyby\Validator\DI\ValidatorExtension
```

Also add the cache option for twig.

```yml
arachne.twig:
	options:
		cache: %tempDir%/cache/twig
```

Usage of ExpressionLanguage
----

If you want to use the [Expression](http://symfony.com/doc/current/reference/constraints/Expression.html) constraint, it is recommended to install one more extension.

```yml
extensions:
	arachne.expressionlanguage: Arachne\ExpressionLanguage\DI\ExpressionLanguage
```

Usage with Doctrine ORM
----

If you want to use Doctrine ORM it is recommended to add some more libraries.

```sh
composer require arachne/doctrine
composer require kdyby/doctrine
composer require symfony/doctrine-bridge
```

And also some additional extensions in config.neon.

```yml
extensions:
	arachne.doctrine: Arachne\Doctrine\DI\DoctrineExtension( %debugMode% )
	kdyby.console: Kdyby\Console\DI\ConsoleExtension
	kdyby.doctrine: Kdyby\Doctrine\DI\OrmExtension
	kdyby.events: Kdyby\Events\DI\EventsExtension
```
