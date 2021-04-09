<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\AssertType;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertTypeWithMessage
{
    /**
     * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.")
     */
    protected $age;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Symfony\Component\Validator\Constraints\Type
