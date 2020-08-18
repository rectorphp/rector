<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

final class KeepSerialiazableObject
{
    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
