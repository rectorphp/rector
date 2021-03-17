<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericRepository;
use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @extends AbstractRepository<RealObject>
 */
final class DirectPassRepository extends AbstractGenericRepository
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Fixture;

use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\AbstractGenericRepository;
use Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject;

/**
 * @extends AbstractRepository<RealObject>
 * @method \Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source\RealObject find($id)
 */
final class DirectPassRepository extends AbstractGenericRepository
{
}

?>
