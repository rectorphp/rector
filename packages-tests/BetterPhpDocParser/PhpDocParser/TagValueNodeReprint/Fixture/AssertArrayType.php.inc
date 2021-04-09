<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\AssertType;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertArrayType
{
    /**
     * @Assert\Type(type={"alpha", "digit"})
     */
    public $accessCode;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Symfony\Component\Validator\Constraints\Type
