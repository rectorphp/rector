<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\DocBlockProperties;

final class SomeNullableInteger
{
    public function run(DocBlockProperties $docBlockProperties): void
    {
        $docBlockProperties->numberNullable->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;

return new UnionType([new IntegerType(), new NullType()]);
