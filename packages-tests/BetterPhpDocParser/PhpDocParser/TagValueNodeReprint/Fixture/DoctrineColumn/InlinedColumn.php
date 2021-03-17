<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineColumn;

use Doctrine\ORM\Mapping as ORM;

final class InlinedColumn
{
    /** @ORM\Column(name="url", type="string") */
    private $loginCount;
}
