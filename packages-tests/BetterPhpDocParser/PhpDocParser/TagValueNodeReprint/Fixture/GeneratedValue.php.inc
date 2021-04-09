<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Doctrine\ORM\Mapping as ORM;

class GeneratedValue
{
    /**
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @ORM\GeneratedValue
     */
    private $empty;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Doctrine\ORM\Mapping\GeneratedValue
