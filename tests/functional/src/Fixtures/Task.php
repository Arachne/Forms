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
     *
     * @var string|null
     */
    private $text;

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }
}
