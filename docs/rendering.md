Rendering
====


Twig theme configuration
----

Arachne/Forms uses the Twig templating engine to render the forms. Symfony provides several simple [themes](symfony.com/doc/current/form/form_customization.html), by default the `form_div_layout.html.twig` is used.

You can change the default themes like this:

```yml
extenstions:
    arachne.forms: Arachne\Forms\DI\FormsExtension
    arachne.twig: Arachne\Twig\DI\TwigExtension( %debugMode% )

# Configure the default form theme to %appDir%/templates/form.twig and
# use bootstrap_3_horizontal_layout.html.twig from Symfony as fallback.
arachne.forms:
    defaultThemes:
        - form.twig
        - bootstrap_3_horizontal_layout.html.twig,

arachne.twig:
    paths:
        - %appDir%/templates
```

For form-specific themes you should use the `FormComponent::$onCreateView` event:

```php
namespace App\FrontModule\Component;

use Arachne\Forms\Application\FormComponent;
use Arachne\Forms\Application\FormComponentFactory;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponent;
use Symfony\Component\Form\FormFactoryInterface;

class CustomForm extends PresenterComponent
{
    private $formComponentFactory;
    private $formFactory;

    public function __construct(FormComponentFactory $formComponentFactory, FormFactoryInterface $formFactory)
    {
        $this->formComponentFactory = $formComponentFactory;
        $this->formFactory = $formFactory;
    }

    protected function createComponentForm()
    {
        $builder = $this->formFactory->createNamedBuilder($this->lookupPath(Presenter::class), 'form', null, []);

        //...

        $component = $this->formComponentFactory->create($builder->getForm());
        $component->onCreateView[] = function ($view, $component) {
            $component->getRenderer()->setTheme($view, [ 'form-specific-template.twig' ]);
        };

        return $component;
    }
}
```
