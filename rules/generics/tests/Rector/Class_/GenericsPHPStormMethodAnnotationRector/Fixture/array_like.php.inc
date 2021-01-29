<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericArrayRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 */
final class ArrayLike extends AbstractGenericArrayRepository
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericArrayRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 * @method \Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject[] findAll()
 */
final class ArrayLike extends AbstractGenericArrayRepository
{
}

?>
