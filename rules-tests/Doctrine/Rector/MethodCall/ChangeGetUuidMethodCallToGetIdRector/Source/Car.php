<?php

declare(strict_types=1);

namespace Rector\Tests\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Source;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 */
class Car
{
    private $uuid;

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
