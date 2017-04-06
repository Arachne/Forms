Usage
====

To use arachne/forms in your Nette application you need to create a component of class `Arachne\Forms\Application\FormComponent`. It is a simple component which connects a Symfony form with Nette application using a signal to submit the form.

The following code shows the most simple way to create a form and attach it to FormComponent. It is a bit too long to copy everywhere so it is recommended to write something more generic and reuse it.

```php
namespace App\FrontModule\Component;

use Arachne\Forms\Application\FormComponent;
use Arachne\Forms\Application\FormComponentFactory;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponent;
use Symfony\Component\Form\FormFactoryInterface;

class CustomForm extends PresenterComponent
{
    /**
     * @var FormComponentFactory
     */
    private $formComponentFactory;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(FormComponentFactory $formComponentFactory, FormFactoryInterface $formFactory)
    {
        $this->formComponentFactory = $formComponentFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * @return FormComponent
     */
    protected function createComponentForm()
    {
        // Create a symfony form using the FormFactory from symfony. There are several ways to do that. Look into symfony documentation for details.
        $builder = $this->formFactory->createNamedBuilder($this->lookupPath(Presenter::class), 'form', null, []);

        $builder->add('firstname');
        $builder->add('lastname');
        $builder->add('email');
        $builder->add('text');

        // Then you need to attach the form to a newly created FormComponent.
        $component = $this->formComponentFactory->create($builder->getForm());

        // FormComponent provides some events which you can use as you need.
        $component->onSuccess[] = function (array $data) {
            $this->redirect('this');
        };

        return $component;
    }
}
```

Best Practice
----

It is highly recommended to define all your forms as custom form types and to add the submit button in template instead of in the code. This will help you to generalize the component above. Read the related [article](http://symfony.com/doc/current/best_practices/forms.html) about this topic.
