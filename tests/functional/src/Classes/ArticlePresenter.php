<?php

namespace Tests\Functional\Classes;

use Arachne\Forms\Application\FormComponentFactory;
use Nette\Application\UI\Presenter;
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
        $builder = $this->formFactory->createBuilder('form', new Task());
        $builder->add('text', 'text');
        $builder->add('save', 'submit');

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
        $presenter = substr($name, strrpos(':' . $name, ':'));
        return [ __DIR__ . "/../../templates/$presenter.$this->view.latte" ];
    }
}
