<?php

declare(strict_types=1);

namespace Tests\Functional;

use Codeception\Test\Unit;
use Contributte\Codeception\Module\NetteApplicationModule;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SimpleFormTest extends Unit
{
    /**
     * @var NetteApplicationModule
     */
    protected $tester;

    public function testPostMethodRendering(): void
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->see('', 'form');
        $this->tester->see('', 'input#form__token'); // CSRF protection
        $this->tester->see('', 'input[name="_do"]'); // Signal for Nette/Application
        $this->tester->see('', 'label.text-field-label');
        $this->tester->see('', 'input.text-field-widget');
        $this->tester->see('', 'button');
    }

    public function testPostMethodValidation(): void
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', '');
        $this->tester->click('button');
        $this->tester->see('', 'input[name="_do"]');
        $this->tester->see('error', '.state');
        $this->tester->see('This value should not be blank.');
    }

    public function testPostMethodSuccess(): void
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', 'lorem ipsum');
        $this->tester->click('button');
        $this->tester->see('', 'input[name="_do"]');
        $this->tester->see('success', '.state');
    }

    public function testGetMethodRendering(): void
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->see('', 'form');
        $this->tester->see('', 'input#form__token'); // CSRF protection
        $this->tester->see('', 'input[name="do"]'); // Signal for Nette/Application
        $this->tester->see('', 'button');
    }

    public function testGetMethodValidation(): void
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', '');
        $this->tester->click('button');
        $this->tester->see('', 'input[name="do"]');
        $this->tester->see('error', '.state');
        $this->tester->see('This value should not be blank.');
    }

    public function testGetMethodSuccess(): void
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', 'lorem ipsum');
        $this->tester->click('button');
        $this->tester->see('', 'input[name="do"]');
        $this->tester->see('success', '.state');
    }
}
