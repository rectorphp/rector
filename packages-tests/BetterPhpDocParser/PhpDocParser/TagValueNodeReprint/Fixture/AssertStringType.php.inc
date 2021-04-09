<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\AssertType;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertStringType
{
    /**
     * @Assert\Type("string")
     */
    public $anotherProperty;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Symfony\Component\Validator\Constraints\Type
