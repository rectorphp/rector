<?php

namespace Rector\Doctrine\Tests\Rector\ClassMethod\ChangeGetIdTypeToUuidRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 */
class SkipSettedId
{
    private $id;

    public function setId(UuidInterface $uuid): int
    {
        return $this->id = $uuid;
    }
}
