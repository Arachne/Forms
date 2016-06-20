<?php

namespace Tests\Functional;

use Codeception\Test\Unit;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SimpleFormTest extends Unit
{
    public function testRendering()
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->see(null, 'form');
        $this->tester->see(null, 'input#form__token'); // CSRF protection
        $this->tester->see(null, 'input[name="do"]'); // Signal for Nette/Application
        $this->tester->see(null, 'button');
    }

    public function testValidation()
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', '');
        $this->tester->click('button');
        $this->tester->see('error', '.state');
        $this->tester->see('This value should not be blank.');
    }

    public function testSuccess()
    {
        $this->tester->amOnPage('/article/');
        $this->tester->seeResponseCodeIs(200);
        $this->tester->fillField('#form_text', 'lorem ipsum');
        $this->tester->click('button');
        $this->tester->see('success', '.state');
    }
}
