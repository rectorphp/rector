<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

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
