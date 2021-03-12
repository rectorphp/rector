<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericMaybeArrayRepository;
use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

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

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericMaybeArrayRepository;
use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 * @method \Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject[]|null findAllMaybe(string $some, int $type, $unknown)
 */
final class ArrayLikeNullable extends AbstractGenericMaybeArrayRepository
{
}

?>
