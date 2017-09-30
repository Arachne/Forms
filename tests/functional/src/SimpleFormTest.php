<?php

namespace Tests\Functional;

use Arachne\Codeception\Module\NetteApplicationModule;
use Codeception\Test\Unit;

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
        $this->tester->see(null, 'form');
        $this->tester->see(null, 'input#form__token'); // CSRF protection
        $this->tester->see(null, 'input[name="_do"]'); // Signal for Nette/Application
        $this->tester->see(null, 'label.text-field-label');
        $this->tester->see(null, 'input.text-field-widget');
        $this->tester->see(null, 'button');
    }

    public function testPostMethodValidation(): void
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', '');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="_do"]');
        $this->tester->see('error', '.state');
        $this->tester->see('This value should not be blank.');
    }

    public function testPostMethodSuccess(): void
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', 'lorem ipsum');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="_do"]');
        $this->tester->see('success', '.state');
    }

    public function testGetMethodRendering(): void
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->see(null, 'form');
        $this->tester->see(null, 'input#form__token'); // CSRF protection
        $this->tester->see(null, 'input[name="do"]'); // Signal for Nette/Application
        $this->tester->see(null, 'button');
    }

    public function testGetMethodValidation(): void
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', '');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="do"]');
        $this->tester->see('error', '.state');
        $this->tester->see('This value should not be blank.');
    }

    public function testGetMethodSuccess(): void
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', 'lorem ipsum');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="do"]');
        $this->tester->see('success', '.state');
    }
}
