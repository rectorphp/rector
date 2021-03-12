<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Ramsey\Uuid\Uuid;

class SkipUuid
{
    /**
     * @var Uuid
     */
    private $id;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id)
    {
        $this->id = $id;
    }
}
