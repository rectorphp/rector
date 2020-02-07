<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector\Source;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Building
{
    private $id;

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }
}
