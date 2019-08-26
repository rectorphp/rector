<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Tests\PhpDocParser\OrmTagParser\Property_\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class SomeProperty
{
    /**
     * @ORM\Column
     */
    public $id;
}
