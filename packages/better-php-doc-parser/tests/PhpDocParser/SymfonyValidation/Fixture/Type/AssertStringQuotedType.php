<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyValidation\Fixture\Type;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertStringQuotedType
{
    /**
     * @Assert\Type("string")
     */
    public $someStringProperty;
}
