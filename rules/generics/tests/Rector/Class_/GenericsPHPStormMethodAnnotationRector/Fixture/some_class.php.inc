<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 */
final class AndroidDeviceRepository extends AbstractGenericRepository
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericRepository;
use Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @template TEntity as RealObject
 * @extends AbstractRepository<TEntity>
 * @method RealObject find($id)
 */
final class AndroidDeviceRepository extends AbstractGenericRepository
{
}

?>
