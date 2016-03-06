<?php

namespace Tests\Functional;

use Codeception\TestCase\Test;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SimpleFormTest extends Test
{

    public function testRendering()
    {
        $this->guy->amOnPage('/article/');
        $this->guy->seeResponseCodeIs(200);
        $this->guy->see(null, 'form');
        $this->guy->see(null, 'input#form__token'); // CSRF protection
        $this->guy->see(null, 'input[name="do"]'); // Signal for Nette/Application
        $this->guy->see(null, 'button');
    }

    public function testValidation()
    {
        $this->guy->amOnPage('/article/');
        $this->guy->seeResponseCodeIs(200);
        $this->guy->fillField('#form_text', '');
        $this->guy->click('button');
        $this->guy->see('error', '.state');
        $this->guy->see('This value should not be blank.');
    }

    public function testSuccess()
    {
        $this->guy->amOnPage('/article/');
        $this->guy->seeResponseCodeIs(200);
        $this->guy->fillField('#form_text', 'lorem ipsum');
        $this->guy->click('button');
        $this->guy->see('success', '.state');
    }
}
