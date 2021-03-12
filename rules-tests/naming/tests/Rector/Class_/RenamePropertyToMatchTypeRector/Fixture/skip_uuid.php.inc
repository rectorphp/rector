<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

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
