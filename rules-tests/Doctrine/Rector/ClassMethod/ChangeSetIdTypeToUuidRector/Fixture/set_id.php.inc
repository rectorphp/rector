<?php

namespace Rector\Tests\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SetId
{
    private $id;

    public function setId(int $uuid): int
    {
        return $this->id = $uuid;
    }
}

?>
-----
<?php

namespace Rector\Tests\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SetId
{
    private $id;

    public function setId(\Ramsey\Uuid\UuidInterface $uuid): int
    {
        return $this->id = $uuid;
    }
}

?>
