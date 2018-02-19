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
        - bootstrap_3_horizontal_layout.html.twig

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
use Nette\Application\UI\Control;
use Symfony\Component\Form\FormFactoryInterface;

class CustomForm extends Control
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
            // Set the form theme for this form.
            $component->getRenderer()->setTheme($view, [ 'form-specific-template.twig' ]);
        };

        return $component;
    }

    public function render()
    {
        // Render the form using the theme specified above.
        $this->getComponent('form')->render();
    }
}
```


Latte macros
----

If you want to have more control about the rendering you can use Latte macros to render only parts of the form.

```php
class CustomForm extends Control
{
    //...

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/template.latte');
        $template->form = $this->getComponent('form')->getView();
        $template->render();
    }
}
```

```
{* template.latte *}
{formStart $form}
    {formLabel $form[field] label_attr => [class => form-label]}
    {formWidget $form[field] attr => [class => form-input]}
{formEnd $form}
```

Here is complete list of the macros available:

- `formComplete` - whole form
- `formStart` - the starting `<form>` tag
- `formEnd` - all fields not yet rendered and the ending `</form>` tag
- `formRest` - all fields not yet rendered
- `formLabel` - the `<label>` tag for the given field
- `formErrors` - errors of the given field
- `formWidget` - the field itself, usually `<input>`, `<select>` or `<textarea>`
- `formRow` - complete row with the given field (label, widget and errors)

The macros are basically equivalents to the form rendering [functions](http://symfony.com/doc/current/reference/forms/twig_reference.html) in Symfony.
