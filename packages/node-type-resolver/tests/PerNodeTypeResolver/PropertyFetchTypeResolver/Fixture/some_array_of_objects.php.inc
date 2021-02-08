<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\DocBlockProperties;

final class SomeArrayOfObjects
{
    public function run(DocBlockProperties $docBlockProperties): void
    {
        $docBlockProperties->arrayOfAbcs->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;

return new ArrayType(new MixedType(), new ObjectType(Abc::class));
