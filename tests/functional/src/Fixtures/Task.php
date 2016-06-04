<?php

namespace Tests\Functional\Fixtures;

use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Task
{
    /**
     * @NotBlank()
     * @var string
     */
    private $text;

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }
}
