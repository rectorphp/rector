<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericMaybeArrayRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 */
final class ArrayLikeNullable extends AbstractGenericMaybeArrayRepository
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericMaybeArrayRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 * @method \Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject[]|null findAllMaybe(string $some, int $type, $unknown)
 */
final class ArrayLikeNullable extends AbstractGenericMaybeArrayRepository
{
}

?>
