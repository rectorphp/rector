<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractRealRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\ChildRealObject;

/**
 * @template TEntity as ChildRealObject
 * @extends AbstractRealRepository<TEntity>
 */
final class RealGetById extends AbstractRealRepository
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractRealRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\ChildRealObject;

/**
 * @template TEntity as ChildRealObject
 * @extends AbstractRealRepository<TEntity>
 * @method \Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\ChildRealObject|null getById($id)
 */
final class RealGetById extends AbstractRealRepository
{
}

?>
