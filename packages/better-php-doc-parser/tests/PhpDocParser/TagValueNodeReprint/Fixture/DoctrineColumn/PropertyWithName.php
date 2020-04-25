<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineColumn;

use Doctrine\ORM\Mapping as ORM;

final class PropertyWithName
{
    /**
     * @ORM\Column(type="string", name="hey")
     * @ORM\Column(name="hey", type="string")
     */
    public $name;
}
