<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Apple
{
    private $id;

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }
}
