<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Source\ApiFilter;
use Doctrine\ORM\Mapping as ORM;

final class Book
{
    /**
     * @ApiFilter(Book::class)
     * @ORM\Column()
     */
    public $filterable;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Doctrine\ORM\Mapping\Column
