<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source;

/**
 * @template TEntity as object
 */
abstract class AbstractGenericMaybeArrayRepository
{
    /**
     * @return TEntity[]|null
     */
    public function findAllMaybe(string $some, int $type, $unknown)
    {
    }
}
