<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source;

/**
 * @template TEntity as object
 */
abstract class AbstractMaybeGenericRepository
{
    /**
     * @return TEntity|null
     */
    public function find($id)
    {
    }
}
