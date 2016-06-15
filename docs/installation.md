Installation
====

The best way to install Arachne/Forms is using [Composer](http://getcomposer.org/).

Recommended
----

The commands below will install all the libraries recommended to use Arachne/Forms. They are intentionally not direct dependencies so that advanced users may skip or replace some of them.

```sh
composer require arachne/csrf
composer require arachne/forms
composer require arachne/twig
composer require arachne/upload
composer require kdyby/annotations
composer require kdyby/translation
composer require kdyby/validator
```

It is recommended to add all of the following extensions into your config.neon. Again advanced users might want to skip or replace some of them.

```yml
extensions:
    # Always required.
    arachne.dihelpers.iteratorresolvers: Arachne\DIHelpers\DI\IteratorResolversExtension
    arachne.dihelpers.iterators: Arachne\DIHelpers\DI\IteratorsExtension
    arachne.dihelpers.resolvers: Arachne\DIHelpers\DI\ResolversExtension
    arachne.forms: Arachne\Forms\DI\FormsExtension

    # Technically optional but do you really want forms without CSRF protection, validation, files upload and a way to render them?
    arachne.csrf: Arachne\Csrf\DI\CsrfExtension
    arachne.twig: Arachne\Twig\DI\TwigExtension(%tempDir%/cache/twig, %debugMode%)
    kdyby.annotations: Kdyby\Annotations\DI\AnnotationsExtension
    kdyby.translation: Kdyby\Translation\DI\TranslationExtension
    kdyby.validator: Kdyby\Validator\DI\ValidatorExtension
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
    arachne.doctrine: Arachne\Doctrine\DI\DoctrineExtension(%debugMode%)
    kdyby.console: Kdyby\Console\DI\ConsoleExtension
    kdyby.doctrine: Kdyby\Doctrine\DI\OrmExtension
```

PropertyAccess configuration
----

If you want to configure PropertyAccessor or simply have it as a service for better performance, add this extension.

```sh
composer require arachne/property-access
```

```yml
extensions:
    arachne.propertyaccess: Arachne\PropertyAccess\DI\PropertyAccessExtension
```

Usage of ExpressionLanguage
----

If you want to use the [Expression](http://symfony.com/doc/current/reference/constraints/Expression.html) constraint, add one more extension.

```sh
composer require arachne/expression-language
```

```yml
extensions:
    arachne.expressionlanguage: Arachne\ExpressionLanguage\DI\ExpressionLanguageExtension
```
