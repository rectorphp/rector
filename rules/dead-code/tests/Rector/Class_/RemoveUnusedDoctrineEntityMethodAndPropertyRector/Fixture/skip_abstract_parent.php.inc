<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product extends Service
{
    public const TYPE = 'product';

    public function getType(): string
    {
        return self::TYPE;
    }
}

abstract class Service
{
    public abstract function getType();
}
