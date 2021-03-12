<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source;

use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 */
abstract class AbstractRealRepository
{
    /**
     * @return TEntity|null
     */
    public function getById($id)
    {
    }
}
