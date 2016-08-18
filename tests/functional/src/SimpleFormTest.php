<?php

namespace Tests\Functional;

use Codeception\Test\Unit;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SimpleFormTest extends Unit
{
    public function testPostMethodRendering()
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->see(null, 'form');
        $this->tester->see(null, 'input#form__token'); // CSRF protection
        $this->tester->see(null, 'input[name="'.(property_exists('Nette\Application\UI\Component', 'onAnchor') ? '_' : '').'do"]'); // Signal for Nette/Application
        $this->tester->see(null, 'button');
    }

    public function testPostMethodValidation()
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', '');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="'.(property_exists('Nette\Application\UI\Component', 'onAnchor') ? '_' : '').'do"]');
        $this->tester->see('error', '.state');
        $this->tester->see('This value should not be blank.');
    }

    public function testPostMethodSuccess()
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', 'lorem ipsum');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="'.(property_exists('Nette\Application\UI\Component', 'onAnchor') ? '_' : '').'do"]');
        $this->tester->see('success', '.state');
    }

    public function testGetMethodRendering()
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->see(null, 'form');
        $this->tester->see(null, 'input#form__token'); // CSRF protection
        $this->tester->see(null, 'input[name="do"]'); // Signal for Nette/Application
        $this->tester->see(null, 'button');
    }

    public function testGetMethodValidation()
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', '');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="do"]');
        $this->tester->see('error', '.state');
        $this->tester->see('This value should not be blank.');
    }

    public function testGetMethodSuccess()
    {
        $this->tester->amOnPage('/article/useget/1');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', 'lorem ipsum');
        $this->tester->click('button');
        $this->tester->see(null, 'input[name="do"]');
        $this->tester->see('success', '.state');
    }
}
