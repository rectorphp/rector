<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineColumn;

use Doctrine\ORM\Mapping as ORM;

final class QuotesInNestedArray
{
    /**
     * @ORM\Column(options={"unsigned"=true, "default"=0})
     */
    private $loginCount;
}
