<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractMaybeGenericRepository;
use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 */
final class NullableGet extends AbstractMaybeGenericRepository
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractMaybeGenericRepository;
use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 * @method \Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject|null find($id)
 */
final class NullableGet extends AbstractMaybeGenericRepository
{
}

?>
