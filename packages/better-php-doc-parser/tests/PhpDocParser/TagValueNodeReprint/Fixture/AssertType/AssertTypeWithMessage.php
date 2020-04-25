<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\AssertType;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertTypeWithMessage
{
    /**
     * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.")
     */
    protected $age;
}
