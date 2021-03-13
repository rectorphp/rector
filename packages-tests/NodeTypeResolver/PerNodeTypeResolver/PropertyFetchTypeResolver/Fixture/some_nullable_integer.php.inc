<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\DocBlockProperties;

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
