<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\DocBlockProperties;

final class ImplicitMixed
{
    public function run(DocBlockProperties $docBlockProperties): void
    {
        $docBlockProperties->implicitMixed->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\MixedType;

return new MixedType();
