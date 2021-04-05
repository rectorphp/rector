<?php

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Doctrine\ORM\Mapping\Column;

final class DoctrineColumnWithEqual
{
    /**
     * @Column(type="string", length=2, options={"fixed"=true, "comment" = "Initial letters of first and last name"})
     */
    public $someProperty;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Doctrine\ORM\Mapping\Column
