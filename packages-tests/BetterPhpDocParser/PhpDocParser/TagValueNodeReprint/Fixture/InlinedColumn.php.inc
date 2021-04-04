<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class InlinedColumn
{
    /** @ORM\Column(name="url", type="string") */
    private $loginCount;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Doctrine\ORM\Mapping\Column
