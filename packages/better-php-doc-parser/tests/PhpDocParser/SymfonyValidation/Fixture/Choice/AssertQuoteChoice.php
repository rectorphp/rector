<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyValidation\Fixture\Choice;

use Symfony\Component\Validator\Constraints as Assert;

class AssertQuoteChoice
{
    const CHOICE_ONE = 'choice_one';
    const CHOICE_TWO = 'choice_two';
    /**
     * @Assert\Choice({SomeEntity::CHOICE_ONE, SomeEntity::CHOICE_TWO})
     */
    private $someChoice = self::CHOICE_ONE;
}
