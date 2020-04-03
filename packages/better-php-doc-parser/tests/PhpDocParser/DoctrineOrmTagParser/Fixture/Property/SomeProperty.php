<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser\Fixture\Property;

use Doctrine\ORM\Mapping as ORM;

final class SomeProperty
{
    /**
     * @ORM\Column
     */
    public $id;
}
