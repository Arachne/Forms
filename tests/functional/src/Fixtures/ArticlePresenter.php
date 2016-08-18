<?php

namespace Tests\Functional\Fixtures;

use Arachne\Forms\Application\FormComponentFactory;
use Nette\Application\UI\Presenter;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticlePresenter extends Presenter
{
    /**
     * @var FormComponentFactory
     * @inject
     */
    public $formComponentFactory;

    /**
     * @var FormFactoryInterface
     * @inject
     */
    public $formFactory;

    public function actionDefault()
    {
    }

    protected function createComponentForm()
    {
        $builder = $this->formFactory->createBuilder(FormType::class, new Task());
        if ($this->getRequest()->getParameter('useget')) {
            $builder->setMethod('GET');
        }
        $builder->add('text', TextType::class);
        $builder->add('save', SubmitType::class);

        $component = $this->formComponentFactory->create($builder->getForm());
        $component->onSuccess[] = function () {
            $this->getTemplate()->state = 'success';
        };
        $component->onError[] = function () {
            $this->getTemplate()->state = 'error';
        };

        return $component;
    }

    public function formatTemplateFiles()
    {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':'.$name, ':'));

        return [
            __DIR__."/../../templates/$presenter.$this->view.latte",
        ];
    }
}
