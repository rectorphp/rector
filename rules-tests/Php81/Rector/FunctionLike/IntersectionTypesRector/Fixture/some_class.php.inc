<?php

namespace Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Fixture;

use Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Source\FirstObjectToIntersect;
use Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Source\SecondObjectToIntersect;

final class SkipScalarTypes
{
    /**
     * @param FirstObjectToIntersect&SecondObjectToIntersect $types
     */
    public function process($types)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Fixture;

use Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Source\FirstObjectToIntersect;
use Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Source\SecondObjectToIntersect;

final class SkipScalarTypes
{
    /**
     * @param FirstObjectToIntersect&SecondObjectToIntersect $types
     */
    public function process(\Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Source\FirstObjectToIntersect&\Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Source\SecondObjectToIntersect $types)
    {
    }
}

?>
