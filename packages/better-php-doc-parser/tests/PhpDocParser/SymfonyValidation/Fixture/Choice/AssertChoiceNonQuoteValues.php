<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyValidation\Fixture\Choice;

use Symfony\Component\Validator\Constraints as Assert;

class AssertChoiceNonQuoteValues
{
    /**
     * @Assert\Choice({chalet, apartment})
     */
    public  $type;
}
