<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class QuotesInNestedArray
{
    /**
     * @ORM\Column(options={"unsigned"=true, "default"=0})
     */
    private $loginCount;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Doctrine\ORM\Mapping\Column
