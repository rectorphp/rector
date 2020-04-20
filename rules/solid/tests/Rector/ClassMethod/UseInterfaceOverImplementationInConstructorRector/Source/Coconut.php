<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Coconut implements Fruit
{
    private $id;

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }
}
