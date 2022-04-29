<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

abstract class SkipReadonlyProperty
{
    /**
     * @var array
     */
    public readonly array $changes;

    public function __construct(Model $model)
    {
        $this->changes = $model->getChanges();
    }
}

?>

