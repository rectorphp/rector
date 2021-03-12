<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source;

/**
 * @template TEntity as object
 */
abstract class AbstractGenericArrayRepository
{
    /**
     * @return TEntity[]
     */
    public function findAll()
    {
    }
}
