<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\ClassWithTypedPropertyTypes;

final class NullableString
{
    public function run(ClassWithTypedPropertyTypes $props): void
    {
        $props->textNullable->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;

return new UnionType([new StringType(), new NullType()]);
